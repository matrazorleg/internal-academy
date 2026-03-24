<?php

namespace Database\Seeders;

use App\Enums\RegistrationStatus;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Academy Admin',
            'email' => 'admin@academy.local',
            'password' => Hash::make('password'),
        ]);

        $employees = User::factory(8)->create();

        Workshop::factory(6)->create();

        $tomorrowWorkshop = Workshop::factory()->create([
            'title' => 'Laravel Architecture Deep Dive',
            'description' => 'Controllers, services, policies, and clean boundaries in real projects.',
            'starts_at' => Carbon::tomorrow()->setTime(10, 0),
            'ends_at' => Carbon::tomorrow()->setTime(12, 0),
            'capacity' => 4,
        ]);

        foreach ($employees->take(3) as $employee) {
            $tomorrowWorkshop->registrations()->create([
                'user_id' => $employee->id,
                'status' => RegistrationStatus::Confirmed->value,
            ]);
        }

        $tomorrowWorkshop->registrations()->create([
            'user_id' => $employees->get(3)->id,
            'status' => RegistrationStatus::Waiting->value,
        ]);
    }
}
