<?php

namespace App\Services;

use App\Models\QrPayment;
use App\Models\User;
use Modules\Business\app\Models\Branch;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class QrPaymentService
{
    /**
     * Generate QR code for payment
     */
    public function generateQrCode(
        int $branchId,
        User $user,
        float $amount,
        ?string $description = null,
        int $expiryMinutes = 15,
        array $metadata = []
    ): QrPayment {
        // Verify branch exists
        $branch = Branch::findOrFail($branchId);
        
        $reference = QrPayment::generateReference();
        $expiresAt = Carbon::now()->addMinutes($expiryMinutes);
        
        // Create QR payment record
        $qrPayment = QrPayment::create([
            'branch_id' => $branchId,
            'user_id' => $user->id,
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
            'branch_id' => $branchId,
            'branch_name' => $branch->name,
            'amount' => $amount,
            'currency' => 'JOD',
            'description' => $description,
            'expires_at' => $expiresAt->toIso8601String(),
        ];

        $qrDataJson = json_encode($qrData);
        
        // Generate QR code image
        $qrCodeImage = $this->generateQrCodeImage($qrDataJson, $reference);

        // Update with QR data and image
        $qrPayment->update([
            'qr_code_data' => $qrDataJson,
            'qr_code_image' => $qrCodeImage,
        ]);

        return $qrPayment->fresh(['branch', 'user']);
    }

    /**
     * Generate QR code image
     */
    protected function generateQrCodeImage(string $data, string $reference): string
    {
        // Generate QR code PNG
        $qrCode = QrCode::format('png')
            ->size(512)
            ->margin(2)
            ->errorCorrection('H')
            ->generate($data);

        // Store in storage/app/public/qr-codes/
        $filename = "qr-codes/{$reference}.png";
        Storage::disk('public')->put($filename, $qrCode);

        return $filename;
    }

    /**
     * Get QR code image as base64
     */
    public function getQrCodeBase64(QrPayment $qrPayment): ?string
    {
        if (!$qrPayment->qr_code_image) {
            return null;
        }

        $path = storage_path('app/public/' . $qrPayment->qr_code_image);
        
        if (!file_exists($path)) {
            return null;
        }

        $imageData = file_get_contents($path);
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    /**
     * Get payment by reference
     */
    public function getByReference(string $reference): ?QrPayment
    {
        return QrPayment::with(['branch', 'user', 'customer'])
            ->where('qr_code_reference', $reference)
            ->first();
    }

    /**
     * Verify QR data
     */
    public function verifyQrData(string $qrData): ?array
    {
        try {
            $data = json_decode($qrData, true);
            
            if (!isset($data['reference'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Process payment
     */
    public function processPayment(string $reference, User $customer): array
    {
        $qrPayment = $this->getByReference($reference);

        if (!$qrPayment || !$qrPayment->canBePaid()) {
            return [
                'success' => false,
                'message' => 'Payment cannot be processed. QR code may be expired or invalid.',
            ];
        }

        $qrPayment->markAsCompleted($customer);

        return [
            'success' => true,
            'message' => 'Payment completed successfully',
            'payment' => $qrPayment->fresh(['branch', 'user', 'customer']),
        ];
    }

    /**
     * Cancel payment
     */
    public function cancelPayment(QrPayment $qrPayment): bool
    {
        if ($qrPayment->status !== 'pending') {
            return false;
        }

        $qrPayment->markAsCancelled();
        return true;
    }

    /**
     * Cleanup expired QR codes
     */
    public function cleanupExpired(): int
    {
        $expired = QrPayment::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expired as $payment) {
            $payment->markAsExpired();
        }

        return $expired->count();
    }
}
