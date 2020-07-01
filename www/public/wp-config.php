<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wp_advhosting' );

/** MySQL database username */
define( 'DB_USER', 'advhostingmysqluser' );

/** MySQL database password */
define( 'DB_PASSWORD', 'dsf344fsd980jfd' );

/** MySQL hostname */
define( 'DB_HOST', 'database' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'r=yj]x04q@(O7nl9V*~x!/$52T1PIYh,rE2(;*H|g@ sD8,R5IF+3hKLo*(bl;5(' );
define( 'SECURE_AUTH_KEY',  'j]QW8?kM-]G.-&wGT^((X+8}3kP!Ea_<+zzl+~+Z/=^ihmI94}6XgVQc0YGlsBry' );
define( 'LOGGED_IN_KEY',    'Cl=3Y9k/aEe9,6]73g75yDH|NZWpwGr9xqj8/:?[jxycckT-}8ss,WXoo3d>} lG' );
define( 'NONCE_KEY',        'x<F@cxTN;VD6Yb:aAc&Pv,G&7^~qmUG~XDWMMX1&sO#!>3+.7~-`UiM4,i.c;UWL' );
define( 'AUTH_SALT',        'axUq@uS<K<}&cE[d)vF<u=GJ!dP0l*Bg&PS!C~JIc~UyFxD/BX{<!&R0e:c4sqB#' );
define( 'SECURE_AUTH_SALT', '9Gw}y?Rx v0}Y4vn!O{~BN%0(l4>dDd3DHs647KARI2JEZ0ZP*p/`,Y<IN%Ci&H]' );
define( 'LOGGED_IN_SALT',   '<e7+.$q3M,,XxHydFbu#n4;Hu<1WUMdo_Vl{k|[v>gGLVT#YvNHkp~2m^=FDa14E' );
define( 'NONCE_SALT',       '26NeY7wR<OXEQX)~cF 7ifVv6O]lG[I;V}4*+x8,Vmii9|HjaiA.!qx)/{eo=1RZ' );

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * For information on other constants tha t can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );
define( 'WP_HOME', 'https://advancedhosting.cloud' );
define( 'WP_SITEURL', 'https://advancedhosting.cloud' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
