<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ecom' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '9qP|SR/#vX}-Io}M+^U6S-N)Sb^iQ.%Ukz_0h$o^:MWJT;?Ok2]/ DXXTAE=OS-2' );
define( 'SECURE_AUTH_KEY',  '.mjmUM[Cn`o|s.tm7#?>,0wfAKx8@$!P|v~UAHNUUa5e5Dw.2n$btQYAjb>8qgg7' );
define( 'LOGGED_IN_KEY',    '<JeB@Po#/4F?Q&EdP8&}KNJtcqe-!UtGSYVC ()y@R^WI}QLbb#6CE1txf~7G3{.' );
define( 'NONCE_KEY',        'b}wRMHseBz,0]Exgas6Eu%@h&>`iGZJ+Tze$>nn3u%#jQY=2yS$i+I9))Gl/J50d' );
define( 'AUTH_SALT',        '?#mZ4++9qe+0p>Z+k,7V,_aOA;|w8d{,Mr}FZC_<1]2eiV3SnL^h`ReJDO{sW3ze' );
define( 'SECURE_AUTH_SALT', 'yAi3xt$Cdjz1W(M<F@/83gAW;1d!w>6diCchMqGv8VXwr|D8x)c6LTy1[w~HVqb/' );
define( 'LOGGED_IN_SALT',   '6L6p`qja;|Ai.([T+VUXTTVbw4|>+<qmJm&E0gyou!i%sREos]:_l|.PkCww|g8:' );
define( 'NONCE_SALT',       'TES9!w*ZLX!n19Y}SfZ@ha8!2zXbCca96)9(PJAVVn];[n]^Y@T>pTd{R!${|<WB' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
