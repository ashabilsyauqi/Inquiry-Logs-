<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $table = 'smtp_settings';

    protected $fillable = [
        'mail_mailer',
        'mail_host',
        'mail_port',
        'mail_username',
        'mail_password',
        'mail_encryption',
        'mail_from_address',
        'mail_from_name',
        'disconnect_alert_emails',
    ];

    /**
     * Apply saved SMTP settings to dynamic Laravel Mailer configuration
     */
    public static function applyConfig(): ?self
    {
        $setting = self::first();
        if (!$setting || !$setting->mail_host) {
            return $setting;
        }

        Config::set('mail.default', $setting->mail_mailer ?: 'smtp');
        Config::set('mail.mailers.smtp.host', $setting->mail_host);
        Config::set('mail.mailers.smtp.port', (int)($setting->mail_port ?: 587));
        Config::set('mail.mailers.smtp.username', $setting->mail_username);
        Config::set('mail.mailers.smtp.password', $setting->mail_password);
        Config::set('mail.mailers.smtp.encryption', $setting->mail_encryption ?: null);
        Config::set('mail.from.address', $setting->mail_from_address ?: 'no-reply@difitech.id');
        Config::set('mail.from.name', $setting->mail_from_name ?: 'Difitech CRM Alert');

        Mail::purge('smtp');

        return $setting;
    }
}
