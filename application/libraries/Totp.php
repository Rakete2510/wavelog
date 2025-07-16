<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * TOTP (Time-based One-Time Password) Library
 * 
 * This library provides TOTP functionality for Two-Factor Authentication
 * Compatible with Google Authenticator, Authy, and other TOTP apps
 */
class Totp {

    private $length = 6;
    private $period = 30;
    private $algorithm = 'sha1';

    /**
     * Generate a random secret key for TOTP
     * 
     * @param int $length Length of the secret (default: 32)
     * @return string Base32 encoded secret
     */
    public function generateSecret($length = 32) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $secret;
    }

    /**
     * Generate TOTP code for given secret and time
     * 
     * @param string $secret Base32 encoded secret
     * @param int $time Unix timestamp (optional, defaults to current time)
     * @return string 6-digit TOTP code
     */
    public function generateCode($secret, $time = null) {
        if ($time === null) {
            $time = time();
        }

        $timeSlice = intval($time / $this->period);
        $secretKey = $this->base32Decode($secret);
        
        // Pack time into binary string
        $time = chr(0).chr(0).chr(0).chr(0).pack('N*', $timeSlice);
        
        // Hash it with secret key
        $hash = hash_hmac($this->algorithm, $time, $secretKey, true);
        
        // Use last 4 bits of result as index/offset
        $offset = ord(substr($hash, -1)) & 0x0F;
        
        // Grab 4 bytes of the result
        $hashpart = substr($hash, $offset, 4);
        
        // Unpack binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;
        
        $modulo = pow(10, $this->length);
        
        return str_pad($value % $modulo, $this->length, '0', STR_PAD_LEFT);
    }

    /**
     * Verify TOTP code
     * 
     * @param string $secret Base32 encoded secret
     * @param string $code User provided code
     * @param int $discrepancy Time window to check (±discrepancy periods)
     * @param int $time Current time (optional)
     * @return bool True if code is valid
     */
    public function verifyCode($secret, $code, $discrepancy = 1, $time = null) {
        if ($time === null) {
            $time = time();
        }

        // Check current time and nearby periods
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->generateCode($secret, $time + ($i * $this->period));
            if ($this->timingSafeEquals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate QR code URL for TOTP setup
     * 
     * @param string $secret Base32 encoded secret
     * @param string $name Account name (e.g., username)
     * @param string $issuer Service name (e.g., "Wavelog")
     * @return string QR code URL
     */
    public function getQRCodeUrl($secret, $name, $issuer = 'Wavelog') {
        $name = urlencode($name);
        $issuer = urlencode($issuer);
        $secret = urlencode($secret);
        
        return "otpauth://totp/{$issuer}:{$name}?secret={$secret}&issuer={$issuer}";
    }

    /**
     * Generate backup codes
     * 
     * @param int $count Number of backup codes to generate
     * @return array Array of backup codes
     */
    public function generateBackupCodes($count = 8) {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = $this->generateRandomCode(8);
        }
        return $codes;
    }

    /**
     * Generate a random backup code
     * 
     * @param int $length Length of the code
     * @return string Random code
     */
    private function generateRandomCode($length = 8) {
        $chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        // Format as XXXX-XXXX
        return substr($code, 0, 4) . '-' . substr($code, 4, 4);
    }

    /**
     * Verify backup code
     * 
     * @param string $code User provided backup code
     * @param array $backupCodes Array of valid backup codes
     * @return bool|int Returns the index of used code if valid, false otherwise
     */
    public function verifyBackupCode($code, $backupCodes) {
        $code = strtoupper(str_replace('-', '', $code));
        foreach ($backupCodes as $index => $backupCode) {
            $backupCode = strtoupper(str_replace('-', '', $backupCode));
            if ($this->timingSafeEquals($code, $backupCode)) {
                return $index;
            }
        }
        return false;
    }

    /**
     * Base32 decode
     * 
     * @param string $input Base32 encoded string
     * @return string Decoded binary string
     */
    private function base32Decode($input) {
        if (empty($input)) return '';
        
        $input = strtoupper($input);
        $lut = array(
            'A' => 0,  'B' => 1,  'C' => 2,  'D' => 3,
            'E' => 4,  'F' => 5,  'G' => 6,  'H' => 7,
            'I' => 8,  'J' => 9,  'K' => 10, 'L' => 11,
            'M' => 12, 'N' => 13, 'O' => 14, 'P' => 15,
            'Q' => 16, 'R' => 17, 'S' => 18, 'T' => 19,
            'U' => 20, 'V' => 21, 'W' => 22, 'X' => 23,
            'Y' => 24, 'Z' => 25, '2' => 26, '3' => 27,
            '4' => 28, '5' => 29, '6' => 30, '7' => 31
        );

        $input = rtrim($input, '=');
        $l = strlen($input);
        $n = 0;
        $j = 0;
        $binary = '';

        for ($i = 0; $i < $l; $i++) {
            $n = $n << 5;
            $n = $n + $lut[$input[$i]];
            $j = $j + 5;
            if ($j >= 8) {
                $j = $j - 8;
                $binary .= chr(($n & (0xFF << $j)) >> $j);
            }
        }

        return $binary;
    }

    /**
     * Timing safe string comparison
     * 
     * @param string $safe Known string
     * @param string $user User provided string
     * @return bool True if strings match
     */
    private function timingSafeEquals($safe, $user) {
        if (function_exists('hash_equals')) {
            return hash_equals($safe, $user);
        }

        if (strlen($safe) !== strlen($user)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < strlen($safe); $i++) {
            $result |= (ord($safe[$i]) ^ ord($user[$i]));
        }

        return $result === 0;
    }
}
