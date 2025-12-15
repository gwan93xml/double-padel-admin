<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\AllowedIp;
use App\Models\User;
use App\Models\WhitelistIp;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RestrictIpAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // if (Auth::check()) {
        //     $user = User::find(Auth::user()->id);
        //     $whitelistIp = $user->whitelistIp;
        //     if (!$whitelistIp) {
        //         abort(403);
        //     }
        //     $ipAddresses = $whitelistIp->ip_addresses;
        //     $currentIp = $this->getClientIp($request);
        //     foreach ($ipAddresses as $ipAddress) {
        //         if ($ipAddress === '*') {
        //             return $next($request);
        //         }
        //         if ($this->isIpAllowed($currentIp, [$ipAddress])) {
        //             return $next($request);
        //         }
        //     }
        //     abort(403);
        // }

        return $next($request);
    }

    /**
     * Check if IP is allowed (supports both individual IPs and CIDR ranges)
     */
    private function isIpAllowed(string $clientIp, array $allowedIps): bool
    {
        foreach ($allowedIps as $allowedIp) {
            $allowedIp = trim($allowedIp);

            // Cek jika ini adalah CIDR range
            if (strpos($allowedIp, '/') !== false) {
                if ($this->ipInRange($clientIp, $allowedIp)) {
                    return true;
                }
            } else {
                // Exact IP match
                if ($clientIp === $allowedIp) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private function ipInRange(string $ip, string $cidr): bool
    {
        list($subnet, $mask) = explode('/', $cidr);

        if (!filter_var($ip, FILTER_VALIDATE_IP) || !filter_var($subnet, FILTER_VALIDATE_IP)) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - (int)$mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Get the real client IP address
     */
    private function getClientIp(Request $request): string
    {
        // Cek header yang umum digunakan oleh proxy/load balancer
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            $ip = $request->server($header);
            if (!empty($ip) && $ip !== 'unknown') {
                // Jika ada multiple IP (separated by comma), ambil yang pertama
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }

                // Validasi format IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback ke IP dari request
        return $request->ip();
    }
}
