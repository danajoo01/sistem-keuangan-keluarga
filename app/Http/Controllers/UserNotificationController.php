<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class UserNotificationController extends Controller
{
    public function visit(Request $request, string $notification): RedirectResponse
    {
        /** @var DatabaseNotification $notificationModel */
        $notificationModel = $request->user()->notifications()->findOrFail($notification);

        if ($notificationModel->read_at === null) {
            $notificationModel->markAsRead();
        }

        return redirect()->to($notificationModel->data['url'] ?? route('dashboard'));
    }
}
