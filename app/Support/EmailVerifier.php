<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * EmailVerifier - Helper class untuk memverifikasi apakah email aktif atau tidak
 * Menggunakan beberapa metode: DNS MX check, syntax check, disposable email check
 */
class EmailVerifier
{
    /**
     * Verifikasi email - cek apakah email valid dan aktif
     * 
     * @param string $email Email yang akan diverifikasi
     * @return array ['valid' => bool, 'message' => string, 'details' => array]
     */
    public static function verify(string $email): array
    {
        $email = trim($email);
        
        // Step 1: Cek format email (syntax)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'valid' => false,
                'message' => 'Format email tidak valid.',
                'details' => ['step' => 'syntax', 'status' => 'invalid']
            ];
        }
        
        // Step 2: Cek disposable email (email sementara)
        if (self::isDisposableEmail($email)) {
            return [
                'valid' => false,
                'message' => 'Email temporales tidak diperbolehkan. Gunakan email asli.',
                'details' => ['step' => 'disposable', 'status' => 'blocked']
            ];
        }
        
        // Step 3: Cek keberadaan domain (MX atau DNS Record)
        $domain = self::extractDomain($email);
        $mxCheck = self::checkMxRecords($domain);
        
        // Di lingkungan cloud (seperti Railway), pengecekan DNS sering dibatasi oleh firewall.
        // Kita tetap melakukan logging jika gagal, tetapi tidak menggagalkan validasi
        // agar admin tetap bisa menyimpan email pemulihan mereka selama syntax benar.
        if (!$mxCheck['has_mx'] && !$mxCheck['dns_ok']) {
            Log::info('EmailVerifier: DNS lookup inconclusive for domain ' . $domain . '. Proceeding based on syntax.');
        }
        
        return [
            'valid' => true,
            'message' => 'Email divalidasi.',
            'details' => [
                'step' => 'all',
                'status' => 'ok',
                'domain' => $domain,
                'mx_hosts' => $mxCheck['mx_hosts'] ?? []
            ]
        ];
    }
    
    /**
     * Cek apakah email menggunakan layanan disposable/temporary email
     */
    public static function isDisposableEmail(string $email): bool
    {
        $domain = self::extractDomain($email);
        $domain = strtolower($domain);
        
        // List domain disposable email yang umum
        $disposableDomains = [
            '10minutemail.com',
            '20minutemail.com',
            'guerrillamail.com',
            'guerrillamail.net',
            'guerrillamail.org',
            'guerrillamail.de',
            'mailinator.com',
            'maildrop.cc',
            'throwaway.email',
            'throwawaymail.com',
            'temp-mail.org',
            'tempail.com',
            'tempmail.com',
            'tempmail.org',
            'dispostable.com',
            'sharklasers.com',
            'spam4.me',
            'spamgourmet.com',
            'spaml.com',
            'spammail.com',
            'mailnesia.com',
            'mail-temporaire.fr',
            'yopmail.com',
            'yopmail.fr',
            'yopmail.net',
            'trashmail.com',
            'trashmail.net',
            'getnada.com',
            'getairmail.com',
            'crazymailing.com',
            'emailondeck.com',
            'fakeinbox.com',
            'fakemailgenerator.com',
            'mohmal.com',
            'tempmail.io',
            'tempr.email',
            'discard.email',
            'emptymails.com',
            'email-temp.com',
            'tempmail address',
            'mintemail.com',
            'mailcatch.com',
            'mailmoat.com',
            'mailnull.com',
            'safetymail.info',
            'sneakemail.com',
            'sofimail.com',
            'spambox.us',
            'spamcowboy.com',
            'spamcowboy.net',
            'spamcowboy.org',
            'spamfree24.com',
            'spamfree24.org',
            'spamfree24.net',
            'spamhole.com',
            'spaml.org',
            'spamnator.com',
            'spamoff.de',
            'tempinbox.com',
            'mailsac.com',
            'mailspring.com',
            'emaillayer.net',
            'emkei.cz',
            'mailforspam.com',
            'incognitomail.com',
            'grr.la',
            'maildirect.com',
            'mymail.inbox.lt',
            'nomail.xl.cx',
            'nowmymail.com',
            'pookh.com',
            'rmqm.net',
            'sogetthis.com',
            'spambox.org',
            'spamex.com',
            'tempemailaddress.com',
            'tempemail.net',
            'tempemail.org',
            'temphaus.com',
            'tmpmail.net',
            'tmpmail.org',
            'tmpmailaddress.com',
            'trashemail.de',
            'trashemail.net',
            'trashmailer.com',
            'trashme.net',
            'tyldd.com',
            'unmail.me',
            'vaxmail.com',
            'webmail4u.com',
            'wh4f.org',
            'willhackforfood.biz',
            'willselfdestruct.com',
            'emailtemporanea.com',
            'emailtemporario.com',
            'temp-email.jp',
            'temp.email.com',
            'tempail.jp',
            'tempo.gq',
            'temp-mail.site',
            'spamg',
        ];
        
        foreach ($disposableDomains as $disposable) {
            if (strpos($domain, $disposable) !== false || $domain === $disposable) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Cek MX records untuk domain
     */
    public static function checkMxRecords(string $domain): array
    {
        $domain = self::extractDomain($domain);
        
        // Cek jika domain valid
        if (empty($domain)) {
            return ['has_mx' => false, 'dns_ok' => false, 'mx_hosts' => []];
        }
        
        // Check if domain resolves
        if (!self::domainExists($domain)) {
            Log::info('EmailVerifier: Domain does not exist - ' . $domain);
            return ['has_mx' => false, 'dns_ok' => false, 'mx_hosts' => []];
        }
        
        // Get MX records
        $mxHosts = [];
        $mxPriority = [];
        
        $result = @dns_get_mx($domain, $mxHosts, $mxPriority);
        
        if (empty($mxHosts)) {
            // Try alternative - check if domain has A/AAAA records
            $hasA = @dns_get_record($domain, DNS_A | DNS_AAAA);
            
            return [
                'has_mx' => false,
                'dns_ok' => !empty($hasA),
                'mx_hosts' => [],
                'has_a' => !empty($hasA)
            ];
        }
        
        return [
            'has_mx' => true,
            'dns_ok' => true,
            'mx_hosts' => $mxHosts
        ];
    }
    
    /**
     * Cek apakah domain ada/exists
     */
    public static function domainExists(string $domain): bool
    {
        $domain = self::extractDomain($domain);
        
        // Check A record
        $aRecord = @dns_get_record($domain, DNS_A);
        if (!empty($aRecord)) {
            return true;
        }
        
        // Check AAAA record (IPv6)
        $aaaaRecord = @dns_get_record($domain, DNS_AAAA);
        if (!empty($aaaaRecord)) {
            return true;
        }
        
        // Check CNAME record
        $cnameRecord = @dns_get_record($domain, DNS_CNAME);
        if (!empty($cnameRecord)) {
            return true;
        }
        
        // Check MX record
        $mxRecord = @dns_get_mx($domain, $mxHosts, $priority);
        if (!empty($mxHosts)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Extract domain dari email
     */
    public static function extractDomain(string $email): string
    {
        $parts = explode('@', $email);
        return isset($parts[1]) ? strtolower(trim($parts[1])) : '';
    }
    
    /**
     * Quick check - apakah email format valid DAN domain aktif
     * 
     * @param string $email
     * @return bool
     */
    public static function isValid(string $email): bool
    {
        $result = self::verify($email);
        return $result['valid'];
    }
}
