<?php

namespace App\Models;

use App\DTO\CaldavSettingsDTO;
use App\DTO\MailSettingsDTO;
use App\DTO\WebdavSettingsDTO;
use App\Enums\InvoiceDueModes;
use App\Enums\LateInvoicesReminderFrequency;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Owner extends Model
{
    /** @use HasFactory<\Database\Factories\OwnerFactory> */
    use HasFactory;

    protected $fillable = [
        'slug',
        'website',
        'contact_id',
        'hide_email',
        'hide_phone',
        'invoice_due_mode',
        'invoice_due_days',
        'invoice_due_days_after_reminder',
        'max_nb_reminders',
        'payment_instructions',
        'late_invoices_reminder',
        'late_invoices_reminder_sent_at',
        'mail_host',
        'mail_port',
        'mail',
        'mail_pass',
        'use_caldav',
        'dav_url',
        'dav_user',
        'dav_pass',
        'use_webdav',
        'webdav_user',
        'webdav_pass',
        'webdav_endpoint',
        'webdav_save_path',
        'timezone',
        'currency',
        'locale',
    ];

    protected $hidden = [
        'mail_pass',
        'dav_pass',
        'webdav_pass',
    ];

    protected $casts = [
        'hide_email' => 'boolean',
        'hide_phone' => 'boolean',
        'invoice_due_mode' => InvoiceDueModes::class,
        'payment_instructions' => 'array',
        'late_invoices_reminder' => LateInvoicesReminderFrequency::class,
        'late_invoices_reminder_sent_at' => 'date',
        'mail_pass' => 'encrypted',
        'dav_pass' => 'encrypted',
        'webdav_pass' => 'encrypted',
    ];

    public function getTimezone(): string
    {
        return app(SettingsService::class)->timezone(org: $this);
    }

    public function getCurrency(): string
    {
        return app(SettingsService::class)->currency(org: $this);
    }

    public function getLocale(): string
    {
        return app(SettingsService::class)->locale(org: $this);
    }

    public function mailSettings(): MailSettingsDTO
    {
        return app(SettingsService::class)->mail($this);
    }

    public function caldavSettings(): CaldavSettingsDTO
    {
        return app(SettingsService::class)->caldav($this);
    }

    public function webdavSettings(): WebdavSettingsDTO
    {
        return app(SettingsService::class)->webdav($this);
    }

    public function usesWebdav(): bool
    {
        return $this->use_webdav && $this->webdavSettings()->valid();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->orderBy('name');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
