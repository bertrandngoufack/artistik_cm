<?php
/**
 * WordPress — Artistik CM (Docker MariaDB)
 *
 * @package WordPress
 */

define( 'DB_NAME', getenv( 'DB_NAME' ) ?: 'artistik_cm_db' );
define( 'DB_USER', getenv( 'DB_USER' ) ?: 'app_user' );
define( 'DB_PASSWORD', getenv( 'DB_PASSWORD' ) !== false ? (string) getenv( 'DB_PASSWORD' ) : 'Bateau123' );
define( 'DB_HOST', getenv( 'DB_HOST' ) ?: 'mariadb' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

define( 'AUTH_KEY',         'q#V|+nCGKxmZTY;>*Vpc$jZ7_iCIM@dk@va@]+Zy#G<QA;w59kLE[Y&A-i7vt2Rv' );
define( 'SECURE_AUTH_KEY',  'kP{4Wn|iVg9dX87yP9f a gfcy~5Mjs,|ct2AQO{L]|~dY}TeK*o. +RKVj=->/d' );
define( 'LOGGED_IN_KEY',    'y~oi?cdg[L($d4kpv8V^@Fnwo5[^>_:GGnz&i-Dov(1lA[+=gblP5hyGVYZf3b)n' );
define( 'NONCE_KEY',        'Umuo^BN{^muZvWUyl(f$srs.CTqQ/4S64XFS&<8In[Z[GSYug;zC$s[tNe3>Cn||' );
define( 'AUTH_SALT',        ']iV`hI}O4ay;Ih(WgV=}l *5shou(aqA(Pc_atQSGc#M?<zk_8LuLC?%}`JwwA-R' );
define( 'SECURE_AUTH_SALT', '-8E.eUf@8*.Dnr# -.T{ey=K.h[%~y8L<Z uS>!5TuoVMDQs #^@^i^$c&@nOcH:' );
define( 'LOGGED_IN_SALT',   'XyGASy;0B: JH*1t`:;306{X+#%AtK|Vew&)Lt|CbP|h(iTG=Gl5n?&F,rg-D0kw' );
define( 'NONCE_SALT',       ',R_me*s=|FhuDl,Onx55eS?v+|6EL|I6.<wNM6 Ulr/n`toi,s~P>1(uZ`vf=*xd' );

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );

/** Écriture des fichiers sans FTP dans le conteneur. */
define( 'FS_METHOD', 'direct' );

/**
 * URL publiques : priorité 1 prod (hébergement), 2 sous-dossier Docker (.env WP_PUBLIC_URL), sinon valeurs en base.
 * Sur votre hébergeur vous pouvez fixer avant la mise en ligne correcte du SQL :
 *   ARTISTIK_CM_SITE_URL=https://www.artistik.cm
 * dans l’env PHP qui lit ce fichier ou un SetEnv Apache autorisé.
 */
$wp_prod_url = getenv( 'ARTISTIK_CM_SITE_URL' );
$wp_public_url = getenv( 'WP_PUBLIC_URL' );

if ( is_string( $wp_prod_url ) && $wp_prod_url !== '' ) {
	$wp_prod_url = rtrim( $wp_prod_url, '/' );
	if ( filter_var( $wp_prod_url, FILTER_VALIDATE_URL ) ) {
		define( 'WP_HOME', $wp_prod_url );
		define( 'WP_SITEURL', $wp_prod_url );
	}
} elseif ( is_string( $wp_public_url ) && $wp_public_url !== '' ) {
	$wp_public_url = rtrim( $wp_public_url, '/' );
	if ( filter_var( $wp_public_url, FILTER_VALIDATE_URL ) ) {
		define( 'WP_HOME', $wp_public_url );
		define( 'WP_SITEURL', $wp_public_url );
	}
}

/* Add any custom values between this line and the "That's all" comment. */

/* That's all, stop editing! Happy publishing. */

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

require_once ABSPATH . 'wp-settings.php';
