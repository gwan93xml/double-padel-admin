<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Audit;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditController extends Controller
{
    /**
     * Display a listing of audit logs.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'auditable_type' => 'required|string',
            'auditable_id' => 'nullable|integer',
            'key' => 'nullable|string|max:255',
            'value' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ]);

        $auditableType = $request->input('auditable_type');
        $auditableId = $request->input('auditable_id');
        $key = $request->input('key');
        $value = $request->input('value');
        $perPage = $request->input('per_page', 50);
        $page = $request->input('page', 1);

        $query = Audit::where('auditable_type', $auditableType)
            ->with('user:id,name,email');

        // Jika ada key dan value, gunakan query detail (cari dalam JSON fields)
        if ($key && $value) {
            $query->where(function ($q) use ($key, $value) {
                $q->where('new_values', 'LIKE', '%"' . $key . '":"' . $value . '"%')
                  ->orWhere('new_values', 'LIKE', '%"' . $key . '":' . $value . '%')
                  ->orWhere('old_values', 'LIKE', '%"' . $key . '":"' . $value . '"%')
                  ->orWhere('old_values', 'LIKE', '%"' . $key . '":' . $value . '%');
            });
        }
        // Jika ada auditable_id, gunakan query normal
        elseif ($auditableId) {
            $query->where('auditable_id', $auditableId);
        }
        // Jika tidak ada parameter spesifik, return error
        else {
            return response()->json([
                'error' => 'Either auditable_id or both key and value parameters are required'
            ], 400);
        }

        $audits = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Transform the data to match the frontend expectations
        $transformedAudits = $audits->getCollection()->map(function ($audit) {
            return [
                'id' => $audit->id,
                'user_type' => $audit->user_type,
                'user_id' => $audit->user_id,
                'event' => $audit->event,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'old_values' => $audit->old_values ?? [],
                'new_values' => $audit->new_values ?? [],
                'url' => $audit->url,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'tags' => $audit->tags,
                'delete_reason' => $audit->delete_reason,
                'created_at' => $audit->created_at,
                'updated_at' => $audit->updated_at,
                'user' => $audit->user ? [
                    'id' => $audit->user->id,
                    'name' => $audit->user->name,
                    'email' => $audit->user->email,
                ] : null,
            ];
        });

        return response()->json([
            'data' => $transformedAudits,
            'current_page' => $audits->currentPage(),
            'last_page' => $audits->lastPage(),
            'per_page' => $audits->perPage(),
            'total' => $audits->total(),
            'from' => $audits->firstItem(),
            'to' => $audits->lastItem(),
        ]);
    }

    /**
     * Get audit statistics for a specific model.
     */
    public function statistics(Request $request): JsonResponse
    {
        $request->validate([
            'auditable_type' => 'required|string',
        ]);

        $auditableType = $request->input('auditable_type');

        $stats = [
            'total_audits' => Audit::where('auditable_type', $auditableType)->count(),
            'created_count' => Audit::where('auditable_type', $auditableType)->where('event', 'created')->count(),
            'updated_count' => Audit::where('auditable_type', $auditableType)->where('event', 'updated')->count(),
            'deleted_count' => Audit::where('auditable_type', $auditableType)->where('event', 'deleted')->count(),
            'restored_count' => Audit::where('auditable_type', $auditableType)->where('event', 'restored')->count(),
            'unique_users' => Audit::where('auditable_type', $auditableType)->distinct('user_id')->count(),
            'recent_audits' => Audit::where('auditable_type', $auditableType)
                ->with('user:id,name,email')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($audit) {
                    return [
                        'id' => $audit->id,
                        'event' => $audit->event,
                        'user' => $audit->user ? $audit->user->name : 'System',
                        'created_at' => $audit->created_at,
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Get audit logs for multiple records of a model type.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'auditable_type' => 'required|string',
            'auditable_ids' => 'required|array',
            'auditable_ids.*' => 'integer',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $auditableType = $request->input('auditable_type');
        $auditableIds = $request->input('auditable_ids');
        $perPage = $request->input('per_page', 50);

        $audits = Audit::where('auditable_type', $auditableType)
            ->whereIn('auditable_id', $auditableIds)
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $transformedAudits = $audits->getCollection()->map(function ($audit) {
            return [
                'id' => $audit->id,
                'user_type' => $audit->user_type,
                'user_id' => $audit->user_id,
                'event' => $audit->event,
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'old_values' => $audit->old_values ?? [],
                'new_values' => $audit->new_values ?? [],
                'url' => $audit->url,
                'ip_address' => $audit->ip_address,
                'user_agent' => $audit->user_agent,
                'tags' => $audit->tags,
                'delete_reason' => $audit->delete_reason,
                'created_at' => $audit->created_at,
                'updated_at' => $audit->updated_at,
                'user' => $audit->user ? [
                    'id' => $audit->user->id,
                    'name' => $audit->user->name,
                    'email' => $audit->user->email,
                ] : null,
            ];
        });

        return response()->json([
            'data' => $transformedAudits,
            'current_page' => $audits->currentPage(),
            'last_page' => $audits->lastPage(),
            'per_page' => $audits->perPage(),
            'total' => $audits->total(),
        ]);
    }

    public function detail(Request $request): JsonResource
    {
        $request->validate([
            'auditable_type' => 'required|string|max:255',
            'key' => 'required|string|max:255',
            'value' => 'required|string|max:255',
        ]);

        $auditableType = $request->input('auditable_type'); // example 'App\Models\YourModel'
        $key = $request->input('key'); // example 'sale_id'
        $value = $request->input('value'); // example '123'

        $audits = Audit::where('auditable_type', $auditableType)
            ->where(function ($q) use ($key, $value) {
                $q->where('new_values', 'LIKE', '%"' . $key . '":"' . $value . '"%')
                  ->orWhere('new_values', 'LIKE', '%"' . $key . '":' . $value . '%')
                  ->orWhere('old_values', 'LIKE', '%"' . $key . '":"' . $value . '"%')
                  ->orWhere('old_values', 'LIKE', '%"' . $key . '":' . $value . '%');
            })
            ->with('user:id,name,email')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($audit) {
                return [
                    'id' => $audit->id,
                    'user_type' => $audit->user_type,
                    'user_id' => $audit->user_id,
                    'event' => $audit->event,
                    'auditable_type' => $audit->auditable_type,
                    'auditable_id' => $audit->auditable_id,
                    'old_values' => $audit->old_values ?? [],
                    'new_values' => $audit->new_values ?? [],
                    'url' => $audit->url,
                    'ip_address' => $audit->ip_address,
                    'user_agent' => $audit->user_agent,
                    'tags' => $audit->tags,
                    'delete_reason' => $audit->delete_reason,
                    'created_at' => $audit->created_at,
                    'updated_at' => $audit->updated_at,
                    'user' => $audit->user ? [
                        'id' => $audit->user->id,
                        'name' => $audit->user->name,
                        'email' => $audit->user->email,
                    ] : null,
                ];
            });
        return new JsonResource($audits);
    }
}
