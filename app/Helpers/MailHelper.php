<?php

namespace App\Helpers;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    public static function configureMail()
    {
        $smtp = SmtpSetting::where('is_active', true)->first();

        if ($smtp) {
            Config::set('mail.mailers.smtp', [
                'transport' => $smtp->mailer,
                'host' => $smtp->host,
                'port' => $smtp->port,
                'encryption' => $smtp->encryption,
                'username' => $smtp->username,
                'password' => $smtp->password,
                'timeout'    => null,
                'auth_mode'  => null,
            ]);

            Config::set('mail.from', [
                'address' => $smtp->from_address,
                'name' => $smtp->from_name,
            ]);

            // 🔑 Recreate the mailer so Laravel stops using the cached one
            app()->forgetInstance('mailer');
            app()->forgetInstance('swift.mailer'); // if still using SwiftMailer
            app()->forgetInstance('mail.manager');
            Mail::clearResolvedInstances();
        }
    }
}
