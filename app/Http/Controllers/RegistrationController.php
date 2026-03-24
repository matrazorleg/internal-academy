<?php

namespace App\Http\Controllers;

use App\Models\Workshop;
use App\Services\WorkshopRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly WorkshopRegistrationService $registrationService
    ) {
    }

    /**
     * Register authenticated employee to a workshop.
     */
    public function store(Request $request, Workshop $workshop): RedirectResponse
    {
        try {
            $status = $this->registrationService->register($request->user(), $workshop);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        $message = $status->value === 'confirmed'
            ? 'Registration confirmed.'
            : 'Workshop full: you were added to the waiting list.';

        return back()->with('success', $message);
    }

    /**
     * Cancel authenticated employee registration from a workshop.
     */
    public function destroy(Request $request, Workshop $workshop): RedirectResponse
    {
        try {
            $this->registrationService->cancel($request->user(), $workshop);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Registration cancelled.');
    }
}
