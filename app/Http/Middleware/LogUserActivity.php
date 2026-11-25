<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogUserActivity
{
    /**
     * Routes that should not create request-level audit entries.
     *
     * Activity log viewing is excluded to prevent recursive logging.
     * Logout is excluded because the authenticated session may be
     * invalidated while its response is being generated.
     */
    private const EXCLUDED_ROUTES = [
        'activity-logs.index',
        'activity-logs.show',
        'logout',
    ];

    /**
     * Request fields that must never be stored in audit metadata.
     */
    private const SENSITIVE_FIELDS = [
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
        'token',
        '_token',
        'authorization',
        'api_key',
        'secret',
        'client_secret',
        'access_token',
        'refresh_token',
        'card_number',
        'cardholder_name',
        'cvv',
        'cvc',
        'pin',
        'otp',
        'security_code',
        'bank_account',
        'account_number',
    ];

    /**
     * Process a request and record its outcome.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userId = Auth::id();
        $routeName = $request->route()?->getName();

        try {
            $response = $next($request);
        } catch (Throwable $exception) {
            // Record the exception before allowing Laravel to handle it.
            $this->recordException(
                $request,
                $userId,
                $routeName,
                $exception
            );

            throw $exception;
        }

        // Ignore requests made by guests. Login attempts need separate
        // authentication-event logging because no user may be authenticated.
        if (!$userId) {
            return $response;
        }

        // Only log named application routes.
        if (!$routeName) {
            return $response;
        }

        // Avoid generating an audit entry for viewing the audit log itself.
        if (in_array($routeName, self::EXCLUDED_ROUTES, true)) {
            return $response;
        }

        // Ignore successful AJAX polling requests to reduce noise.
        if (
            $request->ajax()
            && $request->isMethod('GET')
            && $response->getStatusCode() < 400
        ) {
            return $response;
        }

        $this->recordRequest(
            $request,
            $response,
            $userId,
            $routeName
        );

        return $response;
    }

    /**
     * Record a completed request.
     */
    private function recordRequest(
        Request $request,
        Response $response,
        int|string $userId,
        string $routeName
    ): void {
        $method = strtoupper($request->method());
        $statusCode = $response->getStatusCode();

        $action = $this->resolveAction($method, $statusCode);

        $routeLabel = Str::headline(
            str_replace(['.', '-', '_'], ' ', $routeName)
        );

        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => $action,
                'auditable_type' => null,
                'auditable_id' => null,
                'description' => sprintf(
                    '%s | %s %s | HTTP %d',
                    $routeLabel,
                    $method,
                    $request->path(),
                    $statusCode
                ),
                'old_values' => null,
                'new_values' => [
                    'route' => $routeName,
                    'method' => $method,
                    'path' => $request->path(),
                    'status_code' => $statusCode,
                    'outcome' => $statusCode >= 400
                        ? 'failed'
                        : 'successful',
                    'parameters' => $this->safeInput($request),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit(
                    (string) $request->userAgent(),
                    2000
                ),
            ]);
        } catch (Throwable $exception) {
            $this->reportLoggingFailure(
                $userId,
                $routeName,
                $exception
            );
        }
    }

    /**
     * Record an exception raised during request processing.
     */
    private function recordException(
        Request $request,
        int|string|null $userId,
        ?string $routeName,
        Throwable $exception
    ): void {
        if (!$userId || !$routeName) {
            return;
        }

        if (in_array($routeName, self::EXCLUDED_ROUTES, true)) {
            return;
        }

        try {
            AuditLog::create([
                'user_id' => $userId,
                'action' => 'request_exception',
                'auditable_type' => null,
                'auditable_id' => null,
                'description' => sprintf(
                    'Exception during %s %s: %s',
                    strtoupper($request->method()),
                    $request->path(),
                    class_basename($exception)
                ),
                'old_values' => null,
                'new_values' => [
                    'route' => $routeName,
                    'method' => strtoupper($request->method()),
                    'path' => $request->path(),
                    'exception_type' => class_basename($exception),
                ],
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit(
                    (string) $request->userAgent(),
                    2000
                ),
            ]);
        } catch (Throwable $loggingException) {
            $this->reportLoggingFailure(
                $userId,
                $routeName,
                $loggingException
            );
        }
    }

    /**
     * Determine the action category from the HTTP method and response.
     */
    private function resolveAction(
        string $method,
        int $statusCode
    ): string {
        if ($statusCode >= 400) {
            return 'request_failed';
        }

        return match ($method) {
            'GET', 'HEAD' => 'page_viewed',
            'POST' => 'action_submitted',
            'PUT', 'PATCH' => 'record_updated',
            'DELETE' => 'record_deleted',
            default => 'request_completed',
        };
    }

    /**
     * Return sanitized request input.
     *
     * Uploaded files, nested arrays, and objects are omitted.
     * Sensitive fields are removed recursively.
     */
    private function safeInput(Request $request): array
    {
        $input = $request->except(self::SENSITIVE_FIELDS);

        return $this->sanitizeValue($input);
    }

    /**
     * Sanitize arbitrary request data recursively.
     */
    private function sanitizeValue(mixed $value, int $depth = 0): mixed
    {
        if ($depth >= 4) {
            return '[nested data omitted]';
        }

        if (is_array($value)) {
            $safe = [];

            foreach ($value as $key => $item) {
                if (
                    is_string($key)
                    && in_array(
                        strtolower($key),
                        self::SENSITIVE_FIELDS,
                        true
                    )
                ) {
                    continue;
                }

                // Avoid storing arbitrary nested form payloads.
                if (is_array($item) || is_object($item)) {
                    $safe[$key] = '[structured data omitted]';
                    continue;
                }

                if (is_string($item)) {
                    $safe[$key] = Str::limit($item, 150);
                    continue;
                }

                if (
                    is_null($item)
                    || is_bool($item)
                    || is_int($item)
                    || is_float($item)
                ) {
                    $safe[$key] = $item;
                } else {
                    $safe[$key] = '[data omitted]';
                }
            }

            return $safe;
        }

        if (is_string($value)) {
            return Str::limit($value, 150);
        }

        if (
            is_null($value)
            || is_bool($value)
            || is_int($value)
            || is_float($value)
        ) {
            return $value;
        }

        return '[data omitted]';
    }

    /**
     * Write logging failures to Laravel's application log.
     */
    private function reportLoggingFailure(
        int|string|null $userId,
        ?string $routeName,
        Throwable $exception
    ): void {
        Log::error('Activity audit logging failed.', [
            'user_id' => $userId,
            'route' => $routeName,
            'exception' => $exception->getMessage(),
        ]);
    }
}
