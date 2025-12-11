<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ActivityLogger;

/**
 * Copilot, implement a full notification UI for Printair v2.
 *
 * Requirements:
 * - Methods:
 *   - index(): show a paginated list (15 per page) of ALL notifications for the authenticated user (read + unread), newest first.
 *   - unread(): show only unread notifications (reuse same Blade table component).
 *   - markAsRead(string $id): mark a single notification as read and redirect back.
 *   - markAllAsRead(): mark all notifications for the user as read and redirect back with a success flash message.
 *   - settings(): show a simple notification settings form (toggles for email_notifications, system_notifications).
 *   - updateSettings(Request $request): validate and save those settings on the user model (cast booleans) and redirect back with status.
 *
 * - Use the "notifications.index" and "notifications.unread" views for listing.
 * - Use the "notifications.settings" view for the settings form.
 * - Always use $request->user() instead of Auth::user().
 * - Return Blade views with compact() or explicit array.
 * - For pagination use ->paginate(15)->withQueryString().
 */
class NotificationController extends Controller
{
    

    public function index(Request $request)
    {
        $user = $request->user();

        ActivityLogger::log(
            $user,
            'notifications.index',
            'Viewed all notifications'
        );

        $notifications = $user->notifications()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => 'all',
        ]);
    }

    public function unread(Request $request)
    {
        $user = $request->user();

        ActivityLogger::log(
            $user,
            'notifications.unread',
            'Viewed unread notifications'
        );

        $notifications = $user->unreadNotifications()
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            'filter' => 'unread',
        ]);
    }

    public function markAsRead(Request $request, string $notification)
    {
        $user = $request->user();

        $record = $user->notifications()
            ->where('id', $notification)
            ->firstOrFail();

        $record->markAsRead();

        ActivityLogger::log(
            $user,
            'notifications.mark_as_read',
            'Marked a notification as read',
            ['notification_id' => $notification]
        );

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();

        $user->unreadNotifications->markAsRead();

        ActivityLogger::log(
            $user,
            'notifications.mark_all_as_read',
            'Marked all notifications as read'
        );

        return back()->with('status', 'All notifications marked as read.');
    }

    public function settings(Request $request)
    {
        ActivityLogger::log(
            $request->user(),
            'notifications.settings',
            'Viewed notification settings'
        );

        return view('notifications.settings', [
            'user' => $request->user(),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $data = $request->validate([
            'email_notifications' => ['sometimes', 'boolean'],
            'system_notifications' => ['sometimes', 'boolean'],
        ]);

        $user = $request->user();

        // if checkbox is unchecked, it won't come – so default to false.
        $user->email_notifications = (bool) ($data['email_notifications'] ?? false);
        $user->system_notifications = (bool) ($data['system_notifications'] ?? false);

        $user->save();

        ActivityLogger::log(
            $user,
            'notifications.settings.update',
            'Updated notification settings',
            [
                'email_notifications' => $user->email_notifications,
                'system_notifications' => $user->system_notifications,
            ]
        );

        return back()->with('status', 'Notification settings updated successfully.');
    }
}
