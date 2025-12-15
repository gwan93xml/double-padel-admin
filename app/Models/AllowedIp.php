<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AllowedIp extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'description',
        'is_active',
        'created_by',
        'last_used_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime'
    ];

    /**
     * Get all active allowed IPs
     */
    public static function getActiveIps(): array
    {
        return static::where('is_active', true)
            ->pluck('ip_address')
            ->toArray();
    }

    /**
     * Check if an IP is allowed
     */
    public static function isIpAllowed(string $ip): bool
    {
        $allowedIps = static::getActiveIps();
        
        foreach ($allowedIps as $allowedIp) {
            // Check exact match
            if ($ip === $allowedIp) {
                // Update last used timestamp
                static::where('ip_address', $allowedIp)->update([
                    'last_used_at' => now()
                ]);
                return true;
            }
            
            // Check CIDR range
            if (strpos($allowedIp, '/') !== false && static::ipInRange($ip, $allowedIp)) {
                // Update last used timestamp
                static::where('ip_address', $allowedIp)->update([
                    'last_used_at' => now()
                ]);
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if IP is in CIDR range
     */
    private static function ipInRange(string $ip, string $cidr): bool
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
     * Validate IP address format
     */
    public static function validateIpFormat(string $ip): bool
    {
        // Check for CIDR range
        if (strpos($ip, '/') !== false) {
            list($subnet, $mask) = explode('/', $ip);
            return filter_var($subnet, FILTER_VALIDATE_IP) && 
                   is_numeric($mask) && 
                   $mask >= 0 && 
                   $mask <= 32;
        }
        
        // Check for individual IP
        return filter_var($ip, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Scope for active IPs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for recently used IPs
     */
    public function scopeRecentlyUsed($query, int $days = 30)
    {
        return $query->where('last_used_at', '>=', Carbon::now()->subDays($days));
    }
}