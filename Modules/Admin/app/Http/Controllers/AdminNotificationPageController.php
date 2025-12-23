<?php

namespace Modules\Admin\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Notification\app\Models\NotificationMessage;
use Modules\Notification\app\Models\NotificationProvider;
use Modules\Notification\app\Models\NotificationTemplate;

class AdminNotificationPageController extends Controller
{
    public function providers(): Response
    {
        $providers = NotificationProvider::orderBy('type')->orderBy('priority')->get();
        return Inertia::render('Admin/NotificationProviders', [
            'providers' => $providers,
        ]);
    }

    public function index(): Response
    {
        $notifications = NotificationMessage::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return Inertia::render('Admin/Notifications', [
            'notifications' => $notifications,
        ]);
    }

    public function send(): Response
    {
        $templates = NotificationTemplate::active()->get();
        $providers = NotificationProvider::active()->get();

        return Inertia::render('Admin/SendNotification', [
            'templates' => $templates,
            'providers' => $providers,
        ]);
    }

    public function templates(): Response
    {
        $templates = NotificationTemplate::orderBy('created_at', 'desc')->get();
        return Inertia::render('Admin/NotificationTemplates', [
            'templates' => $templates,
        ]);
    }

    public function settings(): Response
    {
        return Inertia::render('Admin/NotificationSettings');
    }

    public function credentials(): Response
    {
        $statuses = [
            'smtp' => $this->checkSmtpStatus(),
            'sms' => $this->checkSmsStatus(),
            'whatsapp' => $this->checkWhatsappStatus(),
            'push' => $this->checkPushStatus(),
        ];

        return Inertia::render('Admin/NotificationCredentials', [
            'statuses' => $statuses,
        ]);
    }

    private function checkSmtpStatus(): string
    {
        $host = config('mail.mailers.smtp.host');
        $username = config('mail.mailers.smtp.username');
        return ($host && $username) ? 'configured' : 'not_configured';
    }

    private function checkSmsStatus(): string
    {
        $provider = NotificationProvider::where('type', 'sms')->where('is_active', true)->first();
        return $provider ? 'configured' : 'not_configured';
    }

    private function checkWhatsappStatus(): string
    {
        $provider = NotificationProvider::where('type', 'whatsapp')->where('is_active', true)->first();
        return $provider ? 'configured' : 'not_configured';
    }

    private function checkPushStatus(): string
    {
        $provider = NotificationProvider::where('type', 'push')->where('is_active', true)->first();
        return $provider ? 'configured' : 'not_configured';
    }
}
