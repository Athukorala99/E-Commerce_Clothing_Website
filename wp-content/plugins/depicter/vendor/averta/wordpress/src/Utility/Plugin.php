<?php
namespace Averta\WordPress\Utility;

class Plugin
{
    /**
     * Determines whether a plugin is active.
     *
     * @param string $plugin_basename Path to the plugin file relative to the plugins directory.
     *
     * @return mixed
     */
    public static function isActive( $plugin_basename ){
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        return is_plugin_active( $plugin_basename );
    }
}
