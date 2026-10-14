<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\User;

class SecurityManager
{
    private string $jwtSecret;

    public function __construct(string $jwtSecret)
    {
        $this->jwtSecret = $jwtSecret;
    }

    public function createJwtToken(User $user): string
    {
        $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        $payload = json_encode([
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'exp' => time() + 3600
        ]);

        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, $this->jwtSecret, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
    }       
    
    public function generateTotpCodeForTesting(string $secret): string
    {
        $secret = strtoupper(str_replace(' ', '', $secret));
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binarySecret = '';
        foreach (str_split($secret) as $char) {
            $pos = strpos($base32Chars, $char);
            $binarySecret .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        $bytes = '';
        foreach (str_split($binarySecret, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }
        $timeSlice = (int) floor(time() / 30);
        $timeBinary = pack('N*', 0) . pack('N*', $timeSlice);
        $hash = hash_hmac('sha1', $timeBinary, $bytes, true);
        $offset = ord($hash[19]) & 0xf; // Holt das letzte Byte (Index 19)
        $calculatedCode = (((ord($hash[$offset]) & 0x7f) << 24) | ((ord($hash[$offset + 1]) & 0xff) << 16) | ((ord($hash[$offset + 2]) & 0xff) << 8) | (ord($hash[$offset + 3]) & 0xff)) % 1000000;
        return str_pad((string) $calculatedCode, 6, '0', STR_PAD_LEFT);
    }

    public function verifyTotpCode(string $secret, string $code): bool
    {
        $secret = strtoupper(str_replace(' ', '', $secret));
        $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $binarySecret = '';
        
        foreach (str_split($secret) as $char) {
            $pos = strpos($base32Chars, $char);
            if ($pos === false) {
                return false;
            }
            $binarySecret .= str_pad(decbin($pos), 5, '0', STR_PAD_LEFT);
        }
        
        $bytes = '';
        foreach (str_split($binarySecret, 8) as $chunk) {
            if (strlen($chunk) === 8) {
                $bytes .= chr(bindec($chunk));
            }
        }
        
        $timeSlice = (int) floor(time() / 30);
        
        // Toleranz-Check: Aktuelles Zeitfenster sowie 1 Vorheriges prüfen
        for ($i = -1; $i <= 0; $i++) {
            $timeBinary = pack('N*', 0) . pack('N*', $timeSlice + $i);
            $hash = hash_hmac('sha1', $timeBinary, $bytes, true);
            $offset = ord($hash[19]) & 0xf;
            
            $calculatedCode = (
                ((ord($hash[$offset]) & 0x7f) << 24) |
                ((ord($hash[$offset + 1]) & 0xff) << 16) |
                ((ord($hash[$offset + 2]) & 0xff) << 8) |
                (ord($hash[$offset + 3]) & 0xff)
            ) % 1000000;
            
            if (str_pad((string) $calculatedCode, 6, '0', STR_PAD_LEFT) === $code) {
                return true;
            }
        }
        
        return false;
    }
}
