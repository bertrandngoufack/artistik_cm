<?php
/**
 * Thème enfant AIHub — Artistik one-page.
 * Fournit :
 *  - CPT « Solution » (paramétrable via l'admin WP)
 *  - Metaboxes (badge, couleur, icône, modules)
 *  - Customizer pour le hero, les statistiques et le bloc contact
 *  - Template one-page dynamique
 *  - Page d'aide dans l'admin
 *
 * @package AIHub_Child_Artistik
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ARTISTIK_AIHUB_CHILD_VER', '2.4.0' );

/* -------------------------------------------------------------
 * Chargement des assets
 * ------------------------------------------------------------- */

function artistik_aihub_child_enqueue_assets(): void {
	wp_enqueue_style(
		'artistik-onepage',
		get_stylesheet_directory_uri() . '/assets/artistik-onepage.css',
		array(),
		ARTISTIK_AIHUB_CHILD_VER
	);
}
add_action( 'wp_enqueue_scripts', 'artistik_aihub_child_enqueue_assets', 99 );

function artistik_aihub_child_admin_assets( string $hook ): void {
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
	if ( ! $screen ) {
		return;
	}
	if ( 'ak_solution' !== $screen->post_type && 'appearance_page_artistik-help' !== $screen->id ) {
		return;
	}
	wp_enqueue_style(
		'artistik-admin',
		get_stylesheet_directory_uri() . '/assets/artistik-admin.css',
		array(),
		ARTISTIK_AIHUB_CHILD_VER
	);
}
add_action( 'admin_enqueue_scripts', 'artistik_aihub_child_admin_assets' );

/* -------------------------------------------------------------
 * CPT « Solution »
 * ------------------------------------------------------------- */

function artistik_register_solution_cpt(): void {
	$labels = array(
		'name'               => __( 'Solutions', 'aihub-child-artistik' ),
		'singular_name'      => __( 'Solution', 'aihub-child-artistik' ),
		'menu_name'          => __( 'Solutions Artistik', 'aihub-child-artistik' ),
		'add_new'            => __( 'Ajouter une solution', 'aihub-child-artistik' ),
		'add_new_item'       => __( 'Ajouter une solution', 'aihub-child-artistik' ),
		'edit_item'          => __( 'Modifier la solution', 'aihub-child-artistik' ),
		'new_item'           => __( 'Nouvelle solution', 'aihub-child-artistik' ),
		'view_item'          => __( 'Voir la solution', 'aihub-child-artistik' ),
		'all_items'          => __( 'Toutes les solutions', 'aihub-child-artistik' ),
		'search_items'       => __( 'Rechercher une solution', 'aihub-child-artistik' ),
		'not_found'          => __( 'Aucune solution trouvée.', 'aihub-child-artistik' ),
	);
	register_post_type(
		'ak_solution',
		array(
			'labels'        => $labels,
			'public'        => false,
			'show_ui'       => true,
			'show_in_menu'  => true,
			'menu_position' => 25,
			'menu_icon'     => 'dashicons-screenoptions',
			'supports'      => array( 'title', 'editor', 'page-attributes', 'thumbnail' ),
			'hierarchical'  => false,
			'has_archive'   => false,
			'show_in_rest'  => true,
			'rewrite'       => false,
		)
	);
}
add_action( 'init', 'artistik_register_solution_cpt' );

/* -------------------------------------------------------------
 * Metaboxes : badge, couleur, icône, ancre, modules
 * ------------------------------------------------------------- */

function artistik_solution_meta_keys(): array {
	return array(
		'_ak_anchor'   => '',
		'_ak_badge'    => '',
		'_ak_color'    => 'med',
		'_ak_icon'     => 'shield-plus',
		'_ak_subtitle' => '',
		'_ak_modules'  => '',
	);
}

function artistik_color_options(): array {
	return array(
		'med'  => __( 'Santé (teal)', 'aihub-child-artistik' ),
		'edu'  => __( 'Éducation (indigo)', 'aihub-child-artistik' ),
		're'   => __( 'Immobilier (amber)', 'aihub-child-artistik' ),
		'com'   => __( 'Commerce / POS (cyan)', 'aihub-child-artistik' ),
		'agro'  => __( 'Agropastoral / Élevage (vert)', 'aihub-child-artistik' ),
		'dent'  => __( 'Dentaire (bleu clair)', 'aihub-child-artistik' ),
		'trans' => __( 'Transport / Flotte (violet)', 'aihub-child-artistik' ),
	);
}

function artistik_icon_options(): array {
	return array(
		'shield-plus' => __( 'Croix médicale', 'aihub-child-artistik' ),
		'graduation'  => __( 'Diplôme', 'aihub-child-artistik' ),
		'building'    => __( 'Bâtiment', 'aihub-child-artistik' ),
		'chart'       => __( 'Graphique', 'aihub-child-artistik' ),
		'briefcase'   => __( 'Métier', 'aihub-child-artistik' ),
		'spark'       => __( 'Étincelle', 'aihub-child-artistik' ),
		'cart'        => __( 'Caddie / commerce', 'aihub-child-artistik' ),
		'shop'        => __( 'Boutique', 'aihub-child-artistik' ),
		'cow'         => __( 'Bovin / élevage', 'aihub-child-artistik' ),
		'leaf'        => __( 'Feuille / agriculture', 'aihub-child-artistik' ),
		'tooth'       => __( 'Dent', 'aihub-child-artistik' ),
		'truck-2'     => __( 'Camion', 'aihub-child-artistik' ),
		'gauge'       => __( 'Compteur / kilométrage', 'aihub-child-artistik' ),
		'fuel'        => __( 'Carburant', 'aihub-child-artistik' ),
		'tools'       => __( 'Maintenance', 'aihub-child-artistik' ),
		'map'         => __( 'Carte / itinéraire', 'aihub-child-artistik' ),
	);
}

function artistik_solution_register_meta(): void {
	$page  = 'ak_solution';
	$ctx   = 'side';
	add_meta_box(
		'ak_solution_meta',
		__( 'Configuration de la solution', 'aihub-child-artistik' ),
		'artistik_solution_metabox_render',
		$page,
		'normal',
		'high'
	);
	add_meta_box(
		'ak_solution_modules',
		__( 'Modules / fonctionnalités', 'aihub-child-artistik' ),
		'artistik_solution_modules_render',
		$page,
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'artistik_solution_register_meta' );

function artistik_solution_metabox_render( WP_Post $post ): void {
	wp_nonce_field( 'ak_solution_meta', 'ak_solution_nonce' );
	$values = array();
	foreach ( artistik_solution_meta_keys() as $key => $default ) {
		$current = get_post_meta( $post->ID, $key, true );
		$values[ $key ] = ( $current === '' ) ? $default : $current;
	}
	?>
	<div class="ak-admin-grid">
		<p>
			<label for="ak_anchor"><strong><?php esc_html_e( 'Identifiant d’ancre', 'aihub-child-artistik' ); ?></strong></label><br />
			<input type="text" id="ak_anchor" name="_ak_anchor" value="<?php echo esc_attr( $values['_ak_anchor'] ); ?>" placeholder="ex. solumed" class="regular-text" />
			<small class="description"><?php esc_html_e( 'Utilisé pour les ancres et le menu (ex. #solumed). Laissez vide pour utiliser le slug.', 'aihub-child-artistik' ); ?></small>
		</p>
		<p>
			<label for="ak_badge"><strong><?php esc_html_e( 'Badge (étiquette courte)', 'aihub-child-artistik' ); ?></strong></label><br />
			<input type="text" id="ak_badge" name="_ak_badge" value="<?php echo esc_attr( $values['_ak_badge'] ); ?>" placeholder="ex. Santé" class="regular-text" />
		</p>
		<p>
			<label for="ak_subtitle"><strong><?php esc_html_e( 'Sous-titre', 'aihub-child-artistik' ); ?></strong></label><br />
			<input type="text" id="ak_subtitle" name="_ak_subtitle" value="<?php echo esc_attr( $values['_ak_subtitle'] ); ?>" placeholder="ex. Logiciel de gestion des cliniques" class="large-text" />
		</p>
		<p>
			<label for="ak_color"><strong><?php esc_html_e( 'Couleur thématique', 'aihub-child-artistik' ); ?></strong></label><br />
			<select id="ak_color" name="_ak_color">
				<?php foreach ( artistik_color_options() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $values['_ak_color'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="ak_icon"><strong><?php esc_html_e( 'Icône', 'aihub-child-artistik' ); ?></strong></label><br />
			<select id="ak_icon" name="_ak_icon">
				<?php foreach ( artistik_icon_options() as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $values['_ak_icon'], $key ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
	</div>
	<?php
}

function artistik_solution_modules_render( WP_Post $post ): void {
	$modules_raw = (string) get_post_meta( $post->ID, '_ak_modules', true );
	?>
	<p class="description"><?php esc_html_e( 'Format simple — un module par bloc, séparés par une ligne vide. La 1re ligne est le titre du module, les suivantes sont les éléments :', 'aihub-child-artistik' ); ?></p>
	<pre style="background:#f6f7f7;padding:10px;border-radius:4px;">## Patients
- Ouverture de dossier
- Admission
- Réadmission

## Caisse
- Encaissement patient
- Reçus de caisse
- Point de caisse</pre>
	<textarea name="_ak_modules" rows="14" style="width:100%;font-family:Menlo,monospace;font-size:13px;line-height:1.5;"><?php echo esc_textarea( $modules_raw ); ?></textarea>
	<?php
}

function artistik_solution_save_meta( int $post_id, WP_Post $post ): void {
	if ( 'ak_solution' !== $post->post_type ) {
		return;
	}
	if ( ! isset( $_POST['ak_solution_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ak_solution_nonce'] ) ), 'ak_solution_meta' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	$colors = array_keys( artistik_color_options() );
	$icons  = array_keys( artistik_icon_options() );

	$anchor = isset( $_POST['_ak_anchor'] ) ? sanitize_title( wp_unslash( $_POST['_ak_anchor'] ) ) : '';
	if ( $anchor === '' ) {
		$anchor = $post->post_name ?: sanitize_title( get_the_title( $post_id ) );
	}
	update_post_meta( $post_id, '_ak_anchor', $anchor );

	update_post_meta( $post_id, '_ak_badge', isset( $_POST['_ak_badge'] ) ? sanitize_text_field( wp_unslash( $_POST['_ak_badge'] ) ) : '' );
	update_post_meta( $post_id, '_ak_subtitle', isset( $_POST['_ak_subtitle'] ) ? sanitize_text_field( wp_unslash( $_POST['_ak_subtitle'] ) ) : '' );

	$color = isset( $_POST['_ak_color'] ) ? sanitize_key( wp_unslash( $_POST['_ak_color'] ) ) : 'med';
	if ( ! in_array( $color, $colors, true ) ) {
		$color = 'med';
	}
	update_post_meta( $post_id, '_ak_color', $color );

	$icon = isset( $_POST['_ak_icon'] ) ? sanitize_key( wp_unslash( $_POST['_ak_icon'] ) ) : 'shield-plus';
	if ( ! in_array( $icon, $icons, true ) ) {
		$icon = 'shield-plus';
	}
	update_post_meta( $post_id, '_ak_icon', $icon );

	$modules = isset( $_POST['_ak_modules'] ) ? wp_kses_post( wp_unslash( $_POST['_ak_modules'] ) ) : '';
	update_post_meta( $post_id, '_ak_modules', $modules );
}
add_action( 'save_post_ak_solution', 'artistik_solution_save_meta', 10, 2 );

/**
 * Parse le bloc texte « modules » en groupes structurés.
 *
 * @return array<int, array{title:string, items: array<int,string>}>
 */
function artistik_parse_modules( string $raw ): array {
	$raw  = trim( $raw );
	$out  = array();
	if ( $raw === '' ) {
		return $out;
	}
	$blocks = preg_split( '/\R{2,}/', $raw );
	if ( ! is_array( $blocks ) ) {
		return $out;
	}
	foreach ( $blocks as $block ) {
		$lines = preg_split( '/\R+/', trim( $block ) );
		if ( ! is_array( $lines ) || empty( $lines ) ) {
			continue;
		}
		$title = trim( ltrim( array_shift( $lines ), '#' ) );
		$items = array();
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( $line === '' ) {
				continue;
			}
			$items[] = trim( ltrim( $line, "-*•\t " ) );
		}
		if ( $title !== '' || ! empty( $items ) ) {
			$out[] = array(
				'title' => $title,
				'items' => $items,
			);
		}
	}
	return $out;
}

/**
 * Liste des solutions ordonnées (par menu_order puis par date).
 *
 * @return WP_Post[]
 */
function artistik_get_solutions(): array {
	$q = new WP_Query(
		array(
			'post_type'      => 'ak_solution',
			'posts_per_page' => 50,
			'post_status'    => 'publish',
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
			'no_found_rows'  => true,
		)
	);
	return $q->posts ? $q->posts : array();
}

/* -------------------------------------------------------------
 * Customizer : hero, statistiques, contact
 * ------------------------------------------------------------- */

function artistik_customize_register( WP_Customize_Manager $wp_customize ): void {
	$wp_customize->add_section(
		'ak_hero',
		array( 'title' => __( 'Artistik — Hero', 'aihub-child-artistik' ), 'priority' => 30 )
	);
	$fields = array(
		'ak_hero_eyebrow'   => array( 'label' => __( 'Sur-titre', 'aihub-child-artistik' ), 'default' => 'Artistik — logiciels métier' ),
		'ak_hero_title'     => array( 'label' => __( 'Titre principal', 'aihub-child-artistik' ), 'default' => 'Des applications pensées pour votre secteur' ),
		'ak_hero_lead'      => array( 'label' => __( 'Accroche (sous-titre)', 'aihub-child-artistik' ), 'default' => 'Depuis 2006, Artistik conçoit des solutions fiables pour la santé, l’éducation et l’immobilier.' , 'type' => 'textarea' ),
		'ak_hero_cta_label' => array( 'label' => __( 'Texte bouton principal', 'aihub-child-artistik' ), 'default' => 'Découvrir nos solutions' ),
		'ak_hero_cta_url'   => array( 'label' => __( 'Lien bouton principal', 'aihub-child-artistik' ), 'default' => '#solutions' ),
	);
	foreach ( $fields as $id => $cfg ) {
		$wp_customize->add_setting( $id, array( 'default' => $cfg['default'], 'type' => 'option', 'sanitize_callback' => 'wp_kses_post' ) );
		$wp_customize->add_control(
			$id,
			array(
				'label'    => $cfg['label'],
				'section'  => 'ak_hero',
				'type'     => $cfg['type'] ?? 'text',
			)
		);
	}

	/* Stats */
	$wp_customize->add_section( 'ak_stats', array( 'title' => __( 'Artistik — Statistiques', 'aihub-child-artistik' ), 'priority' => 31 ) );
	for ( $i = 1; $i <= 3; $i++ ) {
		$wp_customize->add_setting( "ak_stat_{$i}_value", array( 'default' => array( 1 => '2016', 2 => '7', 3 => '100%' )[ $i ], 'type' => 'option', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( "ak_stat_{$i}_value", array( 'label' => sprintf( __( 'Valeur %d', 'aihub-child-artistik' ), $i ), 'section' => 'ak_stats', 'type' => 'text' ) );
		$wp_customize->add_setting( "ak_stat_{$i}_label", array( 'default' => array( 1 => 'Année de création d’Artistik', 2 => 'familles de produits', 3 => 'orienté gestion métier' )[ $i ], 'type' => 'option', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( "ak_stat_{$i}_label", array( 'label' => sprintf( __( 'Étiquette %d', 'aihub-child-artistik' ), $i ), 'section' => 'ak_stats', 'type' => 'text' ) );
	}

	/* Contact */
	$wp_customize->add_section( 'ak_contact', array( 'title' => __( 'Artistik — Contact', 'aihub-child-artistik' ), 'priority' => 32 ) );
	$contact = array(
		'ak_contact_title' => array( 'default' => 'Un projet ? Parlons-en.' ),
		'ak_contact_text'  => array( 'default' => 'Déploiement, démonstration ou accompagnement : l’équipe Artistik vous répond.' ),
		'ak_contact_email' => array( 'default' => 'info@artistik.cm' ),
		'ak_contact_url'   => array( 'default' => 'https://artistik.cm' ),
	);
	foreach ( $contact as $id => $cfg ) {
		$wp_customize->add_setting( $id, array( 'default' => $cfg['default'], 'type' => 'option', 'sanitize_callback' => 'wp_kses_post' ) );
		$wp_customize->add_control( $id, array( 'label' => str_replace( 'ak_contact_', '', $id ), 'section' => 'ak_contact', 'type' => 'text' ) );
	}
}
add_action( 'customize_register', 'artistik_customize_register' );

function artistik_opt( string $key, string $default = '' ): string {
	$v = get_option( $key, $default );
	return is_string( $v ) ? $v : (string) $v;
}

/* -------------------------------------------------------------
 * Page d'aide dans l'admin
 * ------------------------------------------------------------- */

function artistik_register_help_page(): void {
	add_submenu_page(
		'themes.php',
		__( 'Aide Artistik', 'aihub-child-artistik' ),
		__( 'Aide Artistik', 'aihub-child-artistik' ),
		'edit_theme_options',
		'artistik-help',
		'artistik_render_help_page'
	);
}
add_action( 'admin_menu', 'artistik_register_help_page' );

function artistik_render_help_page(): void {
	$customize_url = admin_url( 'customize.php' );
	$pages_url     = admin_url( 'edit.php?post_type=page' );
	$menu_url      = admin_url( 'nav-menus.php' );
	$cpt_url       = admin_url( 'edit.php?post_type=ak_solution' );
	$trp_url       = admin_url( 'admin.php?page=trp_settings' );
	$trp_editor    = home_url( '/?trp-edit-translation=preview' );
	$smtp_url      = admin_url( 'admin.php?page=gosmtp' );
	$form_id       = (int) get_option( 'ak_contact_form_id', 0 );
	$form_url      = $form_id > 0 ? admin_url( 'admin.php?page=formidable&frm_action=edit&id=' . $form_id ) : admin_url( 'admin.php?page=formidable' );
	$entries_url   = $form_id > 0 ? admin_url( 'admin.php?page=formidable-entries&form=' . $form_id ) : admin_url( 'admin.php?page=formidable-entries' );
	?>
	<div class="wrap ak-help">
		<h1><?php esc_html_e( 'Configuration du site Artistik', 'aihub-child-artistik' ); ?></h1>
		<p class="description"><?php esc_html_e( 'Vous pouvez tout modifier sans toucher au code, depuis l’administration WordPress.', 'aihub-child-artistik' ); ?></p>

		<div class="ak-help-grid">
			<div class="ak-help-card">
				<h2>1. <?php esc_html_e( 'Solutions', 'aihub-child-artistik' ); ?></h2>
				<p><?php esc_html_e( 'Ajoutez ou modifiez vos solutions (SoluMed, LyCol, Simba, etc.). Chaque solution apparaît automatiquement sur la page d’accueil.', 'aihub-child-artistik' ); ?></p>
				<p><a class="button button-primary" href="<?php echo esc_url( $cpt_url ); ?>"><?php esc_html_e( 'Gérer les solutions', 'aihub-child-artistik' ); ?></a></p>
			</div>
			<div class="ak-help-card">
				<h2>2. <?php esc_html_e( 'Hero, stats et contact', 'aihub-child-artistik' ); ?></h2>
				<p><?php esc_html_e( 'Personnalisez le titre, le sous-titre, les chiffres-clés et les coordonnées dans le Customizer.', 'aihub-child-artistik' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( $customize_url ); ?>"><?php esc_html_e( 'Ouvrir le Customizer', 'aihub-child-artistik' ); ?></a></p>
			</div>
			<div class="ak-help-card">
				<h2>3. <?php esc_html_e( 'Pages additionnelles', 'aihub-child-artistik' ); ?></h2>
				<p><?php esc_html_e( 'Créez des pages classiques (mentions légales, conditions, articles, etc.). Elles seront accessibles via le menu.', 'aihub-child-artistik' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( $pages_url ); ?>"><?php esc_html_e( 'Gérer les pages', 'aihub-child-artistik' ); ?></a></p>
			</div>
			<div class="ak-help-card">
				<h2>4. <?php esc_html_e( 'Menu principal', 'aihub-child-artistik' ); ?></h2>
				<p><?php esc_html_e( 'Ajoutez ou réordonnez les entrées et sous-entrées via Apparence → Menus.', 'aihub-child-artistik' ); ?></p>
				<p><a class="button" href="<?php echo esc_url( $menu_url ); ?>"><?php esc_html_e( 'Gérer le menu', 'aihub-child-artistik' ); ?></a></p>
			</div>
			<div class="ak-help-card">
				<h2>5. <?php esc_html_e( 'Formulaire de contact &amp; emails', 'aihub-child-artistik' ); ?></h2>
				<p><?php esc_html_e( 'Le formulaire « Contact Artistik » envoie deux emails à chaque soumission : notification à info@artistik.cm + accusé de réception au visiteur. Modifiez les champs, le sujet ou les destinataires depuis Formidable.', 'aihub-child-artistik' ); ?></p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( $form_url ); ?>"><?php esc_html_e( 'Modifier le formulaire', 'aihub-child-artistik' ); ?></a>
					<a class="button" href="<?php echo esc_url( $entries_url ); ?>"><?php esc_html_e( 'Voir les messages reçus', 'aihub-child-artistik' ); ?></a>
					<a class="button" href="<?php echo esc_url( $smtp_url ); ?>"><?php esc_html_e( 'Réglages SMTP (GoSMTP)', 'aihub-child-artistik' ); ?></a>
				</p>
			</div>
			<div class="ak-help-card">
				<h2>6. <?php esc_html_e( 'Traduction FR / EN', 'aihub-child-artistik' ); ?></h2>
				<p><?php esc_html_e( 'Le site est multilingue grâce à TranslatePress. Cliquez sur « Éditeur de traduction » pour traduire chaque texte directement depuis la page (clic ⇒ saisie).', 'aihub-child-artistik' ); ?></p>
				<p>
					<a class="button button-primary" href="<?php echo esc_url( $trp_editor ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Ouvrir l’éditeur de traduction', 'aihub-child-artistik' ); ?></a>
					<a class="button" href="<?php echo esc_url( $trp_url ); ?>"><?php esc_html_e( 'Réglages TranslatePress', 'aihub-child-artistik' ); ?></a>
				</p>
			</div>
		</div>

		<h2><?php esc_html_e( 'Format des modules d’une solution', 'aihub-child-artistik' ); ?></h2>
		<p><?php esc_html_e( 'Dans le champ « Modules » de chaque solution, utilisez ce format simple :', 'aihub-child-artistik' ); ?></p>
		<pre>## Titre du module
- Élément 1
- Élément 2
- Élément 3

## Autre module
- Élément A
- Élément B</pre>

		<h2><?php esc_html_e( 'Performance & confidentialité', 'aihub-child-artistik' ); ?></h2>
		<p><?php esc_html_e( 'Tous les CSS et JavaScripts sont servis depuis ce serveur (aucun CDN externe), ce qui garantit la rapidité et la conformité (RGPD).', 'aihub-child-artistik' ); ?></p>
	</div>
	<?php
}

/* -------------------------------------------------------------
 * SVG inline (icônes / illustrations)
 * ------------------------------------------------------------- */

function artistik_icon( string $name ): string {
	static $icons = null;
	if ( $icons === null ) {
		$icons = array(
			/* Solutions / familles */
			'shield-plus' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6l8-3z"/><path d="M12 9v6"/><path d="M9 12h6"/></svg>',
			'graduation'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4 1 9l11 5 9-4.1V15"/><path d="M5 11v4c0 1.7 3.1 3 7 3s7-1.3 7-3v-4"/></svg>',
			'building'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 7h2M13 7h2M9 11h2M13 11h2M9 15h2M13 15h2"/><path d="M10 21v-3h4v3"/></svg>',
			'chart'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>',
			'briefcase'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M3 13h18"/></svg>',
			'spark'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M5.6 18.4l2.8-2.8M15.6 8.4l2.8-2.8"/></svg>',

			/* Communs */
			'arrow-right' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 5l7 7-7 7"/></svg>',
			'check'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg>',
			'globe'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/></svg>',

			/* Métier — santé */
			'stethoscope' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3v6a4 4 0 0 0 8 0V3"/><path d="M9 17a4 4 0 0 0 8 0v-2"/><circle cx="17" cy="13" r="2"/></svg>',
			'pill'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="8" width="20" height="8" rx="4" transform="rotate(-45 12 12)"/><path d="M9.2 14.8l5.6-5.6"/></svg>',
			'flask'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3h6M10 3v6L4 19a2 2 0 0 0 1.7 3h12.6A2 2 0 0 0 20 19l-6-10V3"/><path d="M7 14h10"/></svg>',
			'heart-pulse' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h3l2-3 3 6 2-4 2 2h6"/><path d="M21 12.5C21 16 17 19 12 22 7 19 3 16 3 12.5"/></svg>',
			'folder-medical' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M12 12v4M10 14h4"/></svg>',

			/* Métier — éducation */
			'book'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h11a4 4 0 0 1 4 4v12H8a4 4 0 0 1-4-4z"/><path d="M4 16a4 4 0 0 1 4-4h11"/></svg>',
			'clipboard'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="12" height="17" rx="2"/><path d="M9 4h6v3H9zM9 12h6M9 16h4"/></svg>',
			'message'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v12H8l-4 4z"/><path d="M8 9h8M8 13h5"/></svg>',
			'user-tie'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="7" r="3.5"/><path d="M5 21c1-4 4-6 7-6s6 2 7 6"/><path d="M11 12l1 4 1-4"/></svg>',
			'users'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2 21c1-3.5 4-5.5 7-5.5s6 2 7 5.5"/><circle cx="17" cy="9" r="2.5"/><path d="M15.5 14.2c2.6.3 4.6 2 5.5 5.3"/></svg>',

			/* Métier — immobilier */
			'home'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11l9-7 9 7v9a2 2 0 0 1-2 2h-4v-6h-6v6H5a2 2 0 0 1-2-2z"/></svg>',
			'key'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="14" r="4"/><path d="M11 11l9-9M16 6l3 3M14 8l3 3"/></svg>',
			'document'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M6 3h8l4 4v14a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1z"/><path d="M14 3v4h4M8 13h8M8 17h6"/></svg>',
			'receipt'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 3h14v18l-2.5-1.5L14 21l-2.5-1.5L9 21l-2.5-1.5L5 21z"/><path d="M8 8h8M8 12h8M8 16h5"/></svg>',
			'wallet'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="14" rx="2"/><path d="M3 10h18M16 15h2"/></svg>',
			'coin'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M9 8h5a2 2 0 0 1 0 4h-4a2 2 0 0 0 0 4h6"/><path d="M12 6v2M12 16v2"/></svg>',
			'shield'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3.5 8.5-8 9-4.5-.5-8-4-8-9V6l8-3z"/><path d="M9 12l2 2 4-4"/></svg>',
			'trending-down' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l6 6 4-4 8 8"/><path d="M21 17v-4h-4"/></svg>',

			/* Métier — commerce / POS */
			'cart'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4h2l2.5 12h11l2-8H7"/><circle cx="9" cy="20" r="1.5"/><circle cx="17" cy="20" r="1.5"/></svg>',
			'shop'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l1.5-5h15L21 9"/><path d="M4 9v11h16V9"/><path d="M3 9c0 2 2 3.5 4.5 3.5S12 11 12 9c0 2 2 3.5 4.5 3.5S21 11 21 9"/><path d="M9 20v-5h6v5"/></svg>',
			'box'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 7v10l9 4 9-4V7"/><path d="M12 11v10"/></svg>',
			'tag'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20.5 12.5L12 21 3 12V3h9z"/><circle cx="8" cy="8" r="1.5" fill="currentColor"/></svg>',
			'truck'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="13" height="10" rx="1.5"/><path d="M15 10h4l3 3v4h-7"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>',
			'barcode'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5v14M7 5v14M10 5v14M13 5v14M16 5v14M19 5v14"/></svg>',
			'percent'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="7" cy="7" r="2.5"/><circle cx="17" cy="17" r="2.5"/><path d="M19 5L5 19"/></svg>',

			/* Métier — agropastoral / élevage */
			'cow'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 11c0-3 3-5 7-5s7 2 7 5v4c0 3-3 5-7 5s-7-2-7-5z"/><path d="M5 9c-1.5-1-3-1-3 1s1.5 3 3 3"/><path d="M19 9c1.5-1 3-1 3 1s-1.5 3-3 3"/><circle cx="9.5" cy="13" r="1" fill="currentColor"/><circle cx="14.5" cy="13" r="1" fill="currentColor"/><path d="M11 17a2 2 0 0 0 2 0"/></svg>',
			'leaf'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M5 19c0-9 5-14 16-14 0 11-5 16-14 16-1 0-2 0-2-2z"/><path d="M5 19l9-9"/></svg>',
			'pin'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s7-7 7-13a7 7 0 0 0-14 0c0 6 7 13 7 13z"/><circle cx="12" cy="9" r="2.5"/></svg>',
			'route'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="19" r="2.5"/><circle cx="18" cy="5" r="2.5"/><path d="M8 19h6a4 4 0 0 0 0-8h-4a4 4 0 0 1 0-8h6"/></svg>',
			'syringe'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4l6 6"/><path d="M17 7l-9 9-3 3-2-2 3-3 9-9"/><path d="M11 13l3 3"/></svg>',
			'wheat'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22V8"/><path d="M12 8c-2-2-2-4 0-6 2 2 2 4 0 6z"/><path d="M12 12c-3-1-4-3-3-5 3 1 4 3 3 5z"/><path d="M12 12c3-1 4-3 3-5-3 1-4 3-3 5z"/><path d="M12 16c-3-1-4-3-3-5 3 1 4 3 3 5z"/><path d="M12 16c3-1 4-3 3-5-3 1-4 3-3 5z"/></svg>',

			/* Métier — dentaire */
			'tooth'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3c-2 0-3 2-3 4 0 4 1 5 2 9s1 7 3 7 1-4 3-4 1 4 3 4 2-3 3-7 2-5 2-9c0-2-1-4-3-4-3 0-3 1-5 1s-2-1-5-1z"/></svg>',
			'sparkles'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.5 4.5L18 9l-4.5 1.5L12 15l-1.5-4.5L6 9l4.5-1.5z"/><path d="M19 15l.7 2.1L22 18l-2.3.7L19 21l-.7-2.3L16 18l2.3-.9z"/></svg>',
			'calendar'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 9h18M8 3v4M16 3v4"/></svg>',
			'video'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="13" height="12" rx="2"/><path d="M16 10l5-3v10l-5-3z"/></svg>',
			'wrench'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4a5 5 0 0 0-1 9.8L4 22l2 2 8.2-9a5 5 0 0 0 5.6-7l-3 3-3-3z"/></svg>',
			'image'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M3 17l6-6 5 5 3-3 4 4"/></svg>',
			'megaphone'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 11v2l13 5V6z"/><path d="M16 9a3 3 0 0 1 0 6"/><path d="M7 13v3a2 2 0 0 0 4 0"/></svg>',
			'pill-clip'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="9" width="20" height="6" rx="3" transform="rotate(-30 12 12)"/><path d="M8.5 16.5l7-7"/></svg>',

			/* Métier — transport / flotte */
			'truck-2'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="6" width="13" height="11" rx="1.5"/><path d="M15 9h4l3 3v5h-7"/><circle cx="6" cy="18.5" r="2"/><circle cx="18" cy="18.5" r="2"/><path d="M2 13h13"/></svg>',
			'gauge'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 19a9 9 0 0 1 18 0"/><path d="M12 19l4-6"/><circle cx="12" cy="19" r="1.5" fill="currentColor"/></svg>',
			'fuel'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="11" height="18" rx="2"/><path d="M3 12h11"/><path d="M14 7l3 3v8a2 2 0 0 0 4 0v-9l-3-3"/></svg>',
			'tools'        => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a4 4 0 0 0 5 5l-9 9-5-5z"/><path d="M3 21l3-3"/></svg>',
			'map'          => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6l6-3 6 3 6-3v15l-6 3-6-3-6 3z"/><path d="M9 3v15M15 6v15"/></svg>',
			'route-pkg'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="19" r="2.5"/><circle cx="18" cy="5" r="2.5"/><path d="M8 19h6a4 4 0 0 0 0-8h-4a4 4 0 0 1 0-8h6"/></svg>',
			'shield-check' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l8 3v6c0 5-3 8-8 9-5-1-8-4-8-9V6z"/><path d="M9 12l2 2 4-4"/></svg>',
			'history'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/><path d="M12 8v5l3 2"/></svg>',
		);
	}
	return $icons[ $name ] ?? $icons['spark'];
}

/**
 * Détecte automatiquement une icône pertinente à partir du titre d'un module.
 */
function artistik_guess_module_icon( string $title ): string {
	$t = function_exists( 'remove_accents' ) ? remove_accents( $title ) : $title;
	$t = strtolower( $t );
	$rules = array(
		'check'           => array( 'rendez', 'admiss' ),
		'users'           => array( 'patient', 'eleve', 'etudiant', 'utilisateur', 'role', 'staff' ),
		'user-tie'        => array( 'professeur', 'enseignant', 'medecin', 'praticien', 'agent', 'bailleur', 'docteur', 'dentist', 'veterin', 'berger' ),
		'wallet'          => array( 'caisse', 'encaiss' ),
		'receipt'         => array( 'factur', 'recu', 'quittance', 'invoice' ),
		'shield'          => array( 'assur', 'convention', 'conformi' ),
		'coin'            => array( 'honoraire', 'salair', 'commission', 'paiem', 'payment' ),
		'trending-down'   => array( 'depense', 'arriere', 'impaye', 'expense' ),
		'pill'            => array( 'pharmac', 'medicament', 'prescription', 'medic' ),
		'flask'           => array( 'labo', 'examen', 'reactif', 'lab test' ),
		'folder-medical'  => array( 'dossier', 'fiche', 'records' ),
		'clipboard'       => array( 'note', 'evaluation', 'rapport', 'releve', 'reports', 'analyse', 'analyt' ),
		'message'         => array( 'sms', 'message', 'communication' ),
		'home'            => array( 'patrimoine', 'apparte', 'batim', 'logement', 'maison' ),
		'document'        => array( 'contrat', 'bail', 'piece', 'certific' ),
		'book'            => array( 'cours', 'matiere' ),
		/* Commerce / POS */
		'shop'            => array( 'magasin', 'business', 'multi-bus', 'storefront', 'boutique' ),
		'cart'            => array( 'vente', 'sell', 'pos', 'point de vente' ),
		'box'             => array( 'produit', 'stock', 'inventair', 'inventory', 'medicament', 'reception' ),
		'truck'           => array( 'achat', 'fournisseur', 'purchase', 'approvisi', 'livraison' ),
		'tag'             => array( 'prix', 'remise', 'promotion', 'discount' ),
		'barcode'         => array( 'barcode', 'code-barre', 'sku' ),
		'percent'         => array( 'taxe', 'tva', 'tax' ),
		/* Agropastoral / élevage */
		'cow'             => array( 'betail', 'cheptel', 'troupeau', 'animal', 'animaux', 'livestock' ),
		'leaf'            => array( 'aliment', 'pature', 'fourrag', 'food' ),
		'pin'             => array( 'geoloc', 'localis', 'position', 'gps', 'carte' ),
		'route'           => array( 'transhum', 'deplacem', 'route', 'parcours' ),
		'syringe'         => array( 'vaccin', 'soin', 'traitement' ),
		'wheat'           => array( 'production', 'recolte' ),
		/* Transport / flotte */
		'truck-2'         => array( 'transport', 'ordre de transport', 'flotte', 'fleet', 'véhicule', 'vehic', 'camion' ),
		'gauge'           => array( 'kilomet', 'kilomét', 'consommation', 'compteur' ),
		'fuel'            => array( 'carburant', 'plein', 'fuel', 'pneu' ),
		'tools'           => array( 'maintenance', 'révision', 'réparation', 'panne', 'corrective' ),
		'map'             => array( 'planning', 'réservation', 'itineraire', 'itinéraire' ),
		'route-pkg'       => array( 'sous-trait', 'partenaire', 'subcontract' ),
		'shield-check'    => array( 'securit', 'sécurit', 'profil', 'accès', 'authentif', 'login' ),
		'history'         => array( 'log', 'journalisat', 'traçabil', 'tracer' ),

		/* Dentaire */
		'tooth'           => array( 'dent', 'odonto', 'tooth' ),
		'sparkles'        => array( 'soin esthetique', 'blanchim' ),
		'calendar'        => array( 'rendez-vous', 'calendar', 'appointm', 'planning' ),
		'video'           => array( 'telemedic', 'visio', 'consultation distance' ),
		'wrench'          => array( 'maintenance', 'equipement', 'equipment' ),
		'image'           => array( 'imagerie', 'dicom', 'radio' ),
		'megaphone'       => array( 'marketing', 'campagne', 'campaign' ),
	);
	foreach ( $rules as $icon => $needles ) {
		foreach ( $needles as $needle ) {
			if ( str_contains( $t, $needle ) ) {
				return $icon;
			}
		}
	}
	return 'spark';
}

function artistik_hero_illustration(): string {
	return <<<SVG
<svg viewBox="0 0 520 360" role="img" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akGradA" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#0d9488" stop-opacity="0.85"/>
			<stop offset="1" stop-color="#1e293b" stop-opacity="0.85"/>
		</linearGradient>
		<linearGradient id="akGradB" x1="0" x2="1" y1="0" y2="0">
			<stop offset="0" stop-color="#5eead4"/>
			<stop offset="1" stop-color="#818cf8"/>
		</linearGradient>
	</defs>
	<rect x="40" y="40" rx="18" ry="18" width="440" height="280" fill="url(#akGradA)" stroke="#0f766e" stroke-opacity="0.45"/>
	<rect x="60" y="62" rx="6" ry="6" width="180" height="14" fill="#e2e8f0" opacity="0.7"/>
	<rect x="60" y="84" rx="6" ry="6" width="120" height="10" fill="#94a3b8" opacity="0.7"/>
	<rect x="60" y="120" rx="10" ry="10" width="190" height="100" fill="#0b1220" opacity="0.55"/>
	<rect x="270" y="120" rx="10" ry="10" width="190" height="48" fill="#0b1220" opacity="0.55"/>
	<rect x="270" y="172" rx="10" ry="10" width="190" height="48" fill="#0b1220" opacity="0.55"/>
	<polyline fill="none" stroke="url(#akGradB)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"
		points="74,200 100,180 126,190 152,150 178,168 204,140 230,156"/>
	<circle cx="230" cy="156" r="5" fill="#5eead4"/>
	<rect x="60" y="240" rx="8" ry="8" width="400" height="60" fill="#0b1220" opacity="0.55"/>
	<rect x="76" y="256" width="60" height="10" rx="3" fill="#5eead4"/>
	<rect x="76" y="272" width="160" height="8" rx="3" fill="#94a3b8" opacity="0.7"/>
	<rect x="320" y="256" width="120" height="28" rx="14" fill="#5eead4"/>
</svg>
SVG;
}

function artistik_solution_illustration( string $color ): string {
	$builders = array(
		'med'   => 'artistik_illu_med',
		'edu'   => 'artistik_illu_edu',
		're'    => 'artistik_illu_re',
		'com'   => 'artistik_illu_com',
		'agro'  => 'artistik_illu_agro',
		'dent'  => 'artistik_illu_dent',
		'trans' => 'artistik_illu_trans',
	);
	$fn = $builders[ $color ] ?? 'artistik_illu_med';
	return $fn();
}

/**
 * Illustration thématique « santé » (SoluMed) :
 * dashboard avec courbe ECG, badge croix, profil patient, KPI.
 */
function artistik_illu_med(): string {
	return <<<SVG
<svg viewBox="0 0 460 300" role="img" aria-label="Illustration santé" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akMedBg" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#0f766e"/>
			<stop offset="1" stop-color="#0b1220"/>
		</linearGradient>
		<linearGradient id="akMedBtn" x1="0" x2="1"><stop offset="0" stop-color="#5eead4"/><stop offset="1" stop-color="#14b8a6"/></linearGradient>
	</defs>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="url(#akMedBg)"/>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="none" stroke="#5eead4" stroke-opacity="0.25"/>

	<!-- Top bar -->
	<rect x="34" y="34" width="120" height="14" rx="4" fill="#e2e8f0" opacity="0.85"/>
	<rect x="34" y="56" width="70" height="8"  rx="3" fill="#94a3b8" opacity="0.7"/>
	<g transform="translate(390,32)">
		<circle cx="18" cy="18" r="18" fill="#5eead4" opacity="0.18"/>
		<g stroke="#5eead4" stroke-width="2" stroke-linecap="round" fill="none">
			<path d="M18 10v16M10 18h16"/>
		</g>
	</g>

	<!-- Patient card -->
	<rect x="34" y="84" width="170" height="90" rx="12" fill="#0b1220" opacity="0.55"/>
	<circle cx="62" cy="118" r="14" fill="#5eead4" opacity="0.55"/>
	<path d="M52 132c2-7 8-10 14-10s12 3 14 10" fill="none" stroke="#5eead4" stroke-width="2" stroke-linecap="round"/>
	<rect x="86" y="106" width="100" height="9" rx="3" fill="#cbd5e1" opacity="0.85"/>
	<rect x="86" y="120" width="70"  height="7" rx="3" fill="#94a3b8" opacity="0.7"/>
	<rect x="86" y="134" width="90"  height="7" rx="3" fill="#94a3b8" opacity="0.55"/>
	<rect x="46" y="150" width="50"  height="14" rx="7" fill="url(#akMedBtn)"/>
	<rect x="106" y="150" width="40" height="14" rx="7" fill="#0f172a" opacity="0.7" stroke="#5eead4" stroke-opacity="0.6"/>

	<!-- ECG -->
	<rect x="220" y="84" width="206" height="90" rx="12" fill="#0b1220" opacity="0.6"/>
	<text x="234" y="104" font-family="system-ui,sans-serif" font-size="11" fill="#94a3b8" font-weight="600">ECG · 78 bpm</text>
	<polyline fill="none" stroke="#5eead4" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"
		points="232,150 250,150 258,150 264,140 270,162 278,118 286,170 296,150 314,150 326,150 332,142 338,160 346,124 354,168 362,150 426,150"/>
	<circle cx="354" cy="168" r="3" fill="#fca5a5"/>

	<!-- KPI bottom -->
	<g transform="translate(34,190)">
		<rect width="124" height="76" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="26" font-family="system-ui" font-size="10" fill="#94a3b8" font-weight="700">RDV / jour</text>
		<text x="14" y="58" font-family="system-ui" font-size="28" font-weight="800" fill="#f8fafc">42</text>
		<g transform="translate(86,28)" stroke="#5eead4" stroke-width="2" fill="none" stroke-linecap="round">
			<path d="M0 16h6l3-8 4 14 3-6h6"/>
		</g>
	</g>
	<g transform="translate(168,190)">
		<rect width="124" height="76" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="26" font-family="system-ui" font-size="10" fill="#94a3b8" font-weight="700">Lits occupés</text>
		<text x="14" y="58" font-family="system-ui" font-size="28" font-weight="800" fill="#f8fafc">87%</text>
		<g transform="translate(86,52)">
			<rect width="24" height="6" rx="3" fill="#94a3b8" opacity="0.4"/>
			<rect width="20" height="6" rx="3" fill="#5eead4"/>
		</g>
	</g>
	<g transform="translate(302,190)">
		<rect width="124" height="76" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="26" font-family="system-ui" font-size="10" fill="#94a3b8" font-weight="700">Recettes</text>
		<text x="14" y="58" font-family="system-ui" font-size="22" font-weight="800" fill="#f8fafc">+18%</text>
		<g transform="translate(80,30)" stroke="#5eead4" stroke-width="2" fill="none" stroke-linecap="round">
			<path d="M0 24l8-8 8 4 12-14"/>
			<circle cx="28" cy="6" r="2.5" fill="#5eead4"/>
		</g>
	</g>
</svg>
SVG;
}

/**
 * Illustration thématique « éducation » (LyCol) :
 * dashboard scolaire avec relevé de notes, bulletin, livre, étudiants.
 */
function artistik_illu_edu(): string {
	return <<<SVG
<svg viewBox="0 0 460 300" role="img" aria-label="Illustration éducation" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akEduBg" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#4338ca"/>
			<stop offset="1" stop-color="#0b1220"/>
		</linearGradient>
		<linearGradient id="akEduBtn" x1="0" x2="1"><stop offset="0" stop-color="#a5b4fc"/><stop offset="1" stop-color="#818cf8"/></linearGradient>
	</defs>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="url(#akEduBg)"/>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="none" stroke="#a5b4fc" stroke-opacity="0.3"/>

	<rect x="34" y="34" width="140" height="14" rx="4" fill="#e2e8f0" opacity="0.85"/>
	<rect x="34" y="56" width="80" height="8"  rx="3" fill="#a5b4fc" opacity="0.6"/>

	<!-- Diplôme / chapeau -->
	<g transform="translate(376,30)" stroke="#a5b4fc" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
		<path d="M2 12 22 4l20 8-20 8z"/>
		<path d="M10 16v6c0 2 5 4 12 4s12-2 12-4v-6"/>
		<path d="M40 12v8"/>
	</g>

	<!-- Bulletin de notes -->
	<rect x="34" y="84" width="200" height="180" rx="14" fill="#f8fafc"/>
	<rect x="50" y="100" width="120" height="11" rx="3" fill="#312e81"/>
	<rect x="50" y="118" width="80" height="7" rx="3" fill="#94a3b8"/>
	<line x1="50" y1="138" x2="218" y2="138" stroke="#e5e7eb"/>
	<g font-family="system-ui,sans-serif" font-size="11" fill="#475569">
		<text x="50" y="156">Mathématiques</text><text x="186" y="156" fill="#312e81" font-weight="700">17/20</text>
		<text x="50" y="178">Physique</text>     <text x="186" y="178" fill="#312e81" font-weight="700">15/20</text>
		<text x="50" y="200">Français</text>     <text x="186" y="200" fill="#312e81" font-weight="700">14/20</text>
		<text x="50" y="222">Histoire</text>     <text x="186" y="222" fill="#312e81" font-weight="700">16/20</text>
	</g>
	<rect x="50" y="234" width="168" height="20" rx="6" fill="#eef2ff"/>
	<text x="60" y="248" font-family="system-ui" font-size="11" font-weight="700" fill="#4338ca">Moyenne générale</text>
	<text x="190" y="248" font-family="system-ui" font-size="12" font-weight="800" fill="#4338ca">15,5</text>

	<!-- KPI / charts -->
	<g transform="translate(252,84)">
		<rect width="174" height="56" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#a5b4fc" font-weight="700">Effectif total</text>
		<text x="14" y="44" font-family="system-ui" font-size="20" font-weight="800" fill="#f8fafc">1 247 élèves</text>
		<g transform="translate(118,16)" fill="#a5b4fc" opacity="0.85">
			<circle cx="6" cy="10" r="6"/><path d="M0 26c1-6 4-9 6-9s5 3 6 9z"/>
			<circle cx="22" cy="14" r="5"/><path d="M16 28c1-5 3-7 6-7s5 2 6 7z" opacity="0.7"/>
			<circle cx="38" cy="10" r="6"/><path d="M32 26c1-6 4-9 6-9s5 3 6 9z" opacity="0.55"/>
		</g>
	</g>
	<g transform="translate(252,148)">
		<rect width="174" height="116" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="24" font-family="system-ui" font-size="10" fill="#a5b4fc" font-weight="700">Encaissements</text>
		<text x="14" y="46" font-family="system-ui" font-size="18" font-weight="800" fill="#f8fafc">12,4 M</text>
		<!-- Bar chart -->
		<g transform="translate(14,58)">
			<rect x="0"  y="34" width="14" height="16" rx="2" fill="#a5b4fc" opacity="0.55"/>
			<rect x="22" y="22" width="14" height="28" rx="2" fill="#a5b4fc" opacity="0.7"/>
			<rect x="44" y="14" width="14" height="36" rx="2" fill="#a5b4fc"/>
			<rect x="66" y="6"  width="14" height="44" rx="2" fill="#818cf8"/>
			<rect x="88" y="20" width="14" height="30" rx="2" fill="#a5b4fc" opacity="0.7"/>
			<rect x="110" y="10" width="14" height="40" rx="2" fill="#818cf8"/>
			<rect x="132" y="2"  width="14" height="48" rx="2" fill="#5eead4"/>
		</g>
	</g>
</svg>
SVG;
}

/**
 * Illustration thématique « immobilier » (Simba) :
 * carte immeuble avec clés, contrat de bail, suivi loyers.
 */
function artistik_illu_re(): string {
	return <<<SVG
<svg viewBox="0 0 460 300" role="img" aria-label="Illustration immobilier" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akReBg" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#b45309"/>
			<stop offset="1" stop-color="#0b1220"/>
		</linearGradient>
		<linearGradient id="akReBtn" x1="0" x2="1"><stop offset="0" stop-color="#fcd34d"/><stop offset="1" stop-color="#f59e0b"/></linearGradient>
	</defs>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="url(#akReBg)"/>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="none" stroke="#fcd34d" stroke-opacity="0.3"/>

	<rect x="34" y="34" width="140" height="14" rx="4" fill="#fef3c7" opacity="0.9"/>
	<rect x="34" y="56" width="80" height="8" rx="3" fill="#fcd34d" opacity="0.6"/>

	<!-- Immeuble -->
	<g transform="translate(34,86)">
		<rect width="170" height="178" rx="14" fill="#0b1220" opacity="0.55"/>
		<g transform="translate(20,22)">
			<rect x="20" y="0" width="100" height="148" fill="#fef3c7" opacity="0.92" rx="6"/>
			<g fill="#b45309" opacity="0.9">
				<rect x="30" y="14" width="14" height="14" rx="2"/>
				<rect x="56" y="14" width="14" height="14" rx="2"/>
				<rect x="82" y="14" width="14" height="14" rx="2"/>
				<rect x="30" y="38" width="14" height="14" rx="2"/>
				<rect x="56" y="38" width="14" height="14" rx="2"/>
				<rect x="82" y="38" width="14" height="14" rx="2"/>
				<rect x="30" y="62" width="14" height="14" rx="2"/>
				<rect x="56" y="62" width="14" height="14" rx="2"/>
				<rect x="82" y="62" width="14" height="14" rx="2"/>
				<rect x="30" y="86" width="14" height="14" rx="2"/>
				<rect x="56" y="86" width="14" height="14" rx="2"/>
				<rect x="82" y="86" width="14" height="14" rx="2"/>
			</g>
			<rect x="56" y="116" width="28" height="32" rx="3" fill="#78350f"/>
			<circle cx="78" cy="132" r="1.7" fill="#fcd34d"/>
		</g>
		<!-- Tag -->
		<g transform="translate(108,12)">
			<rect width="50" height="22" rx="11" fill="url(#akReBtn)"/>
			<text x="25" y="16" text-anchor="middle" font-family="system-ui" font-size="10" font-weight="800" fill="#78350f">À LOUER</text>
		</g>
	</g>

	<!-- Contrat -->
	<g transform="translate(220,86)">
		<rect width="206" height="78" rx="12" fill="#f8fafc"/>
		<text x="16" y="22" font-family="system-ui" font-size="11" font-weight="800" fill="#78350f">Contrat de bail · #2026-014</text>
		<line x1="16" y1="32" x2="190" y2="32" stroke="#fde68a"/>
		<g font-family="system-ui,sans-serif" font-size="10" fill="#475569">
			<text x="16" y="48">Locataire</text><text x="100" y="48" font-weight="700" fill="#1d2327">M. Dupont</text>
			<text x="16" y="62">Loyer mensuel</text><text x="100" y="62" font-weight="700" fill="#1d2327">350 000 FCFA</text>
		</g>
		<g transform="translate(174,46)" stroke="#b45309" stroke-width="1.6" fill="none" stroke-linecap="round">
			<circle cx="6" cy="10" r="4"/>
			<path d="M9 11l9-9M14 5l3 3"/>
		</g>
	</g>

	<!-- Suivi loyers -->
	<g transform="translate(220,176)">
		<rect width="206" height="88" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="16" y="24" font-family="system-ui" font-size="10" font-weight="700" fill="#fcd34d">Suivi des loyers</text>
		<g font-family="system-ui,sans-serif" font-size="10" fill="#cbd5e1">
			<text x="16" y="44">Janv.</text>
			<rect x="60" y="36" width="120" height="8" rx="4" fill="#fcd34d" opacity="0.3"/>
			<rect x="60" y="36" width="116" height="8" rx="4" fill="#fcd34d"/>
			<text x="16" y="62">Févr.</text>
			<rect x="60" y="54" width="120" height="8" rx="4" fill="#fcd34d" opacity="0.3"/>
			<rect x="60" y="54" width="100" height="8" rx="4" fill="#fcd34d"/>
			<text x="16" y="80">Mars</text>
			<rect x="60" y="72" width="120" height="8" rx="4" fill="#fcd34d" opacity="0.3"/>
			<rect x="60" y="72" width="84" height="8" rx="4" fill="#f59e0b"/>
		</g>
	</g>
</svg>
SVG;
}

/**
 * Illustration thématique « commerce » (Boutik) :
 * dashboard caisse / POS avec ticket, panier, KPI ventes.
 */
function artistik_illu_com(): string {
	return <<<SVG
<svg viewBox="0 0 460 300" role="img" aria-label="Illustration commerce" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akComBg" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#0e7490"/>
			<stop offset="1" stop-color="#0b1220"/>
		</linearGradient>
		<linearGradient id="akComBtn" x1="0" x2="1"><stop offset="0" stop-color="#67e8f9"/><stop offset="1" stop-color="#06b6d4"/></linearGradient>
	</defs>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="url(#akComBg)"/>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="none" stroke="#67e8f9" stroke-opacity="0.3"/>

	<rect x="34" y="34" width="140" height="14" rx="4" fill="#e0f2fe" opacity="0.9"/>
	<rect x="34" y="56" width="80" height="8" rx="3" fill="#67e8f9" opacity="0.6"/>

	<!-- Caddie / panier -->
	<g transform="translate(390,32)" stroke="#67e8f9" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
		<path d="M0 4h6l4 18h22l4-12H10"/>
		<circle cx="14" cy="28" r="2.5" fill="#67e8f9"/>
		<circle cx="30" cy="28" r="2.5" fill="#67e8f9"/>
	</g>

	<!-- Ticket de caisse -->
	<g transform="translate(34,84)">
		<path d="M0 4 L150 4 L150 192 L140 184 L130 192 L120 184 L110 192 L100 184 L90 192 L80 184 L70 192 L60 184 L50 192 L40 184 L30 192 L20 184 L10 192 L0 184 Z" fill="#f8fafc"/>
		<text x="20" y="28" font-family="system-ui" font-size="11" font-weight="800" fill="#0e7490">SUPERMARCHÉ</text>
		<text x="20" y="44" font-family="system-ui" font-size="9" fill="#64748b">Reçu n° 0042 · 14:32</text>
		<line x1="14" y1="56" x2="136" y2="56" stroke="#cbd5e1" stroke-dasharray="2 2"/>
		<g font-family="system-ui" font-size="10" fill="#475569">
			<text x="14" y="74">Riz 5kg</text><text x="120" y="74" text-anchor="end" fill="#0f172a" font-weight="700">3 500</text>
			<text x="14" y="92">Huile 1L</text><text x="120" y="92" text-anchor="end" fill="#0f172a" font-weight="700">1 200</text>
			<text x="14" y="110">Sucre 1kg</text><text x="120" y="110" text-anchor="end" fill="#0f172a" font-weight="700">800</text>
			<text x="14" y="128">Lait UHT</text><text x="120" y="128" text-anchor="end" fill="#0f172a" font-weight="700">650</text>
		</g>
		<line x1="14" y1="138" x2="136" y2="138" stroke="#cbd5e1"/>
		<text x="14" y="158" font-family="system-ui" font-size="11" font-weight="800" fill="#0e7490">TOTAL</text>
		<text x="120" y="158" text-anchor="end" font-family="system-ui" font-size="13" font-weight="800" fill="#0e7490">6 150</text>
		<rect x="22" y="170" width="106" height="8" fill="#0e7490"/>
		<rect x="22" y="170" width="2" height="8" fill="#f8fafc"/>
		<rect x="28" y="170" width="3" height="8" fill="#f8fafc"/>
		<rect x="36" y="170" width="2" height="8" fill="#f8fafc"/>
		<rect x="44" y="170" width="4" height="8" fill="#f8fafc"/>
		<rect x="54" y="170" width="2" height="8" fill="#f8fafc"/>
	</g>

	<!-- KPIs -->
	<g transform="translate(204,84)">
		<rect width="222" height="56" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#67e8f9" font-weight="700">Ventes du jour</text>
		<text x="14" y="46" font-family="system-ui" font-size="20" font-weight="800" fill="#f8fafc">2,3 M FCFA</text>
		<g transform="translate(160,18)" fill="#67e8f9">
			<rect x="0"  y="20" width="6" height="14" rx="1"/>
			<rect x="10" y="14" width="6" height="20" rx="1"/>
			<rect x="20" y="6"  width="6" height="28" rx="1"/>
			<rect x="30" y="10" width="6" height="24" rx="1"/>
			<rect x="40" y="2"  width="6" height="32" rx="1"/>
		</g>
	</g>
	<g transform="translate(204,148)">
		<rect width="106" height="116" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#67e8f9" font-weight="700">Articles</text>
		<text x="14" y="50" font-family="system-ui" font-size="22" font-weight="800" fill="#f8fafc">487</text>
		<text x="14" y="74" font-family="system-ui" font-size="9" fill="#94a3b8">vendus aujourd'hui</text>
		<rect x="14" y="86" width="78" height="16" rx="8" fill="url(#akComBtn)"/>
		<text x="53" y="98" text-anchor="middle" font-family="system-ui" font-size="10" font-weight="800" fill="#0c4a6e">+18%</text>
	</g>
	<g transform="translate(320,148)">
		<rect width="106" height="116" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#67e8f9" font-weight="700">Stock alertes</text>
		<text x="14" y="50" font-family="system-ui" font-size="22" font-weight="800" fill="#fca5a5">12</text>
		<text x="14" y="74" font-family="system-ui" font-size="9" fill="#94a3b8">articles à réapprovisionner</text>
		<g transform="translate(14,86)" fill="none" stroke="#fca5a5" stroke-width="1.5">
			<rect x="0" y="0" width="80" height="6" rx="3" fill="#fca5a5" opacity="0.3"/>
			<rect x="0" y="0" width="22" height="6" rx="3" fill="#fca5a5"/>
		</g>
	</g>
</svg>
SVG;
}

/**
 * Illustration thématique « agropastoral » (Pastra) :
 * carte avec pâturage, géolocalisation troupeau, pin transhumance.
 */
function artistik_illu_agro(): string {
	return <<<SVG
<svg viewBox="0 0 460 300" role="img" aria-label="Illustration agropastoral" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akAgroBg" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#15803d"/>
			<stop offset="1" stop-color="#0b1220"/>
		</linearGradient>
		<linearGradient id="akAgroSky" x1="0" x2="0" y1="0" y2="1">
			<stop offset="0" stop-color="#bbf7d0" stop-opacity="0.7"/>
			<stop offset="1" stop-color="#86efac" stop-opacity="0.4"/>
		</linearGradient>
	</defs>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="url(#akAgroBg)"/>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="none" stroke="#86efac" stroke-opacity="0.35"/>

	<rect x="34" y="34" width="140" height="14" rx="4" fill="#dcfce7" opacity="0.9"/>
	<rect x="34" y="56" width="80" height="8" rx="3" fill="#86efac" opacity="0.6"/>

	<!-- Carte / pâturage -->
	<g transform="translate(34,86)">
		<rect width="240" height="178" rx="14" fill="#f0fdf4"/>
		<!-- Collines -->
		<path d="M0 130 Q40 100 80 120 T160 110 T240 130 L240 178 L0 178Z" fill="#bbf7d0"/>
		<path d="M0 150 Q60 130 120 150 T240 145 L240 178 L0 178Z" fill="#86efac"/>
		<!-- Soleil -->
		<circle cx="200" cy="36" r="14" fill="#fcd34d" opacity="0.85"/>
		<g stroke="#fcd34d" stroke-width="2" stroke-linecap="round">
			<path d="M200 14v-6M200 64v6M178 36h-6M222 36h6M184 20l-4-4M216 20l4-4M184 52l-4 4M216 52l4 4"/>
		</g>
		<!-- Animaux (bovins simplifiés) -->
		<g transform="translate(36,118)">
			<ellipse cx="0" cy="6" rx="10" ry="6" fill="#1e293b"/>
			<ellipse cx="-6" cy="3" rx="4" ry="3" fill="#1e293b"/>
			<line x1="-3" y1="11" x2="-3" y2="14" stroke="#1e293b" stroke-width="2"/>
			<line x1="3" y1="11" x2="3" y2="14" stroke="#1e293b" stroke-width="2"/>
		</g>
		<g transform="translate(72,128)">
			<ellipse cx="0" cy="6" rx="10" ry="6" fill="#1e293b"/>
			<ellipse cx="-6" cy="3" rx="4" ry="3" fill="#1e293b"/>
			<line x1="-3" y1="11" x2="-3" y2="14" stroke="#1e293b" stroke-width="2"/>
			<line x1="3" y1="11" x2="3" y2="14" stroke="#1e293b" stroke-width="2"/>
		</g>
		<g transform="translate(110,124)">
			<ellipse cx="0" cy="6" rx="10" ry="6" fill="#1e293b"/>
			<ellipse cx="-6" cy="3" rx="4" ry="3" fill="#1e293b"/>
			<line x1="-3" y1="11" x2="-3" y2="14" stroke="#1e293b" stroke-width="2"/>
			<line x1="3" y1="11" x2="3" y2="14" stroke="#1e293b" stroke-width="2"/>
		</g>
		<g transform="translate(150,134)">
			<ellipse cx="0" cy="6" rx="10" ry="6" fill="#1e293b"/>
			<ellipse cx="-6" cy="3" rx="4" ry="3" fill="#1e293b"/>
			<line x1="-3" y1="11" x2="-3" y2="14" stroke="#1e293b" stroke-width="2"/>
			<line x1="3" y1="11" x2="3" y2="14" stroke="#1e293b" stroke-width="2"/>
		</g>
		<!-- Trajectoire / route transhumance -->
		<path d="M40 158 Q90 142 130 152 T200 148" fill="none" stroke="#15803d" stroke-width="2" stroke-dasharray="4 3"/>
		<!-- Pin GPS -->
		<g transform="translate(196,138)">
			<path d="M0 0c-4 0-7 3-7 7 0 5 7 13 7 13s7-8 7-13c0-4-3-7-7-7z" fill="#dc2626"/>
			<circle cx="0" cy="7" r="2.5" fill="#fff"/>
		</g>
	</g>

	<!-- KPI : effectif -->
	<g transform="translate(284,86)">
		<rect width="142" height="56" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#86efac" font-weight="700">Effectif total</text>
		<text x="14" y="46" font-family="system-ui" font-size="20" font-weight="800" fill="#f8fafc">348 têtes</text>
		<g transform="translate(110,18)" stroke="#86efac" stroke-width="2" fill="none" stroke-linecap="round">
			<path d="M0 12c0-3 3-5 6-5s6 2 6 5"/>
			<circle cx="6" cy="3" r="3"/>
		</g>
	</g>
	<!-- KPI : production lait -->
	<g transform="translate(284,148)">
		<rect width="142" height="56" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#86efac" font-weight="700">Lait collecté (sem.)</text>
		<text x="14" y="46" font-family="system-ui" font-size="20" font-weight="800" fill="#f8fafc">412 L</text>
	</g>
	<!-- KPI : alertes -->
	<g transform="translate(284,210)">
		<rect width="142" height="54" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#fcd34d" font-weight="700">Alerte sortie de zone</text>
		<text x="14" y="44" font-family="system-ui" font-size="14" font-weight="800" fill="#fcd34d">2 troupeaux</text>
	</g>
</svg>
SVG;
}

/**
 * Illustration thématique « dentaire » (Smily) :
 * dashboard avec dent, planning rendez-vous, odontogramme schématique.
 */
function artistik_illu_dent(): string {
	return <<<SVG
<svg viewBox="0 0 460 300" role="img" aria-label="Illustration dentaire" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akDentBg" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#0369a1"/>
			<stop offset="1" stop-color="#0b1220"/>
		</linearGradient>
		<linearGradient id="akDentBtn" x1="0" x2="1"><stop offset="0" stop-color="#7dd3fc"/><stop offset="1" stop-color="#38bdf8"/></linearGradient>
	</defs>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="url(#akDentBg)"/>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="none" stroke="#7dd3fc" stroke-opacity="0.3"/>

	<rect x="34" y="34" width="140" height="14" rx="4" fill="#e0f2fe" opacity="0.9"/>
	<rect x="34" y="56" width="80" height="8" rx="3" fill="#7dd3fc" opacity="0.6"/>

	<!-- Grosse dent stylisée -->
	<g transform="translate(34,80)">
		<rect width="180" height="184" rx="14" fill="#0b1220" opacity="0.55"/>
		<g transform="translate(40,30)">
			<path d="M50 0c-10 0-15 6-22 6s-12-6-22-6c-10 0-14 8-14 18 0 14 4 18 8 32s4 32 14 32 4-18 14-18 4 18 14 18 8-12 12-32 8-18 8-32c0-10-4-18-12-18z" fill="#f8fafc"/>
			<path d="M30 50c5-3 10-3 15 0" stroke="#bae6fd" stroke-width="1.5" fill="none" stroke-linecap="round"/>
			<g transform="translate(72,8)">
				<circle r="11" fill="#bae6fd"/>
				<path d="M-4-3 l3 3 7-7" stroke="#0369a1" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round"/>
			</g>
		</g>
		<text x="90" y="170" text-anchor="middle" font-family="system-ui" font-size="11" font-weight="700" fill="#bae6fd">Diagnostic IA</text>
	</g>

	<!-- Planning RDV -->
	<g transform="translate(224,80)">
		<rect width="202" height="116" rx="12" fill="#f8fafc"/>
		<text x="14" y="22" font-family="system-ui" font-size="11" font-weight="800" fill="#0369a1">Rendez-vous du jour</text>
		<line x1="14" y1="30" x2="188" y2="30" stroke="#e2e8f0"/>
		<g font-family="system-ui" font-size="10" fill="#475569">
			<rect x="14" y="38" width="36" height="16" rx="4" fill="#bae6fd"/>
			<text x="32" y="50" text-anchor="middle" font-weight="800" fill="#0369a1">09:00</text>
			<text x="58" y="50" fill="#0f172a" font-weight="700">M. Kamga</text>
			<text x="58" y="64" fill="#94a3b8" font-size="9">Détartrage</text>

			<rect x="14" y="72" width="36" height="16" rx="4" fill="#fde68a"/>
			<text x="32" y="84" text-anchor="middle" font-weight="800" fill="#92400e">10:30</text>
			<text x="58" y="84" fill="#0f172a" font-weight="700">Mlle Tcham</text>
			<text x="58" y="98" fill="#94a3b8" font-size="9">Couronne</text>
		</g>
	</g>

	<!-- Odontogramme schématique -->
	<g transform="translate(224,204)">
		<rect width="202" height="60" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="20" font-family="system-ui" font-size="10" font-weight="700" fill="#7dd3fc">Odontogramme</text>
		<g transform="translate(14,28)">
			<g fill="#f8fafc">
				<rect x="0"   y="0" width="14" height="22" rx="3"/>
				<rect x="18"  y="0" width="14" height="22" rx="3"/>
				<rect x="36"  y="0" width="14" height="22" rx="3" fill="#fca5a5"/>
				<rect x="54"  y="0" width="14" height="22" rx="3"/>
				<rect x="72"  y="0" width="14" height="22" rx="3"/>
				<rect x="90"  y="0" width="14" height="22" rx="3" fill="#fcd34d"/>
				<rect x="108" y="0" width="14" height="22" rx="3"/>
				<rect x="126" y="0" width="14" height="22" rx="3"/>
				<rect x="144" y="0" width="14" height="22" rx="3"/>
				<rect x="162" y="0" width="14" height="22" rx="3" fill="#86efac"/>
			</g>
		</g>
	</g>
</svg>
SVG;
}

/**
 * Illustration thématique « transport / flotte » (Konvoi) :
 * dashboard avec carte/itinéraire, camion, KPIs flotte et planning.
 */
function artistik_illu_trans(): string {
	return <<<SVG
<svg viewBox="0 0 460 300" role="img" aria-label="Illustration transport et flotte" xmlns="http://www.w3.org/2000/svg">
	<defs>
		<linearGradient id="akTransBg" x1="0" x2="1" y1="0" y2="1">
			<stop offset="0" stop-color="#6d28d9"/>
			<stop offset="1" stop-color="#0b1220"/>
		</linearGradient>
		<linearGradient id="akTransBtn" x1="0" x2="1"><stop offset="0" stop-color="#c4b5fd"/><stop offset="1" stop-color="#8b5cf6"/></linearGradient>
	</defs>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="url(#akTransBg)"/>
	<rect x="14" y="14" width="432" height="272" rx="20" fill="none" stroke="#c4b5fd" stroke-opacity="0.3"/>

	<rect x="34" y="34" width="140" height="14" rx="4" fill="#ede9fe" opacity="0.9"/>
	<rect x="34" y="56" width="80" height="8" rx="3" fill="#c4b5fd" opacity="0.6"/>

	<!-- Carte / itinéraire -->
	<g transform="translate(34,86)">
		<rect width="260" height="178" rx="14" fill="#f5f3ff"/>
		<!-- Quadrillage de la carte -->
		<g stroke="#e9d5ff" stroke-width="0.7">
			<path d="M0 30h260M0 60h260M0 90h260M0 120h260M0 150h260"/>
			<path d="M40 0v178M80 0v178M120 0v178M160 0v178M200 0v178M240 0v178"/>
		</g>
		<!-- Itinéraire -->
		<path d="M30 150 Q70 120 110 130 T180 90 T230 50" fill="none" stroke="#7c3aed" stroke-width="3" stroke-linecap="round"/>
		<!-- Pin de départ -->
		<g transform="translate(30,150)">
			<path d="M0 -2c-4 0-7 3-7 7 0 5 7 13 7 13s7-8 7-13c0-4-3-7-7-7z" fill="#22c55e"/>
			<circle cx="0" cy="5" r="2.5" fill="#fff"/>
		</g>
		<!-- Pin d'arrivée -->
		<g transform="translate(230,50)">
			<path d="M0 -2c-4 0-7 3-7 7 0 5 7 13 7 13s7-8 7-13c0-4-3-7-7-7z" fill="#dc2626"/>
			<circle cx="0" cy="5" r="2.5" fill="#fff"/>
		</g>
		<!-- Camion en route -->
		<g transform="translate(120,118)">
			<rect x="-14" y="-8" width="20" height="14" rx="2" fill="#1e293b"/>
			<path d="M6 -4h6l4 4v6h-10z" fill="#1e293b"/>
			<circle cx="-7" cy="9" r="3" fill="#0b1220"/>
			<circle cx="-7" cy="9" r="1.5" fill="#c4b5fd"/>
			<circle cx="11" cy="9" r="3" fill="#0b1220"/>
			<circle cx="11" cy="9" r="1.5" fill="#c4b5fd"/>
		</g>
		<text x="14" y="22" font-family="system-ui" font-size="11" font-weight="800" fill="#7c3aed">OT-2026-0042</text>
		<text x="14" y="38" font-family="system-ui" font-size="9" fill="#64748b">Douala → Yaoundé · 245 km</text>
	</g>

	<!-- KPI flotte -->
	<g transform="translate(304,86)">
		<rect width="122" height="56" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#c4b5fd" font-weight="700">Véhicules</text>
		<text x="14" y="46" font-family="system-ui" font-size="20" font-weight="800" fill="#f8fafc">24</text>
		<g transform="translate(86,18)" stroke="#c4b5fd" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
			<rect x="0" y="6" width="14" height="10" rx="1"/>
			<path d="M14 9h4l4 3v4h-8"/>
			<circle cx="4" cy="18" r="1.6" fill="#c4b5fd"/>
			<circle cx="18" cy="18" r="1.6" fill="#c4b5fd"/>
		</g>
	</g>

	<g transform="translate(304,148)">
		<rect width="122" height="56" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#86efac" font-weight="700">En mission</text>
		<text x="14" y="46" font-family="system-ui" font-size="20" font-weight="800" fill="#86efac">17</text>
	</g>

	<!-- Alerte révision -->
	<g transform="translate(304,210)">
		<rect width="122" height="54" rx="12" fill="#0b1220" opacity="0.55"/>
		<text x="14" y="22" font-family="system-ui" font-size="10" fill="#fcd34d" font-weight="700">Révisions</text>
		<text x="14" y="44" font-family="system-ui" font-size="14" font-weight="800" fill="#fcd34d">3 véhicules</text>
		<g transform="translate(96,28)" stroke="#fcd34d" stroke-width="2" fill="none" stroke-linecap="round">
			<path d="M9 1L17 16H1z"/>
			<path d="M9 6v5"/>
			<circle cx="9" cy="13" r="0.8" fill="#fcd34d"/>
		</g>
	</g>
</svg>
SVG;
}

/* -------------------------------------------------------------
 * Renderer one-page (utilisé par template-artistik-onepage.php)
 * ------------------------------------------------------------- */

function artistik_render_onepage(): void {
	$eyebrow = artistik_opt( 'ak_hero_eyebrow', 'Artistik — logiciels métier' );
	$title   = artistik_opt( 'ak_hero_title', 'Des applications pensées pour votre secteur' );
	$lead    = artistik_opt( 'ak_hero_lead', 'Depuis 2006, Artistik conçoit des solutions fiables.' );
	$cta_lbl = artistik_opt( 'ak_hero_cta_label', 'Découvrir nos solutions' );
	$cta_url = artistik_opt( 'ak_hero_cta_url', '#solutions' );
	?>
	<section id="accueil" class="ak-hero">
		<div class="ak-container ak-hero-grid">
			<div class="ak-hero-text">
				<p class="ak-eyebrow ak-eyebrow--hero"><?php echo esc_html( $eyebrow ); ?></p>
				<h1><?php echo wp_kses_post( $title ); ?></h1>
				<p class="ak-lead"><?php echo wp_kses_post( $lead ); ?></p>
				<div class="ak-hero-actions">
					<a class="ak-btn ak-btn--primary" href="<?php echo esc_url( $cta_url ); ?>">
						<?php echo esc_html( $cta_lbl ); ?>
						<span class="ak-icon ak-icon--inline" aria-hidden="true"><?php echo artistik_icon( 'arrow-right' ); ?></span>
					</a>
					<a class="ak-btn ak-btn--ghost" href="#contact"><?php esc_html_e( 'Nous contacter', 'aihub-child-artistik' ); ?></a>
				</div>
				<div class="ak-stats" role="list">
					<?php for ( $i = 1; $i <= 3; $i++ ) :
						$value = artistik_opt( "ak_stat_{$i}_value", '' );
						$label = artistik_opt( "ak_stat_{$i}_label", '' );
						if ( $value === '' && $label === '' ) {
							continue;
						}
						?>
						<div class="ak-stat" role="listitem">
							<strong><?php echo esc_html( $value ); ?></strong>
							<span><?php echo esc_html( $label ); ?></span>
						</div>
					<?php endfor; ?>
				</div>
			</div>
			<div class="ak-hero-art" aria-hidden="true">
				<?php echo artistik_hero_illustration(); ?>
			</div>
		</div>
	</section>

	<?php
	$solutions = artistik_get_solutions();
	?>

	<section id="solutions" class="ak-section">
		<div class="ak-container">
			<div class="ak-section-head">
				<p class="ak-eyebrow"><?php esc_html_e( 'Nos solutions logicielles', 'aihub-child-artistik' ); ?></p>
				<h2><?php esc_html_e( 'Des outils métier pensés pour votre activité', 'aihub-child-artistik' ); ?></h2>
				<p><?php esc_html_e( 'Chaque application regroupe des modules ciblés pour couvrir vos processus du quotidien — administratif, finance, utilisateurs et reporting.', 'aihub-child-artistik' ); ?></p>
			</div>
			<div class="ak-grid ak-grid--solutions">
				<?php foreach ( $solutions as $sol ) :
					$anchor = (string) get_post_meta( $sol->ID, '_ak_anchor', true );
					if ( $anchor === '' ) { $anchor = $sol->post_name; }
					$color  = (string) get_post_meta( $sol->ID, '_ak_color', true ) ?: 'med';
					$badge  = (string) get_post_meta( $sol->ID, '_ak_badge', true );
					$icon   = (string) get_post_meta( $sol->ID, '_ak_icon', true ) ?: 'spark';
					$sub    = (string) get_post_meta( $sol->ID, '_ak_subtitle', true );
					?>
					<a class="ak-tile ak-tile--<?php echo esc_attr( $color ); ?>" href="#<?php echo esc_attr( $anchor ); ?>">
						<span class="ak-tile-icon" aria-hidden="true"><?php echo artistik_icon( $icon ); ?></span>
						<span class="ak-tile-body">
							<span class="ak-tile-badge"><?php echo esc_html( $badge ); ?></span>
							<strong><?php echo esc_html( get_the_title( $sol ) ); ?></strong>
							<small><?php echo esc_html( $sub ); ?></small>
						</span>
						<span class="ak-tile-arrow" aria-hidden="true"><?php echo artistik_icon( 'arrow-right' ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php $i = 0; foreach ( $solutions as $sol ) :
		$i++;
		$anchor = (string) get_post_meta( $sol->ID, '_ak_anchor', true );
		if ( $anchor === '' ) { $anchor = $sol->post_name; }
		$color   = (string) get_post_meta( $sol->ID, '_ak_color', true ) ?: 'med';
		$badge   = (string) get_post_meta( $sol->ID, '_ak_badge', true );
		$icon    = (string) get_post_meta( $sol->ID, '_ak_icon', true ) ?: 'spark';
		$sub     = (string) get_post_meta( $sol->ID, '_ak_subtitle', true );
		$intro   = apply_filters( 'the_content', $sol->post_content );
		$modules = artistik_parse_modules( (string) get_post_meta( $sol->ID, '_ak_modules', true ) );
		$alt     = ( $i % 2 === 1 );
		?>
		<section id="<?php echo esc_attr( $anchor ); ?>" class="ak-section <?php echo $alt ? 'ak-section--alt' : ''; ?>">
			<div class="ak-container">
				<article class="ak-product ak-product--<?php echo esc_attr( $color ); ?>">
					<div class="ak-product-header">
						<div class="ak-product-meta">
							<span class="ak-product-icon" aria-hidden="true"><?php echo artistik_icon( $icon ); ?></span>
							<span class="ak-product-badge"><?php echo esc_html( $badge ); ?></span>
						</div>
						<h3><?php echo esc_html( get_the_title( $sol ) ); ?></h3>
						<p class="ak-sub"><?php echo esc_html( $sub ); ?></p>
					</div>
					<div class="ak-product-body">
						<div class="ak-product-intro"><?php echo wp_kses_post( $intro ); ?></div>
						<div class="ak-product-art" aria-hidden="true"><?php echo artistik_solution_illustration( $color ); ?></div>
					</div>
					<?php if ( ! empty( $modules ) ) : ?>
						<div class="ak-modules">
							<?php foreach ( $modules as $idx => $mod ) :
								$mod_anchor = $anchor . '-' . sanitize_title( $mod['title'] ?: ('m' . $idx) );
								$mod_icon   = artistik_guess_module_icon( (string) $mod['title'] );
								?>
								<div class="ak-module" id="<?php echo esc_attr( $mod_anchor ); ?>">
									<div class="ak-module-head">
										<span class="ak-module-icon" aria-hidden="true"><?php echo artistik_icon( $mod_icon ); ?></span>
										<h4><?php echo esc_html( $mod['title'] ); ?></h4>
									</div>
									<ul>
										<?php foreach ( $mod['items'] as $item ) : ?>
											<li><span class="ak-bullet" aria-hidden="true"><?php echo artistik_icon( 'check' ); ?></span><?php echo esc_html( $item ); ?></li>
										<?php endforeach; ?>
									</ul>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</article>
			</div>
		</section>
	<?php endforeach; ?>

	<?php
	$contact_form_id = (int) get_option( 'ak_contact_form_id', 0 );
	$url             = artistik_opt( 'ak_contact_url', '' );
	$email           = artistik_opt( 'ak_contact_email', '' );
	?>
	<section id="contact" class="ak-section ak-section--alt">
		<div class="ak-container ak-contact">
			<div class="ak-contact-info">
				<p class="ak-eyebrow"><?php esc_html_e( 'Contact', 'aihub-child-artistik' ); ?></p>
				<h2><?php echo esc_html( artistik_opt( 'ak_contact_title', 'Un projet ? Parlons-en.' ) ); ?></h2>
				<p class="ak-contact-lead"><?php echo esc_html( artistik_opt( 'ak_contact_text', 'Parlons de votre besoin.' ) ); ?></p>

				<ul class="ak-contact-list">
					<?php if ( $email ) : ?>
						<li>
							<span class="ak-contact-ico" aria-hidden="true"><?php echo artistik_icon( 'message' ); ?></span>
							<div>
								<strong><?php esc_html_e( 'Email', 'aihub-child-artistik' ); ?></strong>
								<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>
							</div>
						</li>
					<?php endif; ?>
					<?php if ( $url ) : ?>
						<li>
							<span class="ak-contact-ico" aria-hidden="true"><?php echo artistik_icon( 'globe' ); ?></span>
							<div>
								<strong><?php esc_html_e( 'Site web', 'aihub-child-artistik' ); ?></strong>
								<a href="<?php echo esc_url( $url ); ?>" rel="noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', $url ) ); ?></a>
							</div>
						</li>
					<?php endif; ?>
					<li>
						<span class="ak-contact-ico" aria-hidden="true"><?php echo artistik_icon( 'check' ); ?></span>
						<div>
							<strong><?php esc_html_e( 'Réponse sous 24 h', 'aihub-child-artistik' ); ?></strong>
							<span><?php esc_html_e( 'Jours ouvrés — accusé de réception immédiat.', 'aihub-child-artistik' ); ?></span>
						</div>
					</li>
				</ul>
			</div>

			<div class="ak-contact-form">
				<?php if ( $contact_form_id > 0 && shortcode_exists( 'formidable' ) ) : ?>
					<?php echo do_shortcode( '[formidable id="' . (int) $contact_form_id . '"]' ); ?>
				<?php else : ?>
					<p class="ak-muted">
						<?php esc_html_e( 'Le formulaire de contact n’est pas encore configuré. Écrivez-nous à', 'aihub-child-artistik' ); ?>
						<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a>.
					</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<?php
}

/* -------------------------------------------------------------
 * Walker minimal pour le menu (icône caret sous-menus)
 * ------------------------------------------------------------- */

function artistik_register_menus(): void {
	register_nav_menus( array( 'primary' => __( 'Menu principal', 'aihub-child-artistik' ) ) );
}
add_action( 'after_setup_theme', 'artistik_register_menus' );

/* -------------------------------------------------------------
 * Sélecteur de langue maison (FR / EN) — sans dépendance JS
 * Lit la configuration TranslatePress et génère 2 boutons inline.
 * ------------------------------------------------------------- */

/**
 * Retourne l'URL canonique du site (sans préfixe de langue), peu importe la
 * langue actuellement servie. Fait dériver l'URL à partir du host courant pour
 * éviter tout filtrage de TranslatePress sur `home_url`.
 */
function artistik_canonical_base_url(): string {
	$settings = get_option( 'trp_settings', array() );
	$slugs    = isset( $settings['url-slugs'] ) && is_array( $settings['url-slugs'] ) ? $settings['url-slugs'] : array();
	$default  = isset( $settings['default-language'] ) ? (string) $settings['default-language'] : 'fr_FR';

	$home = (string) home_url( '/' );

	foreach ( $slugs as $code => $slug ) {
		if ( $code === $default || empty( $slug ) ) { continue; }
		$home = preg_replace( '#/' . preg_quote( (string) $slug, '#' ) . '/?$#', '/', $home );
	}
	return trailingslashit( $home );
}

/**
 * Retourne le tableau des langues publiées sous forme :
 *   ['fr_FR' => ['code','slug','native','url','flag'], …]
 */
function artistik_get_languages(): array {
	$settings = get_option( 'trp_settings', array() );
	if ( ! is_array( $settings ) ) { $settings = array(); }
	$publish  = isset( $settings['publish-languages'] ) && is_array( $settings['publish-languages'] )
		? $settings['publish-languages']
		: array( 'fr_FR', 'en_US' );
	$slugs    = isset( $settings['url-slugs'] ) && is_array( $settings['url-slugs'] )
		? $settings['url-slugs']
		: array( 'fr_FR' => 'fr', 'en_US' => 'en' );
	$default  = isset( $settings['default-language'] ) ? (string) $settings['default-language'] : 'fr_FR';

	$names = array(
		'fr_FR' => 'Français',
		'en_US' => 'English',
		'es_ES' => 'Español',
		'de_DE' => 'Deutsch',
		'it_IT' => 'Italiano',
		'pt_PT' => 'Português',
		'ar'    => 'العربية',
	);

	$flags_dir = WP_PLUGIN_URL . '/translatepress-multilingual/assets/flags/4x3/';
	$base      = artistik_canonical_base_url();

	$out = array();
	foreach ( $publish as $code ) {
		$slug = isset( $slugs[ $code ] ) ? (string) $slugs[ $code ] : strtolower( substr( $code, 0, 2 ) );
		$url  = $base;
		if ( $code !== $default ) {
			$url = $base . $slug . '/';
		}
		$out[ $code ] = array(
			'code'   => $code,
			'slug'   => $slug,
			'native' => $names[ $code ] ?? $code,
			'flag'   => $flags_dir . $code . '.svg',
			'url'    => $url,
		);
	}
	return $out;
}

/**
 * Détecte la langue actuellement affichée à partir de l'URL (slug /en/).
 */
function artistik_current_language(): string {
	$settings = get_option( 'trp_settings', array() );
	$default  = isset( $settings['default-language'] ) ? (string) $settings['default-language'] : 'fr_FR';
	$slugs    = isset( $settings['url-slugs'] ) && is_array( $settings['url-slugs'] ) ? $settings['url-slugs'] : array();

	$req = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
	$req = '/' . trim( wp_parse_url( $req, PHP_URL_PATH ) ?: '', '/' ) . '/';
	foreach ( $slugs as $code => $slug ) {
		if ( $code === $default ) { continue; }
		if ( strpos( $req, '/' . $slug . '/' ) !== false ) {
			return (string) $code;
		}
	}
	return $default;
}

/**
 * Affiche le sélecteur (à appeler depuis header.php).
 * Le wrapper utilise data-no-translation et data-trp-link-no-replace pour
 * empêcher TranslatePress de réécrire les URLs des langues alternatives.
 */
function artistik_lang_switcher_html(): string {
	$langs = artistik_get_languages();
	if ( count( $langs ) < 2 ) { return ''; }
	$current = artistik_current_language();
	$out = '<div class="ak-lang" role="group" aria-label="' . esc_attr__( 'Sélecteur de langue', 'aihub-child-artistik' ) . '" data-no-translation>';
	foreach ( $langs as $code => $info ) {
		$active = ( $code === $current );
		$out .= sprintf(
			'<a class="ak-lang-item%1$s" href="%2$s" hreflang="%3$s" lang="%3$s" title="%4$s" data-no-translation data-trp-link-no-replace="true"%5$s>'
			. '<img class="ak-lang-flag" src="%6$s" alt="" width="20" height="14" loading="lazy" />'
			. '<span class="ak-lang-name" data-no-translation>%4$s</span>'
			. '</a>',
			$active ? ' is-active" aria-current="true' : '',
			esc_url( $info['url'] ),
			esc_attr( str_replace( '_', '-', $code ) ),
			esc_html( $info['native'] ),
			$active ? ' aria-label="' . esc_attr( sprintf( /* translators: %s: language name */ __( 'Langue actuelle : %s', 'aihub-child-artistik' ), $info['native'] ) ) . '"' : '',
			esc_url( $info['flag'] )
		);
	}
	$out .= '</div>';
	return $out;
}

/**
 * Garde-fou : empêche TranslatePress de toucher aux URLs des items du sélecteur.
 * On filtre la liste des liens sortants juste avant la réécriture.
 */
add_filter( 'trp_skip_url', function ( $skip, $url ) {
	if ( ! is_string( $url ) ) { return $skip; }
	$settings = get_option( 'trp_settings', array() );
	$slugs    = isset( $settings['url-slugs'] ) && is_array( $settings['url-slugs'] ) ? $settings['url-slugs'] : array();
	$default  = isset( $settings['default-language'] ) ? (string) $settings['default-language'] : 'fr_FR';
	foreach ( $slugs as $code => $slug ) {
		if ( $code === $default || ! $slug ) { continue; }
		if ( preg_match( '#/' . preg_quote( $slug, '#' ) . '/?$#', $url ) ) {
			return true;
		}
	}
	return $skip;
}, 10, 2 );

/* -------------------------------------------------------------
 * SMTP : ajustements PHPMailer (HELO valide, debug optionnel)
 * Certains serveurs SMTP refusent les HELO non-FQDN (ex. 0.0.0.0,
 * conteneurs Docker). On force un HELO conforme à RFC 2821 §4.1.1.1.
 * ------------------------------------------------------------- */
add_action( 'phpmailer_init', function ( $phpmailer ) {
	$helo_default = 'artistik.cm';
	$helo         = (string) get_option( 'ak_smtp_helo', $helo_default );
	if ( $helo !== '' ) {
		$phpmailer->Helo     = $helo;
		$phpmailer->Hostname = $helo;
	}
	$reply_to = (string) get_option( 'ak_smtp_reply_to', 'info@artistik.cm' );
	if ( $reply_to !== '' && method_exists( $phpmailer, 'addReplyTo' ) ) {
		$phpmailer->addReplyTo( $reply_to, get_bloginfo( 'name' ) );
	}
}, 99 );
