<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class WorkshopCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_a_workshop(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin);

        $this->post(route('admin.workshops.store'), [
            'title' => 'Laravel Clean Architecture',
            'description' => 'Bounded contexts and clean controllers.',
            'starts_at' => Carbon::now()->addDays(2)->setTime(10, 0)->toDateTimeString(),
            'ends_at' => Carbon::now()->addDays(2)->setTime(12, 0)->toDateTimeString(),
            'capacity' => 20,
        ])->assertRedirect(route('admin.workshops.index'));

        $workshop = Workshop::query()->firstOrFail();

        $this->put(route('admin.workshops.update', $workshop), [
            'title' => 'Laravel Advanced Architecture',
            'description' => 'Bounded contexts and clean controllers.',
            'starts_at' => Carbon::now()->addDays(3)->setTime(10, 0)->toDateTimeString(),
            'ends_at' => Carbon::now()->addDays(3)->setTime(12, 0)->toDateTimeString(),
            'capacity' => 25,
        ])->assertRedirect(route('admin.workshops.index'));

        $this->assertDatabaseHas('workshops', [
            'id' => $workshop->id,
            'title' => 'Laravel Advanced Architecture',
            'capacity' => 25,
        ]);

        $this->delete(route('admin.workshops.destroy', $workshop))
            ->assertRedirect(route('admin.workshops.index'));

        $this->assertDatabaseMissing('workshops', ['id' => $workshop->id]);
    }
}
