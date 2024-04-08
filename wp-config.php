<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'd6370355a5abc9a465cc3367be0623ac2962c8cd55f74a8a' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

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
define( 'AUTH_KEY',         's)v`gPm~__cJMqg>o|4B{>4Muy8<q6t+]2]_yb[JxR}W~Z|mJUZ4jO$^bjwalw&>' );
define( 'SECURE_AUTH_KEY',  'uCu+<K/;LKumrUk^=d#&,KG3x9UoYJI0L.E4QA& `es0Nl1):{~>4~n)&BRTT(CD' );
define( 'LOGGED_IN_KEY',    ' #2.+ &HC]@vaA1dpG<9iaiNUvHG:G4{LAd|6%9lB*Q7$~hQGs#I14N+ds8@4:Z<' );
define( 'NONCE_KEY',        'G7X$UCFp8c{EorVsHLFFGiXR2J-1-O}kP:mpD%el@BaY*jRlg/]m6lai}q@J>3$=' );
define( 'AUTH_SALT',        'PkO@fls<:kkK{a7^M)IW!fa5FZc,>9}i`adX&*KNaWT)0Y>+1xnea$=wT4<:8ey6' );
define( 'SECURE_AUTH_SALT', '@CK6|lFc@n @k<85$+nj*u!d;|Mj>06[8oHz;ZAI6fAyn_m{MsC4R+h?t^Tj5*=V' );
define( 'LOGGED_IN_SALT',   'z?>FL|pT.~n?h_>TT/TVtr*7PFd<B](W~lsrO4^f$b;~2l^E2wmN)E&/d1-Vt3K!' );
define( 'NONCE_SALT',       '/HAo#(J=[mj/d<vKm@DU`GMW2y2K&u{kw_4zAy,/({ fu|eiRO@#LzIrHERo]isL' );

define('JWT_AUTH_SECRET_KEY', 'ccdev');
define('JWT_AUTH_CORS_ENABLE', true);
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
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
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
