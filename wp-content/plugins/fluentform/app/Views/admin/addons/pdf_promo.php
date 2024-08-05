<div class="add_on_modules">
    <div class="modules_header mb-6">
        <h4 class="title mb-2">Fluent Forms PDF Modules</h4> 
        <p class="text">Generate PDF from your form submissions. You can create PDF templates and download / send via email too.</p>
    </div>
    <div class="modules_body">
        <?php if(!$is_installed): ?>
            <div class="install_wrapper text-center mb-5">
                <img class="mb-5" src="<?php echo esc_url($public_url . 'img/pdf-promo-img.png'); ?>" alt="">
                <h2 class="mb-4">PDF Module is not installed yet. Please install now <span class="text-danger">(it's free)</span></h2>
                <p class="fs-15 mb-5" style="width: 540px; margin-left: auto; margin-right: auto;">Generate PDF from your form submissions. You can create PDF templates and download / send via email too.</p>
                <a class="el-button el-button--primary" href="<?php echo esc_url($install_url); ?>">
                    Install Fluent Forms PDF Addon
                </a>
            </div>
        <?php else:
            do_action_deprecated(
                'fluentform_addons_page_render_fluentform_pdf_settings',
                [

                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/addons_page_render_fluentform_pdf_settings',
                'Use fluentform/addons_page_render_fluentform_pdf_settings instead of fluentform_addons_page_render_fluentform_pdf_settings.'
            );
            do_action('fluentform/addons_page_render_fluentform_pdf_settings');
        endif; ?>

    </div>
</div>
