<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3><?= __("Two-Factor Authentication Setup"); ?></h3>
                </div>
                <div class="card-body">
                    <?php $this->load->view('layout/messages'); ?>
                    
                    <?php if (!$otp_status['enabled']): ?>
                        <!-- Setup Mode -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5><?= __("Step 1: Install an Authenticator App"); ?></h5>
                                <p><?= __("Download an authenticator app on your mobile device:"); ?></p>
                                <ul>
                                    <li><strong>Google Authenticator</strong> (Android/iOS)</li>
                                    <li><strong>Authy</strong> (Android/iOS/Desktop)</li>
                                    <li><strong>Microsoft Authenticator</strong> (Android/iOS)</li>
                                    <li><strong>1Password</strong> (Android/iOS/Desktop)</li>
                                </ul>
                                
                                <h5><?= __("Step 2: Scan QR Code"); ?></h5>
                                <p><?= __("Open your authenticator app and scan this QR code:"); ?></p>
                                
                                <div class="text-center mb-3">
                                    <div id="qr-code" style="min-height: 200px;">
                                        <div class="spinner-border" role="status">
                                            <span class="visually-hidden"><?= __("Loading..."); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <strong><?= __("Can't scan?"); ?></strong><br>
                                    <?= __("Manually enter this key in your app:"); ?><br>
                                    <code id="manual-key"><?= $this->user_model->get_by_id($this->session->userdata('user_id'))->row()->otp_secret ?? ''; ?></code>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <h5><?= __("Step 3: Verify Setup"); ?></h5>
                                <p><?= __("Enter the 6-digit code from your authenticator app to complete the setup:"); ?></p>
                                
                                <form method="post" action="<?= site_url('user/enable_otp'); ?>">
                                    <div class="mb-3">
                                        <label for="otp_code" class="form-label"><?= __("Authentication Code"); ?></label>
                                        <input type="text" class="form-control text-center" id="otp_code" name="otp_code" 
                                               placeholder="000000" maxlength="6" pattern="[0-9]{6}" required
                                               style="font-size: 1.5rem; letter-spacing: 0.5rem; font-family: monospace;">
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success"><?= __("Enable 2FA"); ?></button>
                                    <a href="<?= site_url('user/profile'); ?>" class="btn btn-secondary"><?= __("Cancel"); ?></a>
                                </form>
                            </div>
                        </div>
                        
                    <?php else: ?>
                        <!-- Management Mode -->
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i>
                            <?= __("Two-factor authentication is enabled and active."); ?>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <h5><?= __("Backup Codes"); ?></h5>
                                <p><?= __("You have {backup_count} backup codes remaining.", array('backup_count' => $otp_status['backup_codes_count'])); ?></p>
                                
                                <?php if ($this->session->userdata('new_backup_codes')): ?>
                                    <div class="alert alert-warning">
                                        <strong><?= __("Save these backup codes!"); ?></strong><br>
                                        <?= __("Store them in a safe place. Each code can only be used once."); ?>
                                        <div class="mt-2">
                                            <?php foreach ($this->session->userdata('new_backup_codes') as $code): ?>
                                                <code class="d-block"><?= $code; ?></code>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php $this->session->unset_userdata('new_backup_codes'); ?>
                                <?php endif; ?>
                                
                                <a href="<?= site_url('user/regenerate_backup_codes'); ?>" class="btn btn-warning btn-sm">
                                    <?= __("Generate New Backup Codes"); ?>
                                </a>
                            </div>
                            
                            <div class="col-md-6">
                                <h5><?= __("Disable Two-Factor Authentication"); ?></h5>
                                <p><?= __("This will remove 2FA protection from your account."); ?></p>
                                
                                <form method="post" action="<?= site_url('user/disable_otp'); ?>" 
                                      onsubmit="return confirm('<?= __("Are you sure you want to disable two-factor authentication?"); ?>');">
                                    <button type="submit" class="btn btn-danger"><?= __("Disable 2FA"); ?></button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!$otp_status['enabled']): ?>
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate QR code
    const qrUrl = <?= json_encode($qr_url ?? ''); ?>;
    if (qrUrl) {
        QRCode.toCanvas(document.createElement('canvas'), qrUrl, {
            width: 200,
            margin: 2
        }, function(error, canvas) {
            if (error) {
                console.error(error);
                document.getElementById('qr-code').innerHTML = '<div class="alert alert-danger">Failed to generate QR code</div>';
            } else {
                document.getElementById('qr-code').innerHTML = '';
                document.getElementById('qr-code').appendChild(canvas);
            }
        });
    }
    
    // Auto-format OTP input
    const otpInput = document.getElementById('otp_code');
    otpInput.addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
});
</script>
<?php endif; ?>
