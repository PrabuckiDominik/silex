<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function store(Request $request)
    {
        $activity = Activity::create([
            'user_id'  => $request->user()->id,
            'name'     => $request->input('name'),
            'type'     => $request->input('type'),
            'note'     => $request->input('note'),
            'photo_path'=> $request->input('photo_path'),
            'distance' => $request->input('distance'),
            'time'     => $request->input('time'), // sekundy
        ]);

        return response()->json($activity, 201);
    }

    public function update(Request $request, Activity $activity)
    {
        if ($activity->user_id !== $request->user()->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $activity->update($request->only([
            'name',
            'type',
            'note',
            'photo_path',
            'distance',
            'time',
        ]));

        return response()->json($activity);
    }

    public function destroy(Activity $activity)
    {
        if ($activity->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $activity->delete();

        return response()->json(['message' => 'Activity deleted successfully']);
    }

    public function index(Request $request)
    {
        return response()->json(
            $request->user()
                ->activities()
                ->latest()
                ->get()
        );
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
        $activities = $request->user()->activities()->get();

        $totalTime = $activities->sum('time');
        $totalDistance = $activities->sum('distance');

        // Globalny breakdown po typie treningu
        $typeBreakdown = $activities
            ->groupBy('type')
            ->map(function ($group, $type) {
                $time = $group->sum('time');
                return [
                    'activities'   => $group->count(),
                    'distance'     => $group->sum('distance'),
                    'time_seconds' => $time,
                    'time_human'   => $this->formatDuration($time),
                ];
            });

        // Monthly stats z breakdown po typie
        $monthly = $activities
            ->groupBy(fn ($a) => $a->created_at->format('Y-m'))
            ->map(function ($group, $month) {
                $time = $group->sum('time');
                $distance = $group->sum('distance');

                $typeBreakdown = $group
                    ->groupBy('type')
                    ->map(function ($typeGroup, $type) {
                        $time = $typeGroup->sum('time');
                        return [
                            'activities'   => $typeGroup->count(),
                            'distance'     => $typeGroup->sum('distance'),
                            'time_seconds' => $time,
                            'time_human'   => $this->formatDuration($time),
                        ];
                    });

                return [
                    'month'        => $month,
                    'activities'   => $group->count(),
                    'distance'     => $distance,
                    'time_seconds' => $time,
                    'time_human'   => $this->formatDuration($time),
                    'types'        => $typeBreakdown,
                ];
            })
            ->values();

        return response()->json([
            'overall' => [
                'activities'   => $activities->count(),
                'distance'     => $totalDistance,
                'time_seconds' => $totalTime,
                'time_human'   => $this->formatDuration($totalTime),
                'types'        => $typeBreakdown,
            ],
            'monthly' => $monthly,
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
