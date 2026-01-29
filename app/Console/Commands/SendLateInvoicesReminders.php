<?php

namespace App\Console\Commands;

use App\Enums\LateInvoicesReminderFrequency;
use App\Models\Invoice;
use App\Models\Owner;
use App\Services\Mailer\MailService;
use Illuminate\Console\Command;

class SendLateInvoicesReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:send-late-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send late invoices reminder emails to owners based on their configured frequency';

    /**
     * Execute the console command.
     */
    public function handle(MailService $mail): int
    {
        $owners = Owner::where('late_invoices_reminder', '!=', LateInvoicesReminderFrequency::NEVER->value)
            ->get();

        $sentCount = 0;

        foreach ($owners as $owner) {
            // Check if reminder should be sent based on frequency
            if (! $owner->late_invoices_reminder->shouldSendReminder($owner->late_invoices_reminder_sent_at)) {
                continue;
            }

            // Count late invoices for this owner
            $lateCount = Invoice::where('owner_id', $owner->id)
                ->late()
                ->count();

            if ($lateCount === 0) {
                continue;
            }

            // Send reminder
            try {
                $mail->sendLateInvoicesReminder($owner, $lateCount);

                // Update last sent date
                $owner->update([
                    'late_invoices_reminder_sent_at' => now(),
                ]);

                $sentCount++;
                $this->info("Reminder sent to {$owner->contact->display_name()} ({$lateCount} late invoices)");
            } catch (\Exception $e) {
                $this->error("Failed to send reminder to {$owner->contact->display_name()}: {$e->getMessage()}");
            }
        }

        $this->info("Total reminders sent: {$sentCount}");

        return Command::SUCCESS;
    }
}
