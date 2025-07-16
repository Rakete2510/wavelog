<?php

defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class Migration_add_otp_2fa_to_users
 * 
 * Adds Two-Factor Authentication (OTP) support to the users table
 */
class Migration_add_otp_2fa_to_users extends CI_Migration {

    public function up()
    {
        // Add OTP secret key field
        if (!$this->db->field_exists('otp_secret', 'users')) {
            $fields = array(
                'otp_secret varchar(32) DEFAULT NULL COMMENT "OTP Secret Key for 2FA"',
            );
            $this->dbforge->add_column('users', $fields);
        }

        // Add OTP enabled flag
        if (!$this->db->field_exists('otp_enabled', 'users')) {
            $fields = array(
                'otp_enabled tinyint(1) DEFAULT 0 COMMENT "Is OTP/2FA enabled for this user"',
            );
            $this->dbforge->add_column('users', $fields);
        }

        // Add backup codes field (JSON array of backup codes)
        if (!$this->db->field_exists('otp_backup_codes', 'users')) {
            $fields = array(
                'otp_backup_codes text DEFAULT NULL COMMENT "JSON array of backup codes"',
            );
            $this->dbforge->add_column('users', $fields);
        }

        // Add last used timestamp for OTP to prevent replay attacks
        if (!$this->db->field_exists('otp_last_used', 'users')) {
            $fields = array(
                'otp_last_used timestamp NULL DEFAULT NULL COMMENT "Last time OTP was used"',
            );
            $this->dbforge->add_column('users', $fields);
        }
    }

    public function down()
    {
        if ($this->db->field_exists('otp_secret', 'users')) {
            $this->dbforge->drop_column('users', 'otp_secret');
        }
        
        if ($this->db->field_exists('otp_enabled', 'users')) {
            $this->dbforge->drop_column('users', 'otp_enabled');
        }
        
        if ($this->db->field_exists('otp_backup_codes', 'users')) {
            $this->dbforge->drop_column('users', 'otp_backup_codes');
        }
        
        if ($this->db->field_exists('otp_last_used', 'users')) {
            $this->dbforge->drop_column('users', 'otp_last_used');
        }
    }
}
