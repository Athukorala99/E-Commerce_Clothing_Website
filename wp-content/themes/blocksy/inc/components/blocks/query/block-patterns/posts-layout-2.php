<?php

$pattern = [
	'title'      => __( 'Posts - Layout 2', 'blocksy' ),
	'categories' => ['blocksy'],
	'blockTypes' => ['blocksy/query'],

	'content' => '<!-- wp:blocksy/query {"uniqueId":"d68b6aec","limit":6} -->
	<div class="wp-block-blocksy-query"><!-- wp:blocksy/post-template {"layout":{"type":"grid","columnCount":3}} -->
	<!-- wp:group {"style":{"spacing":{"blockGap":"0"},"dimensions":{"minHeight":"100%"}},"layout":{"type":"flex","orientation":"vertical","justifyContent":"left"}} -->
	<div class="wp-block-group" style="min-height:100%"><!-- wp:blocksy/dynamic-data {"field":"wp:featured_image","has_field_link":"yes","style":{"spacing":{"margin":{"bottom":"0"}}}} /-->

	<!-- wp:group {"style":{"dimensions":{"minHeight":"100%"},"layout":{"selfStretch":"fill","flexSize":null}},"backgroundColor":"palette-color-8","layout":{"type":"constrained"}} -->
	<div class="wp-block-group has-palette-color-8-background-color has-background" style="min-height:100%"><!-- wp:blocksy/dynamic-data {"tagName":"h2","has_field_link":"yes","style":{"typography":{"fontSize":"18px"}}} /-->

	<!-- wp:blocksy/dynamic-data {"field":"wp:date","fontSize":"small","style":{"typography":{"textTransform":"uppercase","fontStyle":"normal","fontWeight":"500"}}} /-->

	<!-- wp:blocksy/dynamic-data {"field":"wp:excerpt","excerpt_length":20} /--></div>
	<!-- /wp:group --></div>
	<!-- /wp:group -->
	<!-- /wp:blocksy/post-template --></div>
	<!-- /wp:blocksy/query -->'
];
