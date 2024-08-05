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

$disabled = 'disabled';
$published = __( 'Published', 'depicter' );
$noticeText = '';

if ( ! $isPublishedBefore ) {
    $noticeText = __( 'This slider is not published yet and is not visible to visitors. Open the editor and publish now. This notice is only visible to you.', 'depicter' );
} elseif ( $documentStatus == 'draft' ) {
    $noticeText = __( 'This slider has unpublished changes. Publish the changes to see the final result.', 'depicter' );
}

$documentID = $documentID ?? 0;
?>
<div class="depicter-editor-notice">
    <?php
    if ( $documentStatus == 'draft' ) {
        $disabled = '';
        $published = __( 'Publish Slider', 'depicter' );
        ?>
        <div class="depicter-notice-txts">
            <span class="depicter-notice-icon">!</span>
            <span><?php echo esc_html( $noticeText );?></span>
        </div>
        <?php
    }
    ?>

    <div class="depicter-notice-btns">
        <button class="depicter-edit-slider elementor-button" data-document-id="<?php echo esc_attr( $documentID );?>"><?php echo esc_html__( 'Edit Slider', 'depicter' );?></button>
        <button class="depicter-publish-slider elementor-button elementor-button-success" data-document-id="<?php echo esc_attr( $documentID );?>" <?php echo esc_attr( $disabled );?>>
            <span class="btn-label"> <?php echo esc_html( $published );?></span>
            <span class="depicter-state-icon" style="display: none;"></span>
        </button>
    </div>
</div>
