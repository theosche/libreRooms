<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prevents the session from being saved at the end of the request.
 *
 * Use this on routes that read the session (for auth) but should not
 * write it back, avoiding race conditions where a slow request (e.g.,
 * CalDAV fetch) overwrites session data written by concurrent requests.
 */
class ReadOnlySession
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Replace the session handler with a no-op writer so that
        // StartSession::saveSession() becomes harmless.
        $ref = new \ReflectionProperty(Store::class, 'handler');
        $ref->setValue($request->session(), new class implements SessionHandlerInterface
        {
            public function open(string $path, string $name): bool
            {
                return true;
            }

            public function close(): bool
            {
                return true;
            }

            public function read(string $id): string|false
            {
                return '';
            }

            public function write(string $id, string $data): bool
            {
                return true;
            }

            public function destroy(string $id): bool
            {
                return true;
            }

            public function gc(int $max_lifetime): int|false
            {
                return 0;
            }
        });

        return $response;
    }
}
