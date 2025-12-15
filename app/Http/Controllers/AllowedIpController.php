<?php

namespace App\Http\Controllers;

use App\Models\AllowedIp;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;

class AllowedIpController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allowedIps = AllowedIp::orderBy('created_at', 'desc')->paginate(15);
        
        return Inertia::render('Admin/AllowedIps/Index', [
            'allowedIps' => $allowedIps,
            'currentIp' => request()->ip(),
            'stats' => [
                'total' => AllowedIp::count(),
                'active' => AllowedIp::active()->count(),
                'recently_used' => AllowedIp::recentlyUsed(7)->count(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/AllowedIps/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'ip_address' => [
                'required',
                'string',
                'unique:allowed_ips,ip_address',
                function ($attribute, $value, $fail) {
                    if (!AllowedIp::validateIpFormat($value)) {
                        $fail('The IP address format is invalid. Use format like 192.168.1.1 or 192.168.1.0/24');
                    }
                },
            ],
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        AllowedIp::create([
            'ip_address' => $request->ip_address,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => Auth::user()->name ?? 'System',
        ]);

        return Redirect::route('admin.allowed-ips.index')
            ->with('success', 'IP address added successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AllowedIp $allowedIp)
    {
        return Inertia::render('Admin/AllowedIps/Show', [
            'allowedIp' => $allowedIp
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AllowedIp $allowedIp)
    {
        return Inertia::render('Admin/AllowedIps/Edit', [
            'allowedIp' => $allowedIp
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AllowedIp $allowedIp)
    {
        $request->validate([
            'ip_address' => [
                'required',
                'string',
                'unique:allowed_ips,ip_address,' . $allowedIp->id,
                function ($attribute, $value, $fail) {
                    if (!AllowedIp::validateIpFormat($value)) {
                        $fail('The IP address format is invalid. Use format like 192.168.1.1 or 192.168.1.0/24');
                    }
                },
            ],
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $allowedIp->update([
            'ip_address' => $request->ip_address,
            'description' => $request->description,
            'is_active' => $request->boolean('is_active'),
        ]);

        return Redirect::route('admin.allowed-ips.index')
            ->with('success', 'IP address updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AllowedIp $allowedIp)
    {
        $allowedIp->delete();

        return Redirect::route('admin.allowed-ips.index')
            ->with('success', 'IP address deleted successfully.');
    }

    /**
     * Toggle IP status
     */
    public function toggle(AllowedIp $allowedIp)
    {
        $allowedIp->update([
            'is_active' => !$allowedIp->is_active
        ]);

        $status = $allowedIp->is_active ? 'activated' : 'deactivated';
        
        return Redirect::back()
            ->with('success', "IP address {$status} successfully.");
    }

    /**
     * Add current IP
     */
    public function addCurrentIp(Request $request)
    {
        $currentIp = $request->ip();
        
        $existingIp = AllowedIp::where('ip_address', $currentIp)->first();
        
        if ($existingIp) {
            return Redirect::back()
                ->with('info', 'Your current IP is already in the allowed list.');
        }

        AllowedIp::create([
            'ip_address' => $currentIp,
            'description' => 'Added automatically - Current IP',
            'is_active' => true,
            'created_by' => Auth::user()->name ?? 'System',
        ]);

        return Redirect::back()
            ->with('success', "Your current IP ({$currentIp}) has been added to the allowed list.");
    }

    /**
     * Bulk delete inactive IPs
     */
    public function bulkDeleteInactive()
    {
        $deleted = AllowedIp::where('is_active', false)->delete();
        
        return Redirect::back()
            ->with('success', "Deleted {$deleted} inactive IP addresses.");
    }
}
