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

    .form-signin {
        width: 100%;
        max-width: 430px;
        padding: 15px;
        margin: auto;
    }

    .form-signin input[type="email"] {
        margin-bottom: -1px;
        border-bottom-right-radius: 0;
        border-bottom-left-radius: 0;
    }

    .form-signin input[type="password"] {
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
</style>
<main class="form-signin">
    <img src="<?php echo base_url(); ?>assets/logo/<?php echo $this->optionslib->get_logo('main_logo'); ?>.png" class="mx-auto d-block mainLogo" alt="">
    <?php if (ENVIRONMENT == 'maintenance') { ?>
        <div class="d-flex justify-content-center align-items-center">
            <span class="badge text-bg-warning mb-4 pt-2 pb-2"><?= __("MAINTENANCE MODE"); ?></span>
        </div>
    <?php } ?>
    <div class="my-2 rounded-0 shadow-sm card mb-2 shadow-sm">
        <div class="card-body">
            <?php 
            /**
             * Wavelog Demo
             * 
             * This enables the Wavelog Demo as used in https://demo.wavelog.org.
             * 
             * If you want to use this, place a file called `.demo` in the root folder 
             * and create a non-admin user `demo` with password `demo` by hand. 
             * 
             * It's recommend to create a cronjob which resets this installation every day at 0200 UTC from a backup.
             * We do not provide any functionality for this, so you have to build this on your own.
             * 
             */
            if (file_exists('.demo')) { ?>
                <div class="border-bottom mb-3">
                    <h5><?= __("Welcome to the Demo of Wavelog"); ?></h5>
                    <p><?= __("This demo will be reset every night at 0200z."); ?><br><br>
                    <?= __("Username"); ?>: demo<br>
                    <?= __("Password"); ?>: demo<br><br>
                    <?= sprintf(__("More Information about Wavelog on %sGithub%s."), '<a href="https://www.github.com/wavelog/wavelog" target="_blank">', '</a>'); ?></p>
                </div>
            <?php }
            // End of Demo Part
             ?>
            <form method="post" action="<?php echo site_url('user/login'); ?>" name="users">
                <?php $this->form_validation->set_error_delimiters('', ''); ?>
                <input type="hidden" name="id" value="<?php echo $this->uri->segment(3); ?>" />
                <div class="mb-2">
                    <label for="floatingInput"><strong><?= __("Username"); ?></strong></label>
                    <input type="text" name="user_name" class="form-control" id="floatingInput" placeholder="<?php if (file_exists('.demo')) { echo "demo"; } else { echo __("Username"); } ?>" value="<?php echo $this->input->post('user_name'); ?>" autofocus>
                </div>
                <div class="mb-2">
                    <label for="floatingPassword"><strong><?= __("Password"); ?></strong></label>
                    <input type="password" name="user_password" class="form-control" id="floatingPassword" placeholder="<?php if (file_exists('.demo')) { echo "demo"; } else { echo __("Password"); } ?>">
                </div>
                <div class="mb-2" id="otpField" style="display: <?php echo (isset($show_otp) && $show_otp) ? 'block' : 'none'; ?>;">
                    <label for="otp_code"><strong><?= __("2FA Code"); ?></strong></label>
                    <input type="text" name="otp_code" class="form-control text-center" id="otp_code" 
                           placeholder="000000" maxlength="8" pattern="[0-9A-Z\-]+" 
                           autocomplete="one-time-code" value="<?php echo $this->input->post('otp_code'); ?>"
                           style="font-family: monospace; letter-spacing: 0.2rem;">
                    <div class="form-text"><?= __("Enter the 6-digit code from your app, or backup code"); ?></div>
                </div>
                <div class="mb-2">
                    <div class="row">
                        <div class="col text-start">
                            <small><a class="" href="<?php echo site_url('user/forgot_password'); ?>"><?= __("Forgot your password?"); ?></a></small>
                        </div>
                        <div class="col text-end">
                            <?php  // we only want to create these cookies if the site is reached by https
                                if ($https_check == true && $this->config->item('encryption_key') != 'flossie1234555541') { ?>
                                    <input type="checkbox" value="1" name="keep_login" id="keep_login" />
                                    <label for="keep_login"><small><?= __("Keep me logged in"); ?></small></label>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php $this->load->view('layout/messages'); ?>
                <button class="w-100 btn btn-primary" type="submit"><?= __("Login"); ?> →</button>
            </form>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form[name="users"]');
    const otpField = document.getElementById('otpField');
    const otpInput = document.getElementById('otp_code');
    
    // Check if OTP field should be visible and focus on it
    <?php if (isset($show_otp) && $show_otp): ?>
    if (otpInput) {
        otpInput.focus();
    }
    <?php endif; ?>
    
    // Format OTP input as user types
    if (otpInput) {
        otpInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9A-Z\-]/g, '').toUpperCase();
            
            // If it's a 6-digit code, format with spaces for better readability
            if (value.length <= 6 && !value.includes('-')) {
                value = value.replace(/(.{3})(.{1,3})/, '$1 $2').trim();
            }
            
            e.target.value = value;
        });
        
        otpInput.addEventListener('paste', function(e) {
            setTimeout(() => {
                let value = e.target.value.replace(/[^0-9A-Z\-]/g, '').toUpperCase();
                e.target.value = value;
            }, 10);
        });
        
        // Submit form when 6 digits are entered (without spaces)
        otpInput.addEventListener('input', function(e) {
            const cleanValue = e.target.value.replace(/\s/g, '');
            if (cleanValue.length === 6 && !cleanValue.includes('-')) {
                // Auto-submit after short delay
                setTimeout(() => {
                    form.submit();
                }, 500);
            }
        });
    }
    
    // Handle form submission with potential OTP requirement
    form.addEventListener('submit', function(e) {
        const hasOtpField = otpField.style.display !== 'none';
        
        // If OTP field is visible but empty, focus on it
        if (hasOtpField && !otpInput.value.trim()) {
            e.preventDefault();
            otpInput.focus();
            return;
        }
    });
});
</script>
