<?php

namespace App\Console\Commands;

use App\Enums\ContactTypes;
use App\Models\Contact;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeIdenticalContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contacts:merge-identical
                            {--force : Exécuter les fusions (sinon dry-run)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect and merge duplicate contacts sharing the same type, email and name';

    /**
     * Fields to fill from duplicates when empty on the kept contact.
     *
     * @var list<string>
     */
    private const FILLABLE_FIELDS = [
        'invoice_email',
        'phone',
        'street',
        'zip',
        'city',
        'entity_name',
        'first_name',
        'last_name',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = ! $this->option('force');

        if ($isDryRun) {
            $this->info('Mode dry-run : aucune modification ne sera effectuée.');
            $this->newLine();
        }

        $groups = $this->detectDuplicateGroups();

        if ($groups->isEmpty()) {
            $this->info('Aucun doublon détecté.');

            return Command::SUCCESS;
        }

        $this->info($groups->count().' groupe(s) de doublons détecté(s).');
        $this->newLine();

        $mergedCount = 0;
        $skippedCount = 0;

        foreach ($groups as $key => $contacts) {
            $this->displayGroup($key, $contacts);

            if ($isDryRun) {
                continue;
            }

            $result = $this->mergeGroup($contacts);

            if ($result) {
                $mergedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->newLine();

        if ($isDryRun) {
            $this->info("Dry-run terminé. {$groups->count()} groupe(s) de doublons à fusionner.");
        } else {
            $this->info("Terminé. $mergedCount groupe(s) fusionné(s), $skippedCount ignoré(s).");
        }

        return Command::SUCCESS;
    }

    /**
     * Detect groups of duplicate contacts.
     *
     * @return \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, Contact>>
     */
    private function detectDuplicateGroups(): \Illuminate\Support\Collection
    {
        return Contact::query()
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->withCount('reservations')
            ->get()
            ->groupBy(fn (Contact $contact) => $this->buildGroupKey($contact))
            ->filter(fn ($group) => $group->count() > 1);
    }

    /**
     * Build the grouping key for a contact.
     */
    private function buildGroupKey(Contact $contact): string
    {
        $type = $contact->type->value;
        $email = $this->normalize($contact->email);

        $name = $contact->type === ContactTypes::ORGANIZATION
            ? $this->normalize($contact->entity_name ?? '')
            : $this->normalize(($contact->first_name ?? '').' '.($contact->last_name ?? ''));

        return "$type|$email|$name";
    }

    /**
     * Normalize a string for comparison: trim, collapse whitespace, lowercase.
     */
    private function normalize(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/\s+/u', ' ', $value);

        return mb_strtolower($value);
    }

    /**
     * Display a group of duplicate contacts.
     *
     * @param  \Illuminate\Support\Collection<int, Contact>  $contacts
     */
    private function displayGroup(string $key, \Illuminate\Support\Collection $contacts): void
    {
        $this->line("Groupe : <comment>$key</comment>");

        $this->table(
            ['ID', 'Nom', 'Email', 'Réservations', 'Owner contact'],
            $contacts->map(fn (Contact $c) => [
                $c->id,
                $c->display_name(),
                $c->email,
                $c->reservations_count,
                $c->owners->isNotEmpty() ? 'Oui' : 'Non',
            ])
        );
    }

    /**
     * Merge a group of duplicate contacts. Returns true on success, false if skipped.
     *
     * @param  \Illuminate\Support\Collection<int, Contact>  $contacts
     */
    private function mergeGroup(\Illuminate\Support\Collection $contacts): bool
    {
        // Load owners relationship for owner-contact detection
        $contacts->load('owners');

        // Identify contacts referenced by owners
        $ownerContacts = $contacts->filter(fn (Contact $c) => $c->owners->isNotEmpty());

        if ($ownerContacts->count() > 1) {
            $ids = $ownerContacts->pluck('id')->implode(', ');
            $this->warn("  ⚠ Groupe ignoré : plusieurs contacts sont référencés par des propriétaires (IDs: $ids).");
            $this->newLine();

            return false;
        }

        // Determine the contact to keep
        $kept = $this->determineKeptContact($contacts, $ownerContacts->first());

        $duplicates = $contacts->reject(fn (Contact $c) => $c->id === $kept->id);

        DB::transaction(function () use ($kept, $duplicates) {
            foreach ($duplicates as $duplicate) {
                $this->mergeDuplicateIntoKept($kept, $duplicate);
            }
        });

        $duplicateIds = $duplicates->pluck('id')->implode(', ');
        $this->info("  ✓ Contact #{$kept->id} conservé, doublons supprimés : $duplicateIds");
        $this->newLine();

        return true;
    }

    /**
     * Determine which contact to keep in the group.
     */
    private function determineKeptContact(\Illuminate\Support\Collection $contacts, ?Contact $ownerContact): Contact
    {
        // An owner contact always takes priority
        if ($ownerContact) {
            return $ownerContact;
        }

        // Otherwise: most reservations, then smallest ID
        return $contacts
            ->sortBy([
                ['reservations_count', 'desc'],
                ['id', 'asc'],
            ])
            ->first();
    }

    /**
     * Merge a single duplicate contact into the kept contact, then delete it.
     */
    private function mergeDuplicateIntoKept(Contact $kept, Contact $duplicate): void
    {
        // Fill empty fields from duplicate
        $updated = false;
        foreach (self::FILLABLE_FIELDS as $field) {
            if (empty($kept->{$field}) && ! empty($duplicate->{$field})) {
                $kept->{$field} = $duplicate->{$field};
                $updated = true;
            }
        }

        if ($updated) {
            $kept->save();
        }

        // Reassign reservations
        $reassigned = $duplicate->reservations()->update(['tenant_id' => $kept->id]);

        if ($reassigned > 0) {
            $this->line("    Réservations réaffectées depuis #{$duplicate->id} : $reassigned");
        }

        // Transfer user associations
        $userIds = $duplicate->users()->pluck('users.id')->all();

        if (! empty($userIds)) {
            $kept->users()->syncWithoutDetaching($userIds);
            $this->line("    Utilisateurs transférés depuis #{$duplicate->id} : ".count($userIds));
        }

        // Detach users from duplicate before deletion (pivot has cascade, but be explicit)
        $duplicate->users()->detach();

        // Delete the duplicate
        $duplicate->delete();
    }
}
