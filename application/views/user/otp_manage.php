<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header">
                    <h3><?= __("Two-Factor Authentication Management"); ?></h3>
                </div>
                <div class="card-body">
                    <?php $this->load->view('layout/messages'); ?>
                    
                    <div class="alert alert-success">
                        <i class="fas fa-shield-alt"></i>
                        <?= __("Two-factor authentication is enabled and protecting your account."); ?>
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
                                            <code class="d-block mb-1"><?= htmlspecialchars($code); ?></code>
                                        <?php endforeach; ?>
                                    </div>
                                    <button class="btn btn-sm btn-outline-primary mt-2" onclick="printBackupCodes()">
                                        <i class="fas fa-print"></i> <?= __("Print Codes"); ?>
                                    </button>
                                    <button class="btn btn-sm btn-outline-secondary mt-2" onclick="downloadBackupCodes()">
                                        <i class="fas fa-download"></i> <?= __("Download"); ?>
                                    </button>
                                </div>
                                <?php $this->session->unset_userdata('new_backup_codes'); ?>
                            <?php endif; ?>
                            
                            <?php if ($otp_status['backup_codes_count'] <= 2): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <?= __("You're running low on backup codes. Consider generating new ones."); ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="<?= site_url('user/regenerate_backup_codes'); ?>" 
                               class="btn btn-warning"
                               onclick="return confirm('<?= __("This will invalidate your existing backup codes. Continue?"); ?>');">
                                <i class="fas fa-sync-alt"></i>
                                <?= __("Generate New Backup Codes"); ?>
                            </a>
                        </div>
                        
                        <div class="col-md-6">
                            <h5><?= __("Account Security"); ?></h5>
                            <p><?= __("Your account is protected with two-factor authentication."); ?></p>
                            
                            <div class="mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    <?= __("If you lose access to your authenticator app, you can use backup codes to log in."); ?>
                                </small>
                            </div>
                            
                            <h6><?= __("Disable Two-Factor Authentication"); ?></h6>
                            <p class="text-muted"><?= __("This will remove 2FA protection from your account."); ?></p>
                            
                            <form method="post" action="<?= site_url('user/disable_otp'); ?>" 
                                  onsubmit="return confirm('<?= __("Are you sure you want to disable two-factor authentication? This will make your account less secure."); ?>');">
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-shield-alt"></i>
                                    <?= __("Disable 2FA"); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="text-center">
                        <a href="<?= site_url('user/profile'); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i>
                            <?= __("Back to Profile"); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function printBackupCodes() {
    const codes = <?= json_encode($this->session->userdata('new_backup_codes') ?? []); ?>;
    if (codes.length === 0) return;
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
        <head>
            <title><?= __("Wavelog Backup Codes"); ?></title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                h1 { color: #333; }
                .code { font-family: monospace; font-size: 16px; margin: 5px 0; }
                .warning { color: #d9534f; margin: 20px 0; }
            </style>
        </head>
        <body>
            <h1><?= __("Wavelog Two-Factor Authentication Backup Codes"); ?></h1>
            <p><?= __("Store these codes in a safe place. Each code can only be used once."); ?></p>
            ${codes.map(code => '<div class="code">' + code + '</div>').join('')}
            <div class="warning">
                <strong><?= __("Important:"); ?></strong>
                <ul>
                    <li><?= __("Keep these codes secure and private"); ?></li>
                    <li><?= __("Each code can only be used once"); ?></li>
                    <li><?= __("Generate new codes if you lose these"); ?></li>
                </ul>
            </div>
            <p><em><?= __("Generated on"); ?>: ${new Date().toLocaleString()}</em></p>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
}

function downloadBackupCodes() {
    const codes = <?= json_encode($this->session->userdata('new_backup_codes') ?? []); ?>;
    if (codes.length === 0) return;
    
    const content = `Wavelog Two-Factor Authentication Backup Codes
Generated on: ${new Date().toLocaleString()}

Store these codes in a safe place. Each code can only be used once.

${codes.join('\n')}

Important:
- Keep these codes secure and private
- Each code can only be used once  
- Generate new codes if you lose these
`;
    
    const blob = new Blob([content], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'wavelog-backup-codes.txt';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
}
</script>
