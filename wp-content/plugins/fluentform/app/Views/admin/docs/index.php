<?php
    do_action_deprecated(
        'fluentform_global_menu',
        [
        ],
        FLUENTFORM_FRAMEWORK_UPGRADE,
        'fluentform/global_menu',
        'Use fluentform/global_menu instead of fluentform_global_menu.'
    );
    do_action('fluentform/global_menu');
?>
<div class="ff_form_wrap" id="ff_documentation_app">
    <div class="ff_form_wrap_area">
        <div class="ff_documentaion_wrapper">
            <?php
                do_action_deprecated(
                    'fluentform_before_documentation_wrapper',
                    [
                    ],
                    FLUENTFORM_FRAMEWORK_UPGRADE,
                    'fluentform/before_documentation_wrapper',
                    'Use fluentform/before_documentation_wrapper instead of fluentform_before_documentation_wrapper.'
                );
                do_action('fluentform/before_documentation_wrapper');
            ?>

            <div class="el-row" style="margin-left: -12px; margin-right: -12px;">
                <!-- .el-col -->
                
                <?php if(!defined('FLUENTFORMPRO')): ?>
                    <div class="el-col el-col-24" style="padding-left: 12px; padding-right: 12px">
                        <div class="ff_card h-100 ff_card_pro primary has-mask">
                            <div class="mask">
                                <div class="mask-1"></div>
                                <div class="mask-2"></div>
                                <div class="mask-3"></div>
                            </div>
                            <h3><?php _e('To unlock more features consider upgrading to PRO', 'fluentform') ?></h3>
                            <a target="_blank" rel="nofollow" class="el-button ff_upgrade_btn_large" href="https://fluentforms.com/pricing/?utm_source=plugin&utm_medium=wp_install&utm_campaign=ff_upgrade&theme_style=kadence">Upgrade to Pro</a>
                        </div>
                    </div><!-- .el-col -->
                <?php endif; ?>

                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <h3 class="mb-3"><?php _e('Get Started with Fluent Form Wordpress Plugin', 'fluentform') ?></h3>
                        <p class="text"><?php _e('Getting started with the Fluent Forms is easier than you could imagine. All our customers are not developers and we want to make your life easier.', 'fluentform') ?></p>
                        <div class="ff_video_wrap mt-5">
                            <img class="ff_video_img" src="<?php echo esc_url($public_url); ?>img/video-img.jpg"/>
                            <a href="#" id="ff_video_btn" class="ff_icon_btn ff_video_icon">
                                <i class="ff-icon ff-icon-play"></i>
                            </a>
                        </div>
                        <div id="ff_backdrop">
                            <div id="ff_dialog_wrapper" class="el-dialog__wrapper hidden">
                                <div role="dialog" aria-modal="true" class="el-dialog" style="margin-top: 25vh; width: 50%;">
                                    <div class="el-dialog__header mb-5">
                                        <h4><?php _e( 'Fluent Forms Video Tutorials', 'fluentform' ) ?></h4>
                                        <button id="ff_close_btn" type="button" aria-label="Close" class="el-dialog__headerbtn">
                                            <i class="el-dialog__close el-icon el-icon-close"></i>
                                        </button>
                                    </div>
                                    <div class="el-dialog__body">
                                        <iframe class="w-100" style="height: 340px; border-radius: 8px;" src="https://www.youtube.com/embed/vH0GuhqHA7I?controls=0" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                    </div>
                                </div>
                            </div><!-- .el-dialog__wrapper -->
                        </div>
                    </div>
                </div><!-- .el-col -->
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <h3 class="mb-3"><?php _e('User Guides', 'fluentform') ?></h3>
                        <p class="text">
                            <?php _e('Please check the following articles for getting started with Fluent Forms', 'fluentform') ?>
                        </p>
                        <ul class="ff_list">
                            <?php foreach ($user_guides as $user_guide): ?>
                                <li>
                                    <a target="_blank" href="<?php echo esc_url($user_guide['link']); ?>">
                                      <i class="el-icon el-icon-caret-right"></i>
                                      <span><?php echo esc_html($user_guide['title']); ?></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div><!-- .el-col -->
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <div class="ff_media_group items-start">
                            <div class="ff_media_head">
                                <div class="ff_icon_btn success-soft lg">
                                    <i class="ff-icon ff-icon-microphone"></i>
                                </div>
                            </div>
                            <div class="ff_media_body ml-4">
                                <h3 class="mb-2"><?php _e('Need Expert Support?', 'fluentform') ?></h3>
                                <p class="text mb-4">
                                    <?php _e('Our Experts would like to assist you for your query and any customization.', 'fluentform') ?>
                                </p>
                                <a target="_blank" class="el-button el-button--success el-button--soft" href="https://wpmanageninja.com/support-tickets/">
                                    <?php _e('Contact Support', 'fluentform') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!--.el-col -->
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <div class="ff_media_group items-start">
                            <div class="ff_media_head">
                                <div class="ff_icon_btn primary-soft lg">
                                    <i class="ff-icon ff-icon-document"></i>
                                </div>
                            </div>
                            <div class="ff_media_body ml-4">
                                <h3 class="mb-2"><?php _e('Documentation', 'fluentform') ?></h3>
                                <p class="text mb-4">
                                    <?php _e('Get detailed and guided instruction to level up your website with the necessary set up.', 'fluentform') ?>
                                </p>
                                <a target="_blank" class="el-button el-button--primary el-button--soft" href="https://wpmanageninja.com/docs/fluent-form/">
                                    <?php _e('Visit Documentation', 'fluentform') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!--.el-col -->
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <div class="ff_media_group items-start">
                            <div class="ff_media_head">
                                <div class="ff_icon_btn warning-soft lg">
                                    <i class="ff-icon ff-icon-bug"></i>
                                </div>
                            </div>
                            <div class="ff_media_body ml-4">
                                <h3 class="mb-2"><?php _e('Facing An Issue Or Problem?', 'fluentform') ?></h3>
                                <p class="text mb-4">
                                    <?php _e('Please report us and we promise we will fix that as soon as humanly possible.', 'fluentform') ?>
                                </p>
                                <a target="_blank" class="el-button el-button--warning el-button--soft" href="https://wpmanageninja.com/support-tickets/">
                                    <?php _e('Report An Issue', 'fluentform') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!--.el-col -->
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <div class="ff_media_group items-start">
                            <div class="ff_media_head">
                                <div class="ff_icon_btn blue-soft lg">
                                    <i class="ff-icon ff-icon-handshake"></i>
                                </div>
                            </div>
                            <div class="ff_media_body ml-4">
                                <h3 class="mb-2"><?php _e('Join our community', 'fluentform') ?></h3>
                                <p class="text mb-4">
                                    <?php _e('We have a strong community where we discuss ideas and help each other.', 'fluentform') ?>
                                </p>
                                <a target="_blank" class="el-button el-button--blue el-button--soft" href="https://www.facebook.com/groups/fluentforms/">
                                    <?php _e('Join community', 'fluentform') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!--.el-col -->
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <div class="ff_media_group items-start">
                            <div class="ff_media_head">
                                <div class="ff_icon_btn danger-soft lg">
                                    <i class="ff-icon ff-icon-heart-clock"></i>
                                </div>
                            </div>
                            <div class="ff_media_body ml-4">
                                <h3 class="mb-2"><?php _e('Show Your Love', 'fluentform') ?></h3>
                                <p class="text mb-4">
                                    <?php _e('We need your help to keep developing the plugin. Please review it and spread the love to keep us motivated.', 'fluentform') ?>
                                </p>
                                <a target="_blank" class="el-button el-button--danger el-button--soft" href="https://wordpress.org/support/plugin/fluentform/reviews/#new-post">
                                    <?php _e('Leave a Review', 'fluentform') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!--.el-col -->
                <div class="el-col el-col-12" style="padding-left: 12px; padding-right: 12px">
                    <div class="ff_card h-100">
                        <div class="ff_media_group items-start">
                            <div class="ff_media_head">
                                <div class="ff_icon_btn cyan-soft lg">
                                    <i class="ff-icon ff-icon-star-line"></i>
                                </div>
                            </div>
                            <div class="ff_media_body ml-4">
                                <h3 class="mb-2"><?php _e('Request a Feature', 'fluentform') ?></h3>
                                <p class="text mb-4">
                                    <?php _e('If you need any feature on fluentform, then please request a feature to us with your requirement.', 'fluentform') ?>
                                </p>
                                <a target="_blank" class="el-button el-button--cyan el-button--soft" href="https://github.com/fluentform/fluentform/issues">
                                    <?php _e('Request Now', 'fluentform') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div><!--.el-col -->
            </div><!-- .el-row -->


            <?php
            do_action_deprecated(
                'fluentform_after_documentation_wrapper',
                [
                ],
                FLUENTFORM_FRAMEWORK_UPGRADE,
                'fluentform/after_documentation_wrapper',
                'Use fluentform/after_documentation_wrapper instead of fluentform_after_documentation_wrapper.'
            );
            do_action('fluentform/after_documentation_wrapper');

            wp_add_inline_script('fluent_forms_global', "

                // For support Page Modal
                let btnOpenEl = document.getElementById('ff_video_btn');
                let btnCloseEl = document.getElementById('ff_close_btn');
                let dialogEl = document.getElementById('ff_dialog_wrapper');
    
                dialogEl.classList.add('hidden');
    
                btnOpenEl.addEventListener('click', function(e){
                    e.preventDefault();
                    dialogEl.parentElement.classList.add('ff_backdrop');
                    dialogEl.classList.remove('hidden');
                    dialogEl.classList.add('dialog-fade-enter-active');
                });
    
                btnCloseEl.addEventListener('click', function(){
                    dialogEl.parentElement.classList.remove('ff_backdrop');
                    dialogEl.classList.add('hidden');
                    dialogEl.classList.remove('dialog-fade-enter-active');
                });
            ");
            ?>

        </div>
    </div>
    <global-search></global-search>
</div>
