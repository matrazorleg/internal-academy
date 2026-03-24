<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreWorkshopRequest;
use App\Http\Requests\UpdateWorkshopRequest;
use App\Models\Workshop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class WorkshopController extends Controller
{
    /**
     * Display workshop list with statistics.
     */
    public function index(): Response
    {
        $workshops = Workshop::query()
            ->orderBy('starts_at')
            ->withCount([
                'confirmedRegistrations as confirmed_count',
                'waitingRegistrations as waiting_count',
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
                'edit_url' => route('admin.workshops.edit', $workshop),
                'destroy_url' => route('admin.workshops.destroy', $workshop),
            ]);

        return Inertia::render('Admin/Workshops/Index', [
            'workshops' => $workshops,
            'mostPopularWorkshop' => $this->mostPopularWorkshop(),
            'createUrl' => route('admin.workshops.create'),
            'statsUrl' => route('admin.workshops.stats'),
        ]);
    }

    /**
     * Display workshop creation form.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Workshops/Create', [
            'indexUrl' => route('admin.workshops.index'),
            'storeUrl' => route('admin.workshops.store'),
        ]);
    }

    /**
     * Persist a workshop.
     */
    public function store(StoreWorkshopRequest $request): RedirectResponse
    {
        Workshop::query()->create($request->validated());

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop created.');
    }

    /**
     * Display workshop edit form.
     */
    public function edit(Workshop $workshop): Response
    {
        return Inertia::render('Admin/Workshops/Edit', [
            'workshop' => [
                'id' => $workshop->id,
                'title' => $workshop->title,
                'description' => $workshop->description,
                'starts_at' => $workshop->starts_at->format('Y-m-d\TH:i'),
                'ends_at' => $workshop->ends_at->format('Y-m-d\TH:i'),
                'capacity' => $workshop->capacity,
                'update_url' => route('admin.workshops.update', $workshop),
            ],
            'indexUrl' => route('admin.workshops.index'),
        ]);
    }

    /**
     * Update a workshop.
     */
    public function update(UpdateWorkshopRequest $request, Workshop $workshop): RedirectResponse
    {
        $workshop->update($request->validated());

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop updated.');
    }

    /**
     * Remove a workshop.
     */
    public function destroy(Workshop $workshop): RedirectResponse
    {
        $workshop->delete();

        return redirect()
            ->route('admin.workshops.index')
            ->with('success', 'Workshop deleted.');
    }

    /**
     * Return workshop counters for frontend polling.
     */
    public function stats(): JsonResponse
    {
        $workshops = Workshop::query()
            ->orderBy('starts_at')
            ->withCount([
                'confirmedRegistrations as confirmed_count',
                'waitingRegistrations as waiting_count',
            ])
            ->get()
            ->map(fn (Workshop $workshop) => [
                'id' => $workshop->id,
                'confirmed_count' => $workshop->confirmed_count,
                'waiting_count' => $workshop->waiting_count,
            ]);

        return response()->json([
            'workshops' => $workshops,
            'mostPopularWorkshop' => $this->mostPopularWorkshop(),
        ]);
    }

    /**
     * Get the most popular workshop by confirmed registrations.
     *
     * @return array<string, int|string>|null
     */
    private function mostPopularWorkshop(): ?array
    {
        $mostPopular = Workshop::query()
            ->withCount(['confirmedRegistrations as confirmed_count'])
            ->orderByDesc('confirmed_count')
            ->orderBy('starts_at')
            ->first();

        if (! $mostPopular) {
            return null;
        }

        return [
            'id' => $mostPopular->id,
            'title' => $mostPopular->title,
            'confirmed_count' => $mostPopular->confirmed_count,
        ];
    }
}
