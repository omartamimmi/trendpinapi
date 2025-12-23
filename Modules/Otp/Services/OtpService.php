<?php

namespace Modules\Otp\Services;

use Modules\Otp\app\Models\PhoneVerification;
use Modules\Notification\app\Repositories\CredentialRepository;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Exception;

class OtpService
{
    protected ?Client $twilioClient = null;
    protected ?string $twilioFrom = null;
    protected CredentialRepository $credentialRepository;

    public function __construct(CredentialRepository $credentialRepository)
    {
        $this->credentialRepository = $credentialRepository;
        $this->initializeTwilioClient();
    }

    /**
     * Initialize Twilio client from database credentials
     */
    protected function initializeTwilioClient(): void
    {
        // First try to get credentials from database
        $smsCredentials = $this->credentialRepository->getByChannel('sms');

        if ($smsCredentials && $smsCredentials->isActive) {
            $credentials = $smsCredentials->credentials;
            $sid = $credentials['account_sid'] ?? null;
            $token = $credentials['auth_token'] ?? null;
            $from = $credentials['from_number'] ?? null;

            if ($sid && $token && $from) {
                $this->twilioClient = new Client($sid, $token);
                $this->twilioFrom = $from;
                return;
            }
        }

        // Fallback to env config
        $sid = config('otp.twilio.sid');
        $token = config('otp.twilio.token');
        $from = config('otp.twilio.from');

        if ($sid && $token && $from) {
            $this->twilioClient = new Client($sid, $token);
            $this->twilioFrom = $from;
        }
    }

    /**
     * Send OTP to phone number
     */
    public function send(string $phoneNumber, string $channel = 'sms'): PhoneVerification
    {
        // Delete any existing unverified codes for this phone
        PhoneVerification::where('phone_number', $phoneNumber)
            ->whereNull('verified_at')
            ->delete();

        // Generate a 6-digit code
        $code = $this->generateCode();

        // Create the verification record
        $verification = PhoneVerification::create([
            'phone_number' => $phoneNumber,
            'code' => $code,
            'expires_at' => now()->addMinutes(config('otp.expiry_minutes', 10)),
        ]);

        // Send the code via Twilio
        $this->sendViaTwilio($phoneNumber, $code);

        return $verification;
    }

    /**
     * Verify OTP code
     */
    public function verify(string $phoneNumber, string $code): bool
    {
        $verification = PhoneVerification::where('phone_number', $phoneNumber)
            ->whereNull('verified_at')
            ->latest()
            ->first();

        if (!$verification) {
            throw new Exception(__('otp::messages.verification_not_found'));
        }

        if ($verification->isExpired()) {
            throw new Exception(__('otp::messages.code_expired'));
        }

        if ($verification->hasExceededAttempts(config('otp.max_attempts', 5))) {
            throw new Exception(__('otp::messages.max_attempts_exceeded'));
        }

        if ($verification->code !== $code) {
            $verification->incrementAttempts();
            throw new Exception(__('otp::messages.invalid_code'));
        }

        $verification->markAsVerified();

        return true;
    }

    /**
     * Check if phone number is verified
     */
    public function isVerified(string $phoneNumber): bool
    {
        return PhoneVerification::where('phone_number', $phoneNumber)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', now()->subMinutes(config('otp.verification_valid_minutes', 60)))
            ->exists();
    }

    /**
     * Generate a random OTP code
     */
    protected function generateCode(): string
    {
        $length = config('otp.code_length', 6);
        return str_pad((string) random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP code via Twilio SMS
     */
    protected function sendViaTwilio(string $phoneNumber, string $code): void
    {
        if (!$this->twilioClient || !$this->twilioFrom) {
            // Log the code for development/testing when Twilio is not configured
            \Log::info("OTP Code for {$phoneNumber}: {$code}");
            return;
        }

        try {
            $message = config('otp.message', 'Your verification code is: :code');
            $message = str_replace(':code', $code, $message);

            $this->twilioClient->messages->create(
                $phoneNumber,
                [
                    'from' => $this->twilioFrom,
                    'body' => $message,
                ]
            );
        } catch (TwilioException $e) {
            throw new Exception(__('otp::messages.sms_send_failed') . ': ' . $e->getMessage());
        }
    }
}
