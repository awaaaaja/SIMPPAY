<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('webhooks.secret');

        if (! $secret) {
            abort(500, 'Webhook secret not configured.');
        }

        $signature = $request->header('X-Signature');

        if (! $signature) {
            abort(401, 'Missing webhook signature.');
        }

        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $payload, $secret);

        if (! hash_equals($expected, $signature)) {
            abort(403, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
