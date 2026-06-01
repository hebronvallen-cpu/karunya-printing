<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
    /**
     * Send OTP email using Laravel Mail facade
     */
    public static function sendOtpEmail(Admin $admin, string $otp, string $customSubject = null, string $targetEmail = null): bool
    {
        $email = $targetEmail ?? $admin->email;
        
        if (empty($email)) {
            return false;
        }
        
        $subject = $customSubject ?? 'Kode Verifikasi Password Reset - Karunya Printing';
        
        $body = "Halo {$admin->full_name},\n\n";
        $body .= "Anda meminta reset password. Berikut kode verifikasi Anda:\n\n";
        $body .= "KODE: {$otp}\n\n";
        $body .= "Kode ini berlaku selama 15 menit.\n";
        $body .= "Jika Anda tidak meminta ini, abaikan email ini.\n\n";
        $body .= "Salam,\nKarunya Printing";
        
        try {
            // Use Laravel's Mail facade - respects config/mail.php settings
            Mail::raw($body, function ($m) use ($email, $subject) {
                $m->to($email)
                    ->subject($subject)
                    ->from(config('mail.from.address', 'noreply@karunyaprinting.com'), config('mail.from.name', 'Karunya Printing'));
            });
            
            // Log OTP for development/testing purposes
            Log::info('OTP Email sent - To: ' . $email . ' | Subject: ' . $subject . ' | OTP: ' . $otp);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send OTP email: ' . $e->getMessage());
            return false;
        }
    }
}
