<?php

namespace Modules\Admin\app\Listeners;

use Illuminate\Support\Facades\Log;
use Modules\Admin\app\Events\AdminLoggedIn;

class LogAdminActivity
{
    public function handle(AdminLoggedIn $event): void
    {
        Log::channel('admin')->info('Admin activity logged', [
            'admin_id' => $event->admin->id,
            'admin_email' => $event->admin->email,
            'event' => 'logged_in',
            'timestamp' => now()->toISOString(),
        ]);
    }
}
