<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Ensure only owners can access activity logs.
     */
    private function authorizeActivityLogs(): void
    {
        abort_unless(
            auth()->user()?->role === 'owner',
            403,
            'You are not authorized to access activity logs.'
        );
    }

    /**
     * Display the system activity history.
     */
    public function index(Request $request)
    {
        $this->authorizeActivityLogs();

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'action' => ['nullable', 'string', 'max:100'],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:from',
            ],
        ]);

        $query = AuditLog::query()->with('user');

        // Search activity descriptions, actions, and affected records.
        if (!empty($validated['search'])) {
            $search = $validated['search'];

            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%");
            });
        }

        // Filter by action.
        if (!empty($validated['action'])) {
            $query->where('action', $validated['action']);
        }

        // Filter by user.
        if (!empty($validated['user_id'])) {
            $query->where('user_id', $validated['user_id']);
        }

        // Filter by date range.
        if (!empty($validated['from'])) {
            $query->whereDate('created_at', '>=', $validated['from']);
        }

        if (!empty($validated['to'])) {
            $query->whereDate('created_at', '<=', $validated['to']);
        }

        // Paginate the activity history.
        $logs = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // Available action filters.
        $actions = AuditLog::query()
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Users who have recorded activity.
        $users = AuditLog::query()
            ->whereNotNull('user_id')
            ->join('users', 'audit_logs.user_id', '=', 'users.id')
            ->select('users.id', 'users.name')
            ->distinct()
            ->orderBy('users.name')
            ->get();

        // Activity summary.
        $totalActivities = AuditLog::count();

        $todayActivities = AuditLog::query()
            ->whereDate('created_at', today())
            ->count();

        return view('activity-logs.index', compact(
            'logs',
            'actions',
            'users',
            'totalActivities',
            'todayActivities'
        ));
    }

    /**
     * Display an individual activity log.
     */
    public function show(AuditLog $log)
    {
        $this->authorizeActivityLogs();

        $log->load('user');

        return view('activity-logs.show', compact('log'));
    }
}
