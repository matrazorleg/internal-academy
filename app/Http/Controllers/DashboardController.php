<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Workshop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the authenticated user's dashboard.
     */
    public function __invoke(Request $request): Response|RedirectResponse
    {
        if ($request->user()->role === UserRole::Admin) {
            return redirect()->route('admin.workshops.index');
        }

        $workshops = Workshop::query()
            ->upcoming()
            ->orderBy('starts_at')
            ->withCount([
                'confirmedRegistrations as confirmed_count',
                'waitingRegistrations as waiting_count',
            ])
            ->with([
                'registrations' => fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->select('id', 'workshop_id', 'status'),
            ])
            ->get()
            ->map(fn (Workshop $workshop) => [
                'id' => $workshop->id,
                'title' => $workshop->title,
                'description' => $workshop->description,
                'starts_at' => $workshop->starts_at->toIso8601String(),
                'ends_at' => $workshop->ends_at->toIso8601String(),
                'capacity' => $workshop->capacity,
                'confirmed_count' => $workshop->confirmed_count,
                'waiting_count' => $workshop->waiting_count,
                'available_seats' => max(0, $workshop->capacity - $workshop->confirmed_count),
                'user_registration_status' => $workshop->registrations->first()?->status?->value
                    ?? $workshop->registrations->first()?->status,
                'register_url' => route('workshops.registrations.store', $workshop),
                'cancel_url' => route('workshops.registrations.destroy', $workshop),
            ]);

        return Inertia::render('Employee/Dashboard', [
            'workshops' => $workshops,
        ]);
    }
}
