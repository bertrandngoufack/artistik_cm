<?php
/**
 * Active AIHub + thème enfant Artistik, crée le CPT « Solution »,
 * seed SoluMed / LyCol / Simba, prépare la page d’accueil et le menu.
 *
 * Lancer :
 *   wp eval-file scripts/wordpress-aihub-onepage-setup.php --path=/var/www/html/artistik_cm --allow-root
 *
 * @package Artistik_CM
 */

if ( ! function_exists( 'switch_theme' ) ) {
	fwrite( STDERR, "Utiliser : wp eval-file\n" );
	exit( 1 );
}

$child  = 'aihub-child';
$parent = 'aihub';

if ( ! wp_get_theme( $parent )->exists() ) {
	echo "Thème parent AIHub introuvable.\n";
	exit( 1 );
}
if ( ! wp_get_theme( $child )->exists() ) {
	echo "Thème enfant $child introuvable.\n";
	exit( 1 );
}

switch_theme( $child );

if ( function_exists( 'activate_plugin' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	foreach ( array( 'elementor/elementor.php', 'elementor-pro/elementor-pro.php' ) as $plugin ) {
		$path = WP_PLUGIN_DIR . '/' . $plugin;
		if ( file_exists( $path ) && is_plugin_inactive( $plugin ) ) {
			activate_plugin( $plugin, '', false, false );
		}
	}
}

/* On force l'enregistrement du CPT défini dans le thème enfant. */
if ( function_exists( 'artistik_register_solution_cpt' ) ) {
	artistik_register_solution_cpt();
} else {
	echo "Le thème enfant Artistik ne semble pas chargé.\n";
	exit( 1 );
}

/* ---------------------------------------------------------------
 * Customizer : valeurs par défaut
 * --------------------------------------------------------------- */
$defaults = array(
	'ak_hero_eyebrow'   => 'Artistik — logiciels métier',
	'ak_hero_title'     => 'Des applications pensées pour votre secteur',
	'ak_hero_lead'      => 'Depuis <strong>2006</strong>, Artistik conçoit des solutions fiables pour la <strong>santé</strong>, l’<strong>éducation</strong> et l’<strong>immobilier</strong>. SoluMed, LyCol et Simba — des outils complets, évolutifs et adaptés à votre organisation.',
	'ak_hero_cta_label' => 'Découvrir nos solutions',
	'ak_hero_cta_url'   => '#solutions',

	'ak_stat_1_value' => '2006',
	'ak_stat_1_label' => 'Année de création de SoluMed',
	'ak_stat_2_value' => '3',
	'ak_stat_2_label' => 'familles de produits',
	'ak_stat_3_value' => '100%',
	'ak_stat_3_label' => 'orienté gestion métier',

	'ak_contact_title' => 'Un projet ? Parlons-en.',
	'ak_contact_text'  => 'Déploiement, démonstration ou accompagnement : l’équipe Artistik vous répond.',
	'ak_contact_email' => 'info@artistik.cm',
	'ak_contact_url'   => 'https://artistik.cm',
);
foreach ( $defaults as $key => $value ) {
	if ( get_option( $key, null ) === null || get_option( $key, '' ) === '' ) {
		update_option( $key, $value );
	}
}

/* ---------------------------------------------------------------
 * Seed CPT « Solution »
 * --------------------------------------------------------------- */
$seed_solutions = array(
	array(
		'slug'     => 'solumed',
		'title'    => 'SoluMed',
		'badge'    => 'Santé',
		'subtitle' => 'Logiciel de gestion des cliniques, hôpitaux et polycliniques',
		'color'    => 'med',
		'icon'     => 'shield-plus',
		'order'    => 10,
		'content'  => '<p>Dans un monde technologique en perpétuelle évolution, il est primordial de disposer d’un <strong>système informatique performant</strong>, pour travailler plus rapidement, efficacement, et obtenir des résultats fiables. C’est dans ce cadre que la société Artistik a créé depuis <strong>2006</strong> <strong>SoluMed</strong> : un véritable outil de gestion de votre établissement médical (clinique, centre de santé, groupe médical…). SoluMed s’appuie sur de nombreux modules complémentaires.</p>',
		'modules'  => "## Patients (assurés ou non)\n- Ouverture de dossier\n- Admission\n- Réadmission\n- Hospitalisations\n- File d’attente\n- Rendez-vous\n\n## Caisse\n- Encaissement patient\n- Reçus de caisse (A4 / ticket)\n- Édition de point de caisse\n\n## Assurances\n- Paramétrage des conventions\n- Recouvrement des factures\n\n## Facturation\n- Facture de consultation\n- Facture d’hospitalisation\n- Pro-formas\n- Recouvrement assurances\n\n## Dépenses\n- Saisie des dépenses\n- Situation de trésorerie\n\n## Honoraires\n- Honoraires par médecin\n- Édition par praticien\n- Ristournes\n\n## Pharmacie\n- Approvisionnements\n- Consommations par patient\n- État de stock\n- Inventaire\n- Bons de commande\n\n## Laboratoire\n- Résultats d’examens\n- Réactifs\n- État financier du labo\n\n## Dossier médical\n- Saisie ou scan des dossiers\n- Recherche et édition des dossiers patients",
	),
	array(
		'slug'     => 'lycol',
		'title'    => 'LyCol',
		'badge'    => 'Éducation',
		'subtitle' => 'Logiciel de gestion pour écoles et universités',
		'color'    => 'edu',
		'icon'     => 'graduation',
		'order'    => 20,
		'content'  => '<p><strong>LyCol</strong> est développée par Artistik CM : gestion scolaire et universitaire pour le primaire, le secondaire, les grandes écoles et les universités. C’est un espace collaboratif pour l’établissement, les enseignants, les parents, les élèves et étudiants. Application <strong>web et mobile</strong>, avec des espaces dédiés aux managers et fondateurs.</p>',
		'modules'  => "## Élèves & étudiants\n- Identification\n- Admissions / Réadmissions\n- Inscriptions\n- Listes de classe et indicateurs\n- Attestations, certificats, badges PVC\n- Emploi du temps\n- SMS\n\n## Caisse\n- Encaissements\n- Reçus avec photo\n- Points de caisse\n- Trésorerie générale & individuelle\n- Relances scolarité\n- Tableau de bord\n- Frais annexes / facture État\n- Échéanciers / biométrie / engagement parental\n\n## Professeurs\n- Identification\n- Saisie des vacations\n- Relevés par enseignant\n- États globaux\n- Listes détaillées\n\n## Notes\n- Saisie & mise à jour des notes\n- Relevés semestriels et trimestriels\n- Relevés élève / étudiant\n- Matrice des moyennes / LMD\n- Statistiques / éditeur de rapport\n\n## Envoi de SMS\n- Informations générales\n- Absences\n- Rappels de paiement\n- Notes / Moyennes",
	),
	array(
		'slug'     => 'simba',
		'title'    => 'Simba',
		'badge'    => 'Immobilier',
		'subtitle' => 'Logiciel de gestion locative (loyers)',
		'color'    => 're',
		'icon'     => 'building',
		'order'    => 30,
		'content'  => '<p>Gérer plusieurs biens demande rigueur : impayés, relances, quittances. <strong>Simba</strong> automatise ces opérations avec une interface <strong>simple et efficace</strong>, tout en offrant des fonctions avancées. Quelques clics suffisent pour structurer votre gestion locative.</p>',
		'modules'  => "## Patrimoine & acteurs\n- Appartements\n- Bailleurs\n- Locataires\n- Agents immobiliers\n\n## Contrats & flux financiers\n- Contrat de bail\n- Cautions et frais\n- Loyers payés\n- Mise à jour des arriérés\n- Comptabilité (recettes / dépenses)\n\n## Éditions & pilotage\n- Liste des locataires\n- Liste par bâtiments\n- Reçu de paiement\n- Point de caisse\n- Relevé par locataire\n- État de paiement par bâtiment\n- État des arriérés",
	),
);

$created  = 0;
$updated  = 0;
$sol_meta = array();
foreach ( $seed_solutions as $row ) {
	$existing = get_page_by_path( $row['slug'], OBJECT, 'ak_solution' );
	$post_id  = $existing ? (int) $existing->ID : 0;
	$args     = array(
		'post_type'    => 'ak_solution',
		'post_status'  => 'publish',
		'post_title'   => $row['title'],
		'post_name'    => $row['slug'],
		'menu_order'   => (int) $row['order'],
		'post_content' => $row['content'],
	);
	if ( $post_id > 0 ) {
		$args['ID'] = $post_id;
		wp_update_post( $args );
		$updated++;
	} else {
		$post_id = (int) wp_insert_post( $args );
		$created++;
	}
	if ( $post_id > 0 ) {
		update_post_meta( $post_id, '_ak_anchor', $row['slug'] );
		update_post_meta( $post_id, '_ak_badge', $row['badge'] );
		update_post_meta( $post_id, '_ak_subtitle', $row['subtitle'] );
		update_post_meta( $post_id, '_ak_color', $row['color'] );
		update_post_meta( $post_id, '_ak_icon', $row['icon'] );
		/* Ne pas écraser les modules s'ils ont été personnalisés (taille >= seed). */
		$current = (string) get_post_meta( $post_id, '_ak_modules', true );
		if ( $current === '' ) {
			update_post_meta( $post_id, '_ak_modules', $row['modules'] );
		}
		$sol_meta[] = array(
			'title'  => $row['title'],
			'anchor' => $row['slug'],
			'modules'=> artistik_parse_modules( (string) get_post_meta( $post_id, '_ak_modules', true ) ),
		);
	}
}

/* ---------------------------------------------------------------
 * Page d’accueil one-page
 * --------------------------------------------------------------- */
$front_id = (int) get_option( 'page_on_front' );
if ( $front_id <= 0 ) {
	$home = get_page_by_path( 'accueil', OBJECT, 'page' );
	if ( $home instanceof WP_Post ) {
		$front_id = (int) $home->ID;
	}
}
if ( $front_id <= 0 ) {
	$front_id = (int) wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => 'Accueil',
			'post_name'    => 'accueil',
			'post_content' => '',
		)
	);
} else {
	/* On vide le contenu : tout est désormais piloté par le CPT « Solution » et le Customizer. */
	wp_update_post(
		array(
			'ID'           => $front_id,
			'post_title'   => 'Accueil',
			'post_status'  => 'publish',
			'post_content' => '',
		)
	);
}
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $front_id );
update_post_meta( $front_id, '_wp_page_template', 'template-artistik-onepage.php' );

/* ---------------------------------------------------------------
 * Menu principal
 * --------------------------------------------------------------- */
$base      = trailingslashit( home_url() );
$menu_name = 'Menu principal';
$m         = wp_get_nav_menu_object( $menu_name );
$menu_id   = $m ? (int) $m->term_id : (int) wp_create_nav_menu( $menu_name );

$old_items = wp_get_nav_menu_items( $menu_id );
if ( is_array( $old_items ) ) {
	foreach ( $old_items as $obj ) {
		if ( $obj && ! empty( $obj->ID ) ) {
			wp_delete_post( (int) $obj->ID, true );
		}
	}
}

$tree = array(
	array( 'title' => 'Accueil', 'url' => $base . '#accueil', 'children' => array() ),
);
$solutions_children = array();
foreach ( $sol_meta as $s ) {
	$solutions_children[] = array(
		'title' => $s['title'],
		'url'   => $base . '#' . $s['anchor'],
	);
}
$tree[] = array(
	'title'    => 'Nos solutions',
	'url'      => $base . '#solutions',
	'children' => $solutions_children,
);
foreach ( $sol_meta as $s ) {
	$children = array();
	foreach ( $s['modules'] as $mod ) {
		if ( empty( $mod['title'] ) ) { continue; }
		$children[] = array(
			'title' => $mod['title'],
			'url'   => $base . '#' . $s['anchor'] . '-' . sanitize_title( $mod['title'] ),
		);
	}
	$tree[] = array(
		'title'    => $s['title'],
		'url'      => $base . '#' . $s['anchor'],
		'children' => $children,
	);
}
$tree[] = array( 'title' => 'Contact', 'url' => $base . '#contact', 'children' => array() );

$created_items = 0;
foreach ( $tree as $node ) {
	$parent_id = wp_update_nav_menu_item(
		$menu_id, 0,
		array(
			'menu-item-title'  => $node['title'],
			'menu-item-url'    => $node['url'],
			'menu-item-type'   => 'custom',
			'menu-item-status' => 'publish',
		)
	);
	$created_items++;
	if ( ! is_wp_error( $parent_id ) && ! empty( $node['children'] ) ) {
		foreach ( $node['children'] as $child_node ) {
			wp_update_nav_menu_item(
				$menu_id, 0,
				array(
					'menu-item-title'     => $child_node['title'],
					'menu-item-url'       => $child_node['url'],
					'menu-item-type'      => 'custom',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => (int) $parent_id,
				)
			);
			$created_items++;
		}
	}
}

$locations = get_theme_mod( 'nav_menu_locations', array() );
if ( ! is_array( $locations ) ) { $locations = array(); }
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

printf(
	"OK — Thème : %s | Solutions : %d créées, %d mises à jour | Front ID : %d | Menu items : %d.\n",
	$child,
	$created,
	$updated,
	$front_id,
	$created_items
);
