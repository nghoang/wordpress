<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wordpress');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '*tj|/]_0j`]kr)-4/|5VffuK}AXY7}ABb;wcCv&`oqNJ=<EsB~42TUxVgx.|XT}4');
define('SECURE_AUTH_KEY',  'PBMK>=3*Q>kC|tNQp2RCZ#zIBe2xfK3V/P9+g|`Jwsq0Ot[_!eT|nfH&hritU,|_');
define('LOGGED_IN_KEY',    'Wf,1Jj*S_VFt{|Y7|P[rhwq=Rkm_1QSEE<h+`RlF-]kI2E4J <@8<PD[%E)U >AU');
define('NONCE_KEY',        'NWy+lw2go DXa4j=+tbIgCHioF]R|Q X8`|)i5olHx{n7XYXpB^%Sr>#,Q?})+si');
define('AUTH_SALT',        '>CBzRK8LZgP2;NANiWqs{Z4#4dp]5`|jZq6}3N[0u)kjn+BC}M|CVk8i/Y4en<J|');
define('SECURE_AUTH_SALT', 'x|,$k<b<+,(t;r|]RV4~t5^m+^c|RKv70?okyf1{`w-}}oIvazTc@VGr%4lccKOc');
define('LOGGED_IN_SALT',   'n=WIr1Wc!hwk@s0H/U Q e4QI4o4`T|M:^^X)Ni5/T8l:zs=[vqxM=Pe~@95-ei)');
define('NONCE_SALT',       'Q(V#T$$!!d7NL<:Ar(-kJ#8Q7)hMqYzz7,wEMxqvY+a+{Hu^WBy7!]z+}A _m5nI');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
