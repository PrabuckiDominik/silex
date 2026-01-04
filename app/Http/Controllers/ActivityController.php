<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function start(Request $request)
    {
        $activity = Activity::create([
            'user_id' => $request->user()->id,
            'started_at' => $request->input('started_at') ?? now(),
            ]);

        return response()->json($activity, 201);
    }

    public function finish(Request $request, Activity $activity)
    {
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->only(['name', 'type', 'note', 'distance']);
        $data['ended_at'] = $request->input('ended_at') ?? now();

        if ($request->hasFile('photo')) {
            if ($activity->photo_path) {
                Storage::disk('private')->delete($activity->photo_path);
            }

            $path = $request->file('photo')->store('activities', 'private');
            $data['photo_path'] = $path;
        }

        $activity->update($data);

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
    public function index(Request $request)
    {
        $activities = $request->user()->activities()->orderBy('started_at', 'desc')->get();

        return response()->json($activities);
    }
    public function show(Activity $activity, Request $request)
    {
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($activity);
    }
    public function stats(Request $request)
    {
        $user = $request->user();

        $activities = $user->activities()
            ->whereNotNull('ended_at')
            ->get();

        $totalActivities = $activities->count();

        $totalDistance = $activities->sum('distance');

        $totalTimeSeconds = $activities->sum(function ($activity) {
            return $activity->started_at && $activity->ended_at
                ? $activity->started_at->diffInSeconds($activity->ended_at)
                : 0;
        });

        $monthly = $activities
            ->groupBy(fn ($a) => $a->started_at->format('Y-m'))
            ->map(function ($group, $month) {

                $time = $group->sum(function ($activity) {
                    return $activity->started_at->diffInSeconds($activity->ended_at);
                });

                return [
                    'month' => $month,
                    'activities' => $group->count(),
                    'distance' => $group->sum('distance'),
                    'time_seconds' => $time,
                    'time_human' => $this->formatDuration($time),
                ];
            })
            ->values();

        return response()->json([
            'overall' => [
                'activities' => $totalActivities,
                'distance' => $totalDistance,
                'time_seconds' => $totalTimeSeconds,
                'time_human' => $this->formatDuration($totalTimeSeconds),
            ],
        ]);
    }
    public function photo(Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$activity->photo_path || !Storage::disk('private')->exists($activity->photo_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('private')->path($activity->photo_path));
    }
    private function formatDuration(int $seconds): string
    {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $seconds = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
