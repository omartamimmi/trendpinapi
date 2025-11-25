<?php

namespace App\Services;

use App\Models\QrPayment;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class QrPaymentService
{
    /**
     * Generate QR code payment for merchant
     *
     * @param User $merchant
     * @param float $amount
     * @param string|null $description
     * @param int $expiryMinutes
     * @param array $metadata
     * @return QrPayment
     */
    public function generateQrCode(
        User $merchant,
        float $amount,
        ?string $description = null,
        int $expiryMinutes = 15,
        array $metadata = []
    ): QrPayment {
        // Generate unique reference
        $reference = QrPayment::generateReference();

        // Create expiry time
        $expiresAt = Carbon::now()->addMinutes($expiryMinutes);

        // Create payment record
        $qrPayment = QrPayment::create([
            'merchant_id' => $merchant->id,
            'qr_code_reference' => $reference,
            'amount' => $amount,
            'currency' => 'JOD',
            'description' => $description,
            'status' => 'pending',
            'expires_at' => $expiresAt,
            'metadata' => $metadata,
        ]);

        // Generate QR code data (JSON payload)
        $qrData = [
            'reference' => $reference,
            'merchant_id' => $merchant->id,
            'merchant_name' => $merchant->name,
            'amount' => $amount,
            'currency' => 'JOD',
            'description' => $description,
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        $qrDataJson = json_encode($qrData);

        // Generate QR code image
        $qrCodeImage = $this->generateQrCodeImage($qrDataJson, $reference);

        // Update payment with QR code data and image path
        $qrPayment->update([
            'qr_code_data' => $qrDataJson,
            'qr_code_image' => $qrCodeImage,
        ]);

        return $qrPayment->fresh();
    }

    /**
     * Generate QR code image and save to storage
     *
     * @param string $data
     * @param string $reference
     * @return string Path to QR code image
     */
    protected function generateQrCodeImage(string $data, string $reference): string
    {
        // Generate QR code as PNG
        $qrCode = QrCode::format('png')
            ->size(400)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($data);

        // Save to storage
        $filename = 'qr-codes/' . $reference . '.png';
        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Get QR code image as base64
     *
     * @param QrPayment $qrPayment
     * @return string
     */
    public function getQrCodeBase64(QrPayment $qrPayment): string
    {
        if ($qrPayment->qr_code_image && Storage::disk('public')->exists($qrPayment->qr_code_image)) {
            $image = Storage::disk('public')->get($qrPayment->qr_code_image);
            return 'data:image/png;base64,' . base64_encode($image);
        }

        // If image doesn't exist, regenerate it
        if ($qrPayment->qr_code_data) {
            $filename = $this->generateQrCodeImage($qrPayment->qr_code_data, $qrPayment->qr_code_reference);
            $qrPayment->update(['qr_code_image' => $filename]);
            $image = Storage::disk('public')->get($filename);
            return 'data:image/png;base64,' . base64_encode($image);
        }

        return '';
    }

    /**
     * Verify and decode QR code data
     *
     * @param string $qrData
     * @return array|null
     */
    public function verifyQrData(string $qrData): ?array
    {
        try {
            $data = json_decode($qrData, true);

            if (!$data || !isset($data['reference'], $data['merchant_id'], $data['amount'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get QR payment by reference
     *
     * @param string $reference
     * @return QrPayment|null
     */
    public function getByReference(string $reference): ?QrPayment
    {
        return QrPayment::where('qr_code_reference', $reference)->first();
    }

    /**
     * Process payment from customer
     *
     * @param string $reference
     * @param User $customer
     * @return array
     */
    public function processPayment(string $reference, User $customer): array
    {
        $qrPayment = $this->getByReference($reference);

        if (!$qrPayment) {
            return [
                'success' => false,
                'message' => 'Payment not found',
            ];
        }

        // Check if already paid
        if ($qrPayment->isCompleted()) {
            return [
                'success' => false,
                'message' => 'This payment has already been completed',
            ];
        }

        // Check if expired
        if ($qrPayment->isExpired()) {
            $qrPayment->markAsExpired();
            return [
                'success' => false,
                'message' => 'This QR code has expired',
            ];
        }

        // Check if can be paid
        if (!$qrPayment->canBePaid()) {
            return [
                'success' => false,
                'message' => 'This payment cannot be processed',
            ];
        }

        // Here you would integrate with actual payment gateway
        // For now, we'll just mark it as completed
        // In production, you'd process the payment through a gateway first

        $qrPayment->markAsCompleted($customer);

        return [
            'success' => true,
            'message' => 'Payment completed successfully',
            'payment' => $qrPayment->fresh(['merchant', 'customer']),
        ];
    }

    /**
     * Cancel QR payment
     *
     * @param QrPayment $qrPayment
     * @return bool
     */
    public function cancelPayment(QrPayment $qrPayment): bool
    {
        if ($qrPayment->status === 'pending') {
            $qrPayment->markAsCancelled();
            return true;
        }

        return false;
    }

    /**
     * Get merchant's QR payments
     *
     * @param User $merchant
     * @param string|null $status
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getMerchantPayments(User $merchant, ?string $status = null, int $perPage = 20)
    {
        $query = QrPayment::where('merchant_id', $merchant->id)
            ->with('customer')
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get customer's QR payments
     *
     * @param User $customer
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCustomerPayments(User $customer, int $perPage = 20)
    {
        return QrPayment::where('customer_id', $customer->id)
            ->with('merchant')
            ->orderBy('paid_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Expire old pending QR codes
     * Should be called by a scheduled task
     *
     * @return int Number of expired QR codes
     */
    public function expireOldQrCodes(): int
    {
        $expiredCount = QrPayment::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->count();

        QrPayment::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->update(['status' => 'expired']);

        return $expiredCount;
    }
}
