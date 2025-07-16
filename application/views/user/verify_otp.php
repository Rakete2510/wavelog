<style>
    html,
    body {
        height: 100%;
    }

    body {
        display: flex;
        align-items: center;
        padding-top: 40px;
        padding-bottom: 40px;
    }

    .form-otp {
        width: 100%;
        max-width: 430px;
        padding: 15px;
        margin: auto;
    }

    .otp-input {
        font-size: 1.5rem;
        text-align: center;
        letter-spacing: 0.5rem;
        font-family: monospace;
    }
</style>

<main class="form-otp">
    <img src="<?php echo base_url(); ?>assets/logo/<?php echo $this->optionslib->get_logo('main_logo'); ?>.png" class="mx-auto d-block mainLogo" alt="">
    
    <div class="my-2 rounded-0 shadow-sm card mb-2 shadow-sm">
        <div class="card-body">
            <div class="text-center mb-3">
                <h5 class="card-title"><?= __("Two-Factor Authentication"); ?></h5>
                <p class="text-muted"><?= __("Enter the 6-digit code from your authenticator app"); ?></p>
            </div>

            <form method="post" action="<?php echo site_url('user/verify_otp'); ?>" name="otp_form">
                <?php $this->form_validation->set_error_delimiters('', ''); ?>
                
                <div class="mb-3">
                    <label for="otp_code"><strong><?= __("Authentication Code"); ?></strong></label>
                    <input type="text" name="otp_code" class="form-control otp-input" id="otp_code" 
                           placeholder="000000" maxlength="8" pattern="[0-9A-Z\-]+" 
                           autocomplete="one-time-code" autofocus>
                    <div class="form-text"><?= __("Enter the 6-digit code from your app, or an 8-character backup code"); ?></div>
                </div>

                <?php $this->load->view('layout/messages'); ?>
                
                <button class="w-100 btn btn-primary mb-2" type="submit"><?= __("Verify"); ?></button>
                
                <div class="text-center">
                    <a href="<?php echo site_url('user/login'); ?>" class="text-muted"><?= __("← Back to Login"); ?></a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp_code');
    
    // Auto-format input as user types
    otpInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^0-9A-Z\-]/g, '').toUpperCase();
        
        // Format backup codes with dash
        if (value.length > 6 && value.indexOf('-') === -1) {
            value = value.substring(0, 4) + '-' + value.substring(4, 8);
        }
        
        e.target.value = value;
    });
    
    // Submit form when 6 digits are entered
    otpInput.addEventListener('input', function(e) {
        if (e.target.value.replace(/[^0-9]/g, '').length === 6) {
            // Small delay to ensure the user sees the complete code
            setTimeout(function() {
                document.forms.otp_form.submit();
            }, 500);
        }
    });
});
</script>
