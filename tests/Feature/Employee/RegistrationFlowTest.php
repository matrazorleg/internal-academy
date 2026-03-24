<?php

namespace Tests\Feature\Employee;

use App\Enums\RegistrationStatus;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class RegistrationFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_is_waitlisted_if_workshop_is_full_and_promoted_after_cancellation(): void
    {
        $confirmedUser = User::factory()->create();
        $waitingUser = User::factory()->create();
        $workshop = Workshop::factory()->create(['capacity' => 1]);

        Registration::query()->create([
            'user_id' => $confirmedUser->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed->value,
        ]);

        $this->actingAs($waitingUser)
            ->post(route('workshops.registrations.store', $workshop))
            ->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'user_id' => $waitingUser->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Waiting->value,
        ]);

        $this->actingAs($confirmedUser)
            ->delete(route('workshops.registrations.destroy', $workshop))
            ->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'user_id' => $waitingUser->id,
            'workshop_id' => $workshop->id,
            'status' => RegistrationStatus::Confirmed->value,
        ]);
    }

    public function test_employee_cannot_register_to_overlapping_workshops(): void
    {
        $employee = User::factory()->create();

        $workshopA = Workshop::factory()->create([
            'starts_at' => Carbon::now()->addDays(5)->setTime(10, 0),
            'ends_at' => Carbon::now()->addDays(5)->setTime(12, 0),
            'capacity' => 10,
        ]);

        $workshopB = Workshop::factory()->create([
            'starts_at' => Carbon::now()->addDays(5)->setTime(11, 0),
            'ends_at' => Carbon::now()->addDays(5)->setTime(13, 0),
            'capacity' => 10,
        ]);

        $this->actingAs($employee)
            ->post(route('workshops.registrations.store', $workshopA))
            ->assertRedirect();

        $this->assertDatabaseHas('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $workshopA->id,
        ]);

        $this->actingAs($employee)->post(route('workshops.registrations.store', $workshopB))
            ->assertRedirect();

        $this->assertDatabaseMissing('registrations', [
            'user_id' => $employee->id,
            'workshop_id' => $workshopB->id,
        ]);
    }
}
