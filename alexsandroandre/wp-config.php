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
define('DB_NAME', 'alexs159_wrdp1');

/** MySQL database username */
//define('DB_USER', 'alexs159_wrdp1');
define('DB_USER', 'root');

/** MySQL database password */
//define('DB_PASSWORD', 'AH4y0IkatLZ7dv9');
define('DB_PASSWORD', '');

/** MySQL hostname */
//define('DB_HOST', '50.116.86.15');
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'V>x~gPut?%n:G2M `0>yg2`9dTQt^B??#a%*_rY#7+7.=biWrvU~WS<oSUM1 JgP');
define('SECURE_AUTH_KEY',  'qV)t^h^n`NHkSUD^TnRP3/Hc0&$$ja{P;Oq)Q#h$w3+|Z.5OK2D::!#(+xgo,o_3');
define('LOGGED_IN_KEY',    'Dy1@g1ofoFSxf T,;FUd0.02r1t0~*??S2-:Ky:YOzx4V}X<Q3yV@{2uFp$FXEZj');
define('NONCE_KEY',        '8IP<[@`S;/ULG.}a3-!2)5<|2J:@Y y8*u`Hz)}>y2_.yH4t&3wml)2gwf5|.N2M');
define('AUTH_SALT',        'u=8yi.dbmf+GU.uKeV9g]d<5JMAs1z&l%58!]o9zl}Z6`&;3I8|aEPF?aCtB}s5M');
define('SECURE_AUTH_SALT', '*D}}>(Q[2-eW:b0pc~Ys5`Xy.|+Re_Zos~)WyGL&JY>m[},sYIV,=o!T4vv]S<0O');
define('LOGGED_IN_SALT',   'OL0SNNH2!NTdK37Q@5|~nq_)!#B+@,J$e`f%:IbcBVb{A;=<mHM:P FyU)$_#2HD');
define('NONCE_SALT',       'RI!#wmz=;8={bdB^^-Hc`E{ _am?Pc.BW+~Em-2a.V{I5bS4Sq6RUN}}7zY]f?`E');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
