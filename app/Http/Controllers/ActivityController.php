<?php
// app/Http/Controllers/ActivityController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // POST /api/activity
    // Receives a ping from the Python tracker every minute.
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        // 1. Validate the incoming data
        $data = $request->validate([
            'user_id'        => 'required|integer|exists:users,id',
            'timestamp'      => 'required|date',
            'status'         => 'required|in:working,idle,offline',
            'active_seconds' => 'required|integer|min:0|max:3600',
        ]);

        // 2. Save to database
        $log = ActivityLog::create([
            'user_id'        => $data['user_id'],
            'logged_at'      => Carbon::parse($data['timestamp']),
            'status'         => $data['status'],
            'active_seconds' => $data['active_seconds'],
        ]);

        // 3. Return a success response
        return response()->json([
            'message' => 'Activity logged successfully',
            'log_id'  => $log->id,
        ], 201);
    }


    // ─────────────────────────────────────────────────────────────
    // GET /api/users/status
    // Returns the latest status of ALL users (for the dashboard).
    // ─────────────────────────────────────────────────────────────
    public function allStatus(): JsonResponse
    {
        $users = User::all();

        $result = $users->map(function (User $user) {

            // Get the most recent log entry for this user
            $latestLog = ActivityLog::where('user_id', $user->id)
                ->latest('logged_at')
                ->first();

            // If no ping received within 2 minutes, mark as offline
            $isRecent = $latestLog
                && $latestLog->logged_at->gt(now()->subMinutes(2));

            $currentStatus = $isRecent ? $latestLog->status : 'offline';

            // Find today's first "working" log (start time)
            $startTime = ActivityLog::where('user_id', $user->id)
                ->where('status', 'working')
                ->whereDate('logged_at', today())
                ->oldest('logged_at')
                ->value('logged_at');

            // Sum all active_seconds today
            $totalActiveSeconds = ActivityLog::where('user_id', $user->id)
                ->whereDate('logged_at', today())
                ->sum('active_seconds');

            return [
                'user_id'             => $user->id,
                'name'                => $user->name,
                'status'              => $currentStatus,
                'last_seen'           => $latestLog?->logged_at,
                'start_time'          => $startTime,
                'total_active_seconds'=> (int) $totalActiveSeconds,
            ];
        });

        return response()->json($result);
    }


    // ─────────────────────────────────────────────────────────────
    // GET /api/users/{id}/daily
    // Returns all log entries for a specific user today.
    // ─────────────────────────────────────────────────────────────
    public function daily(int $userId): JsonResponse
    {
        $logs = ActivityLog::where('user_id', $userId)
            ->whereDate('logged_at', today())
            ->orderBy('logged_at')
            ->get();

        return response()->json($logs);
    }


    // ─────────────────────────────────────────────────────────────
    // GET /api/users/{id}/weekly
    // Returns a per-day summary for the last 7 days.
    // ─────────────────────────────────────────────────────────────
    public function weekly(int $userId): JsonResponse
    {
        $logs = ActivityLog::selectRaw(
            'DATE(logged_at)              AS date,
             SUM(active_seconds)          AS total_active_seconds,
             SUM(status = "working")      AS working_pings,
             SUM(status = "idle")         AS idle_pings'
        )
        ->where('user_id', $userId)
        ->where('logged_at', '>=', now()->subDays(7))
        ->groupBy('date')
        ->orderBy('date')
        ->get();

        return response()->json($logs);
    }
}
