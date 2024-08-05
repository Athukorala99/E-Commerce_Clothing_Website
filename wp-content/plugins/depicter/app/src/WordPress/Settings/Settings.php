<?php

namespace Depicter\WordPress\Settings;

use Depicter\WordPress\Settings\Options\Checkbox;
use Depicter\WordPress\Settings\Options\Choices;
use Depicter\WordPress\Settings\Options\CodeEditor;
use Depicter\WordPress\Settings\Options\Select;
use Depicter\WordPress\Settings\Options\SelectMultiple;
use Depicter\WordPress\Settings\Options\Text;
use Depicter\WordPress\Settings\Options\Textarea;
use Depicter\WordPress\Settings\Options\WPEditor;
use Depicter\Jeffreyvr\WPSettings\WPSettings;
use Depicter\WordPress\Settings\Options\Nonce;

class Settings extends WPSettings
{

    public $prefix = 'depicter_';
    
    public function __construct( $title, $slug = null, $prefix = 'depicter_')
    {
        $this->prefix = $prefix;
        add_filter( 'wp_settings_option_type_map', [ $this, 'change_options_handler' ] );
        
        parent::__construct( $title, $slug );
    }

    public function change_options_handler( $types )
    {
        return [
            'text' => Text::class,
            'checkbox' => Checkbox::class,
            'choices' => Choices::class,
            'textarea' => Textarea::class,
            'wp-editor' => WPEditor::class,
            'code-editor' => CodeEditor::class,
            'select' => Select::class,
            'select-multiple' => SelectMultiple::class,
            'nonce' => Nonce::class
        ];
    }

    public function get_url()
    {
        if ($this->parent_slug) {
            return \add_query_arg('page', $this->slug, \admin_url("admin.php?page=" . $this->parent_slug));
        }

        return \admin_url("admin.php?page=$this->slug");
    }

    public function save()
    {
        if (! isset($_POST['wp_settings_trigger'])) {
            return;
        }

        if (! current_user_can($this->capability)) {
            wp_die(__('What do you think you are doing?'));
        }

        if (! isset($_POST[$this->option_name]) && !isset($_POST['wp_settings_submitted'])) {
            return;
        }

        $new_options = apply_filters('wp_settings_new_options', $_POST[$this->option_name] ?? [] );

        if ( empty( $new_options['_depicter_settings_nonce'] ) || ! wp_verify_nonce( $new_options['_depicter_settings_nonce'], 'depicter-settings' ) ) {
            wp_die(__('Permission Error!'));
        }

        foreach ($new_options as $option => $value) {
            $_option = $this->find_option($option);

            $valid = $_option->validate($value);

            if (!$valid) {
                continue;
            }

            $value = apply_filters( "wp_settings_new_options_$option", $_option->sanitize($value), $_option );

            update_option( $this->prefix . $option, $_option->sanitize( $value ) );
        }

        // checking unchecked checkbox option
        foreach ($this->tabs as $tab) {
            foreach ($tab->sections as $section) {
                foreach ($section->options as $option) {
                    if ( $option->type == 'checkbox' && !isset( $new_options[ $option->args['name' ] ] ) ) {
                        update_option( $this->prefix . $option->args['name'], '' );
                    }
                }
            }
        }

        $this->flash->set('success', __('Saved changes!'));
    }
}