<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'setting' => Setting::first(),
            'auth' => [
                'user' => $request->user() ? $request->user()->load(['roles.permissions']) : $request->user(),
            ],
            'errors' => function () use ($request) {
                return $request->session()->get('errors')
                    ? $request->session()->get('errors')->getBag('default')->toArray()
                    : (object) [];
            },
            'flash' => [
                'error' => function () use ($request) {
                    info($request->session()->get('error'));
                    return $request->session()->get('error');
                }
            ],
        ];
    }
}
