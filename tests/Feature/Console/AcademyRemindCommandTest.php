<?php

namespace Tests\Feature\Console;

use App\Enums\RegistrationStatus;
use App\Mail\WorkshopReminderMail;
use App\Models\Registration;
use App\Models\User;
use App\Models\Workshop;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AcademyRemindCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_remind_command_sends_emails_only_to_confirmed_participants_of_tomorrow_workshops(): void
    {
        Mail::fake();

        $confirmed = User::factory()->create(['email' => 'confirmed@example.com']);
        $waiting = User::factory()->create(['email' => 'waiting@example.com']);
        $otherDay = User::factory()->create(['email' => 'otherday@example.com']);

        $tomorrowWorkshop = Workshop::factory()->create([
            'starts_at' => Carbon::tomorrow()->setTime(9, 0),
            'ends_at' => Carbon::tomorrow()->setTime(11, 0),
        ]);

        $differentDayWorkshop = Workshop::factory()->create([
            'starts_at' => Carbon::now()->addDays(5)->setTime(9, 0),
            'ends_at' => Carbon::now()->addDays(5)->setTime(11, 0),
        ]);

        Registration::query()->create([
            'user_id' => $confirmed->id,
            'workshop_id' => $tomorrowWorkshop->id,
            'status' => RegistrationStatus::Confirmed->value,
        ]);

        Registration::query()->create([
            'user_id' => $waiting->id,
            'workshop_id' => $tomorrowWorkshop->id,
            'status' => RegistrationStatus::Waiting->value,
        ]);

        Registration::query()->create([
            'user_id' => $otherDay->id,
            'workshop_id' => $differentDayWorkshop->id,
            'status' => RegistrationStatus::Confirmed->value,
        ]);

        $this->artisan('academy:remind')
            ->expectsOutput('Reminder emails sent: 1')
            ->assertSuccessful();

        Mail::assertSent(WorkshopReminderMail::class, function (WorkshopReminderMail $mail) use ($confirmed): bool {
            return $mail->hasTo($confirmed->email);
        });

        Mail::assertNotSent(WorkshopReminderMail::class, function (WorkshopReminderMail $mail) use ($waiting): bool {
            return $mail->hasTo($waiting->email);
        });

        Mail::assertNotSent(WorkshopReminderMail::class, function (WorkshopReminderMail $mail) use ($otherDay): bool {
            return $mail->hasTo($otherDay->email);
        });
    }
}
