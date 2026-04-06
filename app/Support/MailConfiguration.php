<?php

namespace App\Support;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class MailConfiguration
{
    public static function current(): ?MailSetting
    {
        if (! Schema::hasTable('mail_settings')) {
            return null;
        }

        return MailSetting::query()->latest('id')->first();
    }

    public static function isConfigured(): bool
    {
        return self::current()?->isConfigured() ?? false;
    }

    public static function apply(): void
    {
        $setting = self::current();

        if (! $setting || ! $setting->isConfigured()) {
            return;
        }

        Config::set('mail.default', $setting->mailer ?: 'smtp');
        Config::set('mail.mailers.smtp.transport', 'smtp');
        Config::set('mail.mailers.smtp.host', $setting->host);
        Config::set('mail.mailers.smtp.port', $setting->port);
        Config::set('mail.mailers.smtp.username', $setting->username);
        Config::set('mail.mailers.smtp.password', $setting->password);
        Config::set('mail.mailers.smtp.encryption', $setting->encryption ?: null);
        Config::set('mail.from.address', $setting->from_address);
        Config::set('mail.from.name', $setting->from_name ?: config('app.name'));
    }
}
