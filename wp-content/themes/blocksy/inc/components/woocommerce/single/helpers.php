<?php

function blocksy_has_product_share_box() {
    $prefix = blocksy_manager()->screen->get_prefix();

    $post_type = get_post_type();

    if ($post_type !== 'product') {
        return false;
    }

    $default_product_layout = blocksy_get_woo_single_layout_defaults();

    $layout = blocksy_get_theme_mod(
        'woo_single_layout',
        $default_product_layout
    );

    $layout = blocksy_normalize_layout(
        $layout,
        $default_product_layout
    );

    $product_view_type = blocksy_get_product_view_type();

    if (
        $product_view_type === 'top-gallery'
        ||
        $product_view_type === 'columns-top-gallery'
    ) {
        $woo_single_split_layout = blocksy_get_theme_mod(
            'woo_single_split_layout',
            [
                'left' => blocksy_get_woo_single_layout_defaults('left'),
                'right' => blocksy_get_woo_single_layout_defaults('right')
            ]
        );

        $layout = array_merge(
            $woo_single_split_layout['left'],
            $woo_single_split_layout['right']
        );
    }

    $product_sharebox = array_values(array_filter($layout, function($k) {
        return $k['id'] === 'product_sharebox';
    }));

    if (empty($product_sharebox)) {
        return false;
    }

    if (
        isset($product_sharebox[0]['enabled'])
        &&
        $product_sharebox[0]['enabled']
    ) {
        return true;
    }

    return false;
}