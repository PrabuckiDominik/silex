<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AdminNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function send(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:500',
            'type' => 'required|in:different,patch,rotation',
        ]);

        $users = User::all();

        foreach ($users as $user) {
            $user->notify(new AdminNotification(
                $request->title,
                $request->body,
                $request->type
            ));
        }

        return response()->json(['message' => 'Powiadomienia wysłane.']);
    }
    public function getUserNotifications(Request $request)
    {
        return $request->user()->notifications()->latest()->paginate(10);
    }
}
