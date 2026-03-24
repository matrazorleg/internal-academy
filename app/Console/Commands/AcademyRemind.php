<?php

namespace App\Console\Commands;

use App\Mail\WorkshopReminderMail;
use App\Models\Workshop;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class AcademyRemind extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'academy:remind';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails to participants of tomorrow workshops';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $start = now()->addDay()->startOfDay();
        $end = now()->addDay()->endOfDay();

        $workshops = Workshop::query()
            ->whereBetween('starts_at', [$start, $end])
            ->with([
                'confirmedRegistrations.user:id,email',
            ])
            ->get();

        $sent = 0;

        foreach ($workshops as $workshop) {
            foreach ($workshop->confirmedRegistrations as $registration) {
                if (! $registration->user?->email) {
                    continue;
                }

                Mail::to($registration->user->email)->send(new WorkshopReminderMail($workshop));
                $sent++;
            }
        }

        $this->info("Reminder emails sent: {$sent}");

        return self::SUCCESS;
    }
}
