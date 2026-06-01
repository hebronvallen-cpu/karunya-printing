<?php

namespace App\Support;

use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OtpHelper
{
    /**
     * Generate 6-digit OTP
     */
    public static function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    /**
     * Send OTP via email (using SMTP)
     */
    public static function sendOtpViaEmail(Admin $admin, string $otp): bool
    {
        $email = $admin->email;
        
        if (empty($email)) {
            return false;
        }

        try {
            // Simple email sending using PHP mail()
            $subject = 'Kode Verifikasi - Karunya Printing Admin';
            $message = "Halo {$admin->full_name},\n\n";
            $message .= "Kode verifikasi Anda adalah: ** {$otp} **\n\n";
            $message .= "Kode ini berlaku selama 15 menit.\n";
            $message .= "Jika Anda tidak meminta ini, abaikan email ini.\n\n";
            $message .= "Salam,\nKarunya Printing";

            $headers = "From: noreply@karunyaprinting.com\r\n";
            $headers .= "Reply-To: noreply@karunyaprinting.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

            return mail($email, $subject, $message, $headers);
        } catch (\Exception $e) {
            \Log::error('OTP Email error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create OTP record in database
     */
    public static function createOtpRecord(Admin $admin, string $otp, int $expiresMinutes = 15): bool
    {
        try {
            // Clear old OTPs first
            self::clearOtps($admin->id);

            $admin->otp_code = $otp;
            $admin->otp_expires_at = now()->addMinutes($expiresMinutes);
            $admin->save();

            return true;
        } catch (\Exception $e) {
            \Log::error('OTP create error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Verify OTP
     */
    public static function verifyOtp(int $adminId, string $otp): bool
    {
        $admin = Admin::find($adminId);
        
        if ($admin === null) {
            return false;
        }

        if (empty($admin->otp_code) || empty($admin->otp_expires_at)) {
            return false;
        }

        // Check expiry
        if (now()->gt($admin->otp_expires_at)) {
            self::clearOtps($adminId);
            return false;
        }

        // Verify OTP
        $valid = ($admin->otp_code === $otp);
        
        // Clear OTP after verification (one-time use)
        if ($valid) {
            self::clearOtps($adminId);
        }

        return $valid;
    }

    /**
     * Clear OTPs for admin
     */
    public static function clearOtps(int $adminId): void
    {
        $admin = Admin::find($adminId);
        if ($admin !== null) {
            $admin->otp_code = null;
            $admin->otp_expires_at = null;
            $admin->save();
        }
    }

    /**
     * Generate password reset token
     */
    public static function generateResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Create password reset record
     */
    public static function createPasswordReset(Admin $admin): string
    {
        $token = self::generateResetToken();
        
        // Store token hash in database (we need to add this column or use a separate table)
        // For simplicity, we'll use OTP as the reset mechanism
        $otp = self::generateOtp();
        self::createOtpRecord($admin, $otp, 60); // 60 minutes for password reset
        
        return $otp;
    }
}
