<?php

namespace App\Services\Settings;

use App\Models\Owner;
use App\Models\Room;
use App\Models\SystemSettings;
use App\DTO\MailSettingsDTO;
use App\DTO\CaldavSettingsDTO;
use App\DTO\WebdavSettingsDTO;

class SettingsService
{
    protected ?systemSettings $systemSettings;
    public function __construct() {
        $this->systemSettings = app(SystemSettings::class);
    }
    protected function value(string $key, ?Room $room = null, ?Owner $org = null): mixed
    {
        return $room?->{$key}
            ?? $org?->{$key}
            ?? $this->systemSettings->{$key};
    }

    public function mail(?Owner $org=null): MailSettingsDTO
    {
        return new MailSettingsDTO(
            host: $this->value('mail_host', org: $org),
            port: $this->value('mail_port', org: $org),
            user: $this->value('mail', org: $org),
            pass: $this->value('mail_pass', org: $org),
        );
    }

    public function hasDefaultMailSettings(): bool
    {
        return $this->systemSettings->mail_host && $this->systemSettings->mail_port &&
                $this->systemSettings->mail && $this->systemSettings->mail_pass;
    }

    public function caldav(?Owner $org=null): CaldavSettingsDTO
    {
        return new CaldavSettingsDTO(
            url: $this->value('dav_url', org: $org),
            user: $this->value('dav_user', org: $org),
            pass: $this->value('dav_pass', org: $org),
        );
    }

    public function hasDefaultCaldavSettings(): bool
    {
        return $this->systemSettings->dav_url && $this->systemSettings->dav_user && $this->systemSettings->dav_pass;
    }

    public function webdav(?Owner $org=null): WebdavSettingsDTO
    {
        return new WebdavSettingsDTO(
            user: $this->value('webdav_user', org: $org),
            pass: $this->value('webdav_pass', org: $org),
            webdavUrl: $this->value('webdav_endpoint', org: $org),
            savePath: $this->value('webdav_save_path', org: $org),
        );
    }

    public function hasDefaultWebdavSettings(): bool
    {
        return $this->systemSettings->webdav_user && $this->systemSettings->webdav_pass
            && $this->systemSettings->webdav_endpoint && $this->systemSettings->webdav_save_path;
    }

    public function timezone(?Room $room=null, ?Owner $org=null): String
    {
        return $this->value('timezone', $room, $room?->owner ?? $org);
    }
    public function currency(?Owner $org=null): String
    {
        return $this->value('currency', org: $org);
    }
    public function locale(?Owner $org=null): String
    {
        return $this->value('locale', org: $org);
    }

    /**
     * Configure le mailer Laravel avec les paramètres SMTP système
     */
    public function configureMailer(?Owner $org = null): void
    {
        $mailSettings = $this->mail($org);

        // Ne configure que si tous les paramètres sont présents
        if (!$mailSettings->host || !$mailSettings->port || !$mailSettings->user || !$mailSettings->pass) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp' => [
                'transport' => 'smtp',
                'host' => $mailSettings->host,
                'port' => $mailSettings->port,
                'encryption' => $mailSettings->port == 465 ? 'ssl' : 'tls',
                'username' => $mailSettings->user,
                'password' => $mailSettings->pass,
                'timeout' => null,
            ],
            'mail.from' => [
                'address' => $mailSettings->user,
                'name' => config('app.name'),
            ],
        ]);

        // Force Laravel à recharger le mailer avec la nouvelle config
        app('mail.manager')->forgetMailers();
    }
}
