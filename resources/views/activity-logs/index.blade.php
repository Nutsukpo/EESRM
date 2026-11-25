
@extends('layouts.app')

@section('title', 'Activity Logs')
@section('page-title', 'Activity Logs')

@section('content')
@php
    $formatDetails = function ($value) {
        if ($value === null || $value === '') {
            return 'No details recorded.';
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        return json_encode(
            $value,
            JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
                | JSON_UNESCAPED_UNICODE
        ) ?: 'No details recorded.';
    };
@endphp

<div class="container-fluid py-3">

    {{-- Page heading --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">
                <i class="fa-solid fa-clipboard-list me-2"></i>
                Activity Logs
            </h2>
            <p class="text-muted mb-0">
                Review and investigate actions performed in the restaurant system.
            </p>
        </div>

        <button type="button" class="btn btn-outline-dark" onclick="window.print()">
            <i class="fa-solid fa-print me-1"></i>
            Print
        </button>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">All Recorded Activities</div>
                    <h3 class="fw-bold mt-2 mb-0">
                        {{ number_format((int) $totalActivities) }}
                    </h3>
                    <small class="text-muted">Total audit records</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Activities Today</div>
                    <h3 class="fw-bold mt-2 mb-0 text-success">
                        {{ number_format((int) $todayActivities) }}
                    </h3>
                    <small class="text-muted">Records created today</small>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small">Matching Activities</div>
                    <h3 class="fw-bold mt-2 mb-0">
                        {{ number_format((int) $logs->total()) }}
                    </h3>
                    <small class="text-muted">Based on the current filters</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4 activity-filters">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-filter me-2"></i>
                Filter Activity History
            </h5>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('activity-logs.index') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-6 col-xl-3">
                        <label for="search" class="form-label">Search</label>
                        <input
                            type="search"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ request('search') }}"
                            maxlength="150"
                            placeholder="Description, action, record..."
                        >
                    </div>

                    <div class="col-md-6 col-xl-2">
                        <label for="action" class="form-label">Action</label>
                        <select name="action" id="action" class="form-select">
                            <option value="">All actions</option>

                            @foreach ($actions as $actionOption)
                                <option
                                    value="{{ $actionOption }}"
                                    @selected((string) request('action') === (string) $actionOption)
                                >
                                    {{ ucwords(str_replace(['_', '-'], ' ', $actionOption)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-xl-2">
                        <label for="user_id" class="form-label">User</label>
                        <select name="user_id" id="user_id" class="form-select">
                            <option value="">All users</option>

                            @foreach ($users as $userOption)
                                <option
                                    value="{{ $userOption->id }}"
                                    @selected((string) request('user_id') === (string) $userOption->id)
                                >
                                    {{ $userOption->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 col-xl-2">
                        <label for="from" class="form-label">From date</label>
                        <input
                            type="date"
                            name="from"
                            id="from"
                            class="form-control"
                            value="{{ request('from') }}"
                        >
                    </div>

                    <div class="col-md-6 col-xl-2">
                        <label for="to" class="form-label">To date</label>
                        <input
                            type="date"
                            name="to"
                            id="to"
                            class="form-control"
                            value="{{ request('to') }}"
                        >
                    </div>

                    <div class="col-md-6 col-xl-1 d-grid">
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-magnifying-glass me-1"></i>
                            Filter
                        </button>
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2">
                        <a
                            href="{{ route('activity-logs.index') }}"
                            class="btn btn-outline-secondary btn-sm"
                        >
                            <i class="fa-solid fa-rotate-left me-1"></i>
                            Clear filters
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Activity table --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold mb-0">System Activity History</h5>

            <span class="badge text-bg-light border">
                {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }}
                of {{ number_format((int) $logs->total()) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">Date &amp; Time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>Record</th>
                        <th>IP Address</th>
                        <th class="text-end">Details</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-semibold">
                                    {{ $log->created_at?->format('d M Y') ?? '—' }}
                                </div>
                                <small class="text-muted">
                                    {{ $log->created_at?->format('h:i:s A') ?? '—' }}
                                </small>
                            </td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $log->user?->name ?? 'System / Unknown' }}
                                </div>
                                <small class="text-muted">
                                    User ID: {{ $log->user_id ?? '—' }}
                                </small>
                            </td>

                            <td>
                                <span class="badge text-bg-primary">
                                    {{ ucwords(str_replace(['_', '-'], ' ', $log->action ?? 'Activity')) }}
                                </span>
                            </td>

                            <td style="min-width: 220px; max-width: 360px;">
                                {{ $log->description ?: 'No description recorded.' }}
                            </td>

                            <td>
                                <div>
                                    {{ filled($log->auditable_type) ? class_basename($log->auditable_type) : '—' }}
                                </div>

                                @if ($log->auditable_id !== null)
                                    <small class="text-muted">
                                        ID: {{ $log->auditable_id }}
                                    </small>
                                @endif
                            </td>

                            <td class="text-nowrap">
                                {{ $log->ip_address ?: '—' }}
                            </td>

                            <td class="text-end text-nowrap">
                                <a
                                    href="{{ route('activity-logs.show', $log) }}"
                                    class="btn btn-sm btn-outline-primary"
                                    aria-label="View activity log {{ $log->id }}"
                                >
                                    <i class="fa-solid fa-eye me-1"></i>
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fa-solid fa-clipboard-list fa-2x text-muted mb-3"></i>
                                <h6 class="fw-bold">No activity records found</h6>
                                <p class="text-muted mb-3">
                                    No audit records match the selected filters.
                                </p>
                                <a
                                    href="{{ route('activity-logs.index') }}"
                                    class="btn btn-outline-secondary btn-sm"
                                >
                                    Clear filters
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="card-footer bg-white">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

</div>

<style>
    .activity-filters .form-label {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .table td {
        vertical-align: middle;
    }

    @media print {
        .app-sidebar,
        .app-header,
        .navbar,
        .activity-filters,
        button,
        .btn,
        .pagination {
            display: none !important;
        }

        body,
        main,
        .content-wrapper,
        .container-fluid {
            background: #fff !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .card {
            box-shadow: none !important;
            border: 1px solid #ddd !important;
        }

        table {
            font-size: 10px !important;
        }

        th,
        td {
            padding: 5px !important;
        }
    }
</style>
@endsection
