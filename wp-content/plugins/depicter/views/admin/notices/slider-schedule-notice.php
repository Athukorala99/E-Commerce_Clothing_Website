<?php
/**
 * Blank canvas.
 *
 * @package Depicter
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// $view_args is required to be passed

global $wp_version;
extract( $view_args );
?>

<script type="text/javascript">
    (function () {
        var init = function () {
            document.querySelectorAll(".depicter-notice-wrapper").forEach(function( element ) {
                if (!element.dataset.hasEvent) {
                    element.dataset.hasEvent = true;
                    element
                    .querySelector(".close-icon")
                    .addEventListener("click", function() {
                        element.remove();
                    });
                }
            });
        };

        if (document.readyState === "complete") {
            init();
        } else {
            document.addEventListener("DOMContentLoaded", init);
        }
    })();
</script>

<style>
.depicter-notice-wrapper{
    position: absolute !important;
    top: 20px !important;
    left: 20px !important;
    display: flex !important;
    align-items: flex-start !important;
    background-color: #7361E8 !important;
    border-radius: 5px !important;
    padding: 10px !important;
    box-shadow: 5px 10px 30px #00000026 !important;
    max-width: 533px !important;
    z-index: 50 !important;
}

.depicter-notice-wrapper span {
    color: #FFFFFF !important;
    font-size: 12px !important;
    line-height: 17px !important;
    font-weight: 600 !important;
    font-family: sans-serif;
}

.depicter-notice-wrapper span.notice-icon {
    margin-right: 7px !important;
    width: 17px;
}

.depicter-notice-wrapper span.notice-icon img {
    max-width: 17px;
}

.depicter-notice-wrapper a {
    color: #00D4E4 !important;
    text-decoration: underline !important;
}

.depicter-notice-wrapper .close-icon {
    width: 10px !important;
    height: 10px !important;
    margin-left: 10px !important;
    cursor: pointer !important;
}

.depicter-notice-wrapper .close-icon:before,
    .depicter-notice-wrapper .close-icon:after {
    width: 2px !important;
    height: 10px !important;
    background-color: #fff !important;
    display: block !important;
    content: " " !important;
    position: absolute !important;
}

.depicter-notice-wrapper .close-icon:before {
    transform: rotate(45deg) !important;
}
.depicter-notice-wrapper .close-icon:after {
    transform: rotate(-45deg) !important;
}

</style>

<div class="depicter-notice-wrapper">
    <span class="notice-icon"><img src="<?php echo Depicter::core()->assets()->getUrl() . '/resources/images/svg/clock.svg'; ?>"></span>
        <?php
        echo '<span>';
        echo sprintf( esc_html__( 'It\'s currently hidden due to visibility scheduling settings. To change the settings, %s Open Depicter editor %s. Note that this message is only visible to you, not to visitors.', 'depicter'), '<a href="' . $editUrl . '" target="_blank">', '</a>' );
        echo '</span><span class="close-icon"></span>';
        ?>
</div>
