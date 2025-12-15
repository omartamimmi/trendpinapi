<?php

namespace Modules\Notification\app\Services;

use App\Models\User;
use Modules\Notification\app\Jobs\SendBulkNotificationJob;
use Modules\Notification\app\Jobs\SendEventNotificationJob;
use Modules\Notification\app\Models\NotificationMessage;

/**
 * Async wrapper for notification services
 * Dispatches notifications to queue instead of sending synchronously
 */
class AsyncNotificationService
{
    /**
     * Queue an event notification
     */
    public static function sendEventNotification(
        string $eventId,
        string $recipientType,
        User|int $recipient,
        array $placeholders = []
    ): void {
        $userId = $recipient instanceof User ? $recipient->id : $recipient;

        SendEventNotificationJob::dispatch(
            $eventId,
            $recipientType,
            $userId,
            $placeholders
        );
    }

    /**
     * Queue a new customer notification
     */
    public static function sendNewCustomerNotification(User $user): void
    {
        self::sendEventNotification(
            'new_customer',
            'customer',
            $user,
            [
                'customer_name' => $user->name,
                'customer_email' => $user->email,
            ]
        );

        // Also notify admins
        self::notifyAdmins('new_customer', [
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'customer_phone' => $user->phone ?? 'N/A',
        ]);
    }

    /**
     * Queue a new retailer notification
     */
    public static function sendNewRetailerNotification(User $user): void
    {
        self::sendEventNotification(
            'new_retailer',
            'retailer',
            $user,
            [
                'retailer_name' => $user->name,
                'retailer_email' => $user->email,
            ]
        );

        // Also notify admins
        self::notifyAdmins('new_retailer', [
            'retailer_name' => $user->name,
            'retailer_email' => $user->email,
            'retailer_phone' => $user->phone ?? 'N/A',
        ]);
    }

    /**
     * Queue a retailer approved notification
     */
    public static function sendRetailerApprovedNotification(User $user): void
    {
        self::sendEventNotification(
            'retailer_approved',
            'retailer',
            $user,
            [
                'retailer_name' => $user->name,
            ]
        );
    }

    /**
     * Queue a retailer rejected notification
     */
    public static function sendRetailerRejectedNotification(User $user, string $reason = ''): void
    {
        self::sendEventNotification(
            'retailer_rejected',
            'retailer',
            $user,
            [
                'retailer_name' => $user->name,
                'rejection_reason' => $reason,
            ]
        );
    }

    /**
     * Queue notifications to all admins
     */
    public static function notifyAdmins(string $eventId, array $placeholders = []): void
    {
        $admins = User::role('admin')->get();

        foreach ($admins as $admin) {
            SendEventNotificationJob::dispatch(
                $eventId,
                'admin',
                $admin->id,
                $placeholders
            );
        }
    }

    /**
     * Queue a bulk notification message
     */
    public static function sendBulkNotification(NotificationMessage $message): void
    {
        $message->update(['status' => 'queued']);
        SendBulkNotificationJob::dispatch($message->id);
    }

    /**
     * Queue a bulk notification by ID
     */
    public static function sendBulkNotificationById(int $messageId): void
    {
        $message = NotificationMessage::find($messageId);
        if ($message) {
            $message->update(['status' => 'queued']);
            SendBulkNotificationJob::dispatch($messageId);
        }
    }
}
