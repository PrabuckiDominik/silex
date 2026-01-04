<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function start(Request $request)
    {
        $activity = Activity::create([
            'user_id' => $request->user()->id,
            'started_at' => now(),
        ]);

        return response()->json($activity, 201);
    }

    public function finish(Request $request, Activity $activity)
    {
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $activity->update([
            'ended_at' => now(),
            'name' => $request->input('name'),
            'type' => $request->input('type'),
            'note' => $request->input('note'),
            'photo_url' => $request->input('photo_url'),
            'distance' => $request->input('distance'),
        ]);

        return response()->json($activity);
    }
    public function destroy(Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $activity->delete();

        return response()->json([
            'message' => 'Activity deleted successfully'
        ]);
    }
}
