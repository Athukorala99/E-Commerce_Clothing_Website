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
    background-color: #F7BA19 !important;
    border-radius: 5px !important;
    padding: 10px !important;
    box-shadow: 5px 10px 30px #00000026 !important;
    max-width: 600px !important;
    z-index: 50 !important;
}

.depicter-notice-wrapper span {
    color: black !important;
    font-size: 12px !important;
    line-height: 17px !important;
    font-weight: 600 !important;
    font-family: sans-serif;
}

.depicter-notice-wrapper span.notice-icon {
    background-color: #fff !important;
    color: #F7BA19 !important;
    padding: 2px 8px !important;
    font-size: 14px !important;
    border-radius: 50% !important;
    margin-right: 7px !important;
}

.depicter-notice-wrapper a {
    color: #0A00FF !important;
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
    <span class="notice-icon">!</span>

        <?php
        echo '<span>';
        if ( ! $isPublishedBefore ) {
            echo esc_html__('This slider is not published yet and is not visible to visitors.', 'depicter' ) . '<br>';
        } else {
            echo esc_html__('This slider has unpublished changes. Publish the changes to see the final result.', 'depicter' ) . '<br>';
        }
        echo '<a href="' . esc_url( $editUrl ) . '" target="_blank">' . esc_html__( 'Open the editor', 'depicter' ) . '</a> ' . esc_html__( 'and publish now.', 'depicter' ) . ' ';
        echo esc_html__( 'This notice is only visible to you.', 'depicter' ) . '</span><span class="close-icon"></span>';
        ?>
</div>
