<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WorkshopRegistrationService
{
    /**
     * Register a user to a workshop as confirmed or waiting list.
     */
    public function register(User $user, Workshop $workshop): RegistrationStatus
    {
        return DB::transaction(function () use ($user, $workshop): RegistrationStatus {
            $lockedWorkshop = Workshop::query()->lockForUpdate()->findOrFail($workshop->id);

            if ($lockedWorkshop->starts_at->isPast()) {
                throw ValidationException::withMessages([
                    'workshop' => 'Cannot register to a workshop that has already started.',
                ]);
            }

            $alreadyRegistered = Registration::query()
                ->where('user_id', $user->id)
                ->where('workshop_id', $lockedWorkshop->id)
                ->exists();

            if ($alreadyRegistered) {
                throw ValidationException::withMessages([
                    'workshop' => 'You are already registered for this workshop.',
                ]);
            }

            $hasOverlappingWorkshop = Registration::query()
                ->where('user_id', $user->id)
                ->whereHas('workshop', function ($query) use ($lockedWorkshop): void {
                    $query
                        ->where('starts_at', '<', $lockedWorkshop->ends_at)
                        ->where('ends_at', '>', $lockedWorkshop->starts_at);
                })
                ->exists();

            if ($hasOverlappingWorkshop) {
                throw ValidationException::withMessages([
                    'workshop' => 'You already have a workshop at an overlapping time.',
                ]);
            }

            $confirmedCount = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->confirmed()
                ->count();

            $status = $confirmedCount < $lockedWorkshop->capacity
                ? RegistrationStatus::Confirmed
                : RegistrationStatus::Waiting;

            Registration::create([
                'user_id' => $user->id,
                'workshop_id' => $lockedWorkshop->id,
                'status' => $status->value,
            ]);

            return $status;
        });
    }

    /**
     * Cancel a user registration. If a confirmed seat is freed, promote first waiting user.
     */
    public function cancel(User $user, Workshop $workshop): void
    {
        DB::transaction(function () use ($user, $workshop): void {
            $lockedWorkshop = Workshop::query()->lockForUpdate()->findOrFail($workshop->id);

            $registration = Registration::query()
                ->where('user_id', $user->id)
                ->where('workshop_id', $lockedWorkshop->id)
                ->lockForUpdate()
                ->first();

            if (! $registration) {
                throw ValidationException::withMessages([
                    'workshop' => 'No registration found for this workshop.',
                ]);
            }

            $wasConfirmed = $registration->status === RegistrationStatus::Confirmed;
            $registration->delete();

            if (! $wasConfirmed) {
                return;
            }

            $nextInLine = Registration::query()
                ->where('workshop_id', $lockedWorkshop->id)
                ->waiting()
                ->orderBy('created_at')
                ->lockForUpdate()
                ->first();

            if ($nextInLine) {
                $nextInLine->update([
                    'status' => RegistrationStatus::Confirmed->value,
                ]);
            }
        });
    }
}
