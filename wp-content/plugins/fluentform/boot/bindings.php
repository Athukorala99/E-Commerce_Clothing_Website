<?php

use FluentForm\App\Services\FormBuilder\Components;
use FluentForm\App\Services\FormBuilder\FormBuilder;
use FluentForm\App\Services\WPAsync\FluentFormAsyncRequest;

/**
 * Add only the plugin specific bindings here.
 *
 * $app
 * @var \FluentForm\Framework\Foundation\Application
 */

// Bind the Form Builder to the App instance.
$app->bind('formBuilder', function ($app) {
    return new FormBuilder($app);
});

$app->singleton('components', function($app) {
    return new Components(fluentformLoadFile('Services/FormBuilder/DefaultElements.php'));
});

$app->bind('fluentFormAsyncRequest', function ($app) {
    return new FluentFormAsyncRequest($app);
});
