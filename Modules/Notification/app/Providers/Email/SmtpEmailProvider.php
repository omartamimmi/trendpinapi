<?php

namespace Modules\Notification\app\Providers\Email;

use Modules\Notification\app\Providers\AbstractNotificationChannel;
use Modules\Notification\app\DTOs\NotificationPayload;
use Modules\Notification\app\DTOs\NotificationResult;
use Modules\Notification\app\DTOs\CredentialTestResult;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

/**
 * SMTP Email Provider
 * Sends emails via SMTP using Laravel's mail or direct PHPMailer
 */
class SmtpEmailProvider extends AbstractNotificationChannel
{
    protected string $channelType = 'email';

    protected function getRequiredConfigFields(): array
    {
        return ['host', 'port', 'username', 'password', 'from_address'];
    }

    public function send(NotificationPayload $payload): NotificationResult
    {
        $this->logAttempt($payload);

        if (!$this->isConfigured()) {
            return NotificationResult::failure('SMTP is not configured');
        }

        try {
            $mail = $this->createMailer();

            // Set recipient
            $mail->addAddress($payload->recipientContact);

            // Set content
            $mail->isHTML(true);
            $mail->Subject = $payload->subject;
            $mail->Body = nl2br($payload->body);
            $mail->AltBody = strip_tags($payload->body);

            $mail->send();

            $result = NotificationResult::success(
                'Email sent successfully',
                $mail->getLastMessageID(),
                ['recipient' => $payload->recipientContact]
            );

            $this->logResult($result);
            return $result;

        } catch (PHPMailerException $e) {
            $result = NotificationResult::failure(
                'Failed to send email: ' . $e->getMessage(),
                'SMTP_ERROR',
                $e
            );

            $this->logResult($result);
            return $result;

        } catch (\Exception $e) {
            $result = NotificationResult::failure(
                'Unexpected error: ' . $e->getMessage(),
                'UNKNOWN_ERROR',
                $e
            );

            $this->logResult($result);
            return $result;
        }
    }

    public function testConnection(): CredentialTestResult
    {
        if (!$this->isConfigured()) {
            return CredentialTestResult::failure(
                'Configuration incomplete',
                'Please fill in all required SMTP fields'
            );
        }

        try {
            $mail = $this->createMailer();

            // Test SMTP connection
            if ($mail->smtpConnect()) {
                $mail->smtpClose();

                return CredentialTestResult::success(
                    'SMTP connection successful',
                    'Successfully connected to ' . $this->getConfig('host') . ':' . $this->getConfig('port'),
                    [
                        'host' => $this->getConfig('host'),
                        'port' => $this->getConfig('port'),
                        'encryption' => $this->getConfig('encryption', 'tls'),
                    ]
                );
            }

            return CredentialTestResult::failure(
                'SMTP connection failed',
                'Could not establish connection to SMTP server'
            );

        } catch (PHPMailerException $e) {
            return CredentialTestResult::failure(
                'SMTP connection failed',
                $e->getMessage(),
                'SMTP_ERROR'
            );

        } catch (\Exception $e) {
            return CredentialTestResult::failure(
                'Connection test failed',
                $e->getMessage(),
                'UNKNOWN_ERROR'
            );
        }
    }

    /**
     * Create and configure PHPMailer instance
     */
    private function createMailer(): PHPMailer
    {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = $this->getConfig('host');
        $mail->Port = (int) $this->getConfig('port', 587);
        $mail->SMTPAuth = true;
        $mail->Username = $this->getConfig('username');
        $mail->Password = $this->getConfig('password');

        // Encryption
        $encryption = $this->getConfig('encryption', 'tls');
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }

        // Sender
        $mail->setFrom(
            $this->getConfig('from_address'),
            $this->getConfig('from_name', config('app.name'))
        );

        // Charset
        $mail->CharSet = 'UTF-8';

        return $mail;
    }
}
