
@extends('layouts.app')

@section('title', 'Activity Log Details')
@section('page-title', 'Activity Log Details')

@section('content')
@php
    $prettyJson = function ($value) {
        if (is_null($value) || $value === '') {
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
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) ?: 'No details recorded.';
    };
@endphp

<div class="container-fluid py-3">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Activity Log Details</h2>
            <p class="text-muted mb-0">
                Review the details of this recorded system activity.
            </p>
        </div>

        <a href="{{ route('activity-logs.index') }}"
           class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i>
            Back to Activity Logs
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-clipboard-list text-success me-2"></i>
                Activity Information
            </h5>
        </div>

        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="text-muted small">Log ID</label>
                    <div class="fw-semibold">#{{ $log->id }}</div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">Action</label>
                    <div>
                        <span class="badge text-bg-primary">
                            {{ ucwords(str_replace(['_', '-'], ' ', $log->action ?? 'Activity')) }}
                        </span>
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">Performed By</label>
                    <div class="fw-semibold">
                        {{ $log->user?->name ?? 'System / Unknown' }}
                    </div>
                    <small class="text-muted">
                        User ID: {{ $log->user_id ?? '—' }}
                    </small>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">Date and Time</label>
                    <div class="fw-semibold">
                        {{ $log->created_at?->format('d M Y, h:i:s A') ?? '—' }}
                    </div>
                </div>

                <div class="col-12">
                    <label class="text-muted small">Description</label>
                    <div class="p-3 bg-light rounded">
                        {{ $log->description ?: 'No description recorded.' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">Affected Record Type</label>
                    <div class="fw-semibold">
                        {{ $log->auditable_type ? class_basename($log->auditable_type) : '—' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">Affected Record ID</label>
                    <div class="fw-semibold">
                        {{ $log->auditable_id ?? '—' }}
                    </div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">IP Address</label>
                    <div>{{ $log->ip_address ?: 'Not recorded' }}</div>
                </div>

                <div class="col-md-6">
                    <label class="text-muted small">User Agent</label>
                    <div class="text-break">
                        {{ $log->user_agent ?: 'Not recorded' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-clock-rotate-left text-warning me-2"></i>
                        Previous Values
                    </h5>
                </div>

                <div class="card-body">
                    <pre class="activity-json">{{ $prettyJson($log->old_values) }}</pre>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-file-circle-check text-success me-2"></i>
                        New Values
                    </h5>
                </div>

                <div class="card-body">
                    <pre class="activity-json">{{ $prettyJson($log->new_values) }}</pre>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.activity-json {
    background: #f8f9fa;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 16px;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    font-size: 13px;
    line-height: 1.7;
    margin-bottom: 0;
    min-height: 120px;
}

@media print {
    .app-sidebar,
    .app-header,
    .navbar,
    .btn {
        display: none !important;
    }

    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
    }
}
</style>
@endsection
