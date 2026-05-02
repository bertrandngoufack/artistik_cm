<?php
/**
 * Import / mise à jour des pages Artistik CM (cahier des charges web_site_artistik_cm).
 * Exécuter : wp eval-file scripts/wordpress-import-artistik-pages.php --path=/var/www/html/artistik_cm --allow-root
 *
 * @package Artistik_CM
 */

if ( ! function_exists( 'wp_insert_post' ) ) {
	fwrite( STDERR, "WordPress non chargé. Utilisez wp eval-file.\n" );
	exit( 1 );
}

/**
 * Crée ou met à jour une page par slug.
 */
function artistik_cm_ensure_page( string $slug, string $title, string $content ): int {
	$existing = get_page_by_path( $slug, OBJECT, 'page' );
	if ( $existing instanceof WP_Post ) {
		wp_update_post(
			array(
				'ID'           => $existing->ID,
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'publish',
			)
		);
		return (int) $existing->ID;
	}
	$id = wp_insert_post(
		array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $title,
			'post_name'    => $slug,
			'post_content' => $content,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		fwrite( STDERR, $id->get_error_message() . "\n" );
		exit( 1 );
	}
	return (int) $id;
}

$home = <<<'HTML'
<!-- wp:html -->
<section class="hero is-primary is-bold">
<div class="hero-body">
<div class="container">
<p class="title is-2">Artistik</p>
<p class="subtitle is-5">Solutions logicielles web, IPTV et accompagnement — depuis 2006.</p>
<p class="mt-4"><a class="button is-light is-artistik" href="/artistik_cm/nos-solutions/">Découvrir nos solutions</a> <a class="button is-outlined is-light" href="/artistik_cm/contact/">Contact</a></p>
</div>
</div>
</section>
<section class="section">
<div class="container content">
<h2 class="title is-3">Bienvenue</h2>
<p>Conception du site <strong><a href="https://artistik.cm" rel="noopener">artistik.cm</a></strong> en français et en anglais, fortement paramétrable dans l’administration WordPress, avec CSS et JavaScript servis en local (thème Bulma intégré).</p>
<p>Nos offres couvrent la gestion des établissements de santé (<strong>SoluMed</strong>), l’éducation (<strong>LyCol</strong>) et l’immobilier locatif (<strong>Simba</strong>).</p>
<div class="columns is-multiline mt-5">
<div class="column is-one-third">
<div class="box h-full">
<h3 class="title is-5">SoluMed</h3>
<p class="is-size-7">Cliniques, hôpitaux, centres de santé.</p>
<a href="/artistik_cm/nos-solutions/#solumed" class="is-size-7">En savoir plus →</a>
</div>
</div>
<div class="column is-one-third">
<div class="box h-full">
<h3 class="title is-5">LyCol</h3>
<p class="is-size-7">Écoles et universités, web & mobile.</p>
<a href="/artistik_cm/nos-solutions/#lycol" class="is-size-7">En savoir plus →</a>
</div>
</div>
<div class="column is-one-third">
<div class="box h-full">
<h3 class="title is-5">Simba</h3>
<p class="is-size-7">Gestion locative et loyers.</p>
<a href="/artistik_cm/nos-solutions/#simba" class="is-size-7">En savoir plus →</a>
</div>
</div>
</div>
</div>
</section>
<!-- /wp:html -->
HTML;

$solutions = <<<'HTML'
<!-- wp:html -->
<section class="section">
<div class="container content">
<h1 class="title is-2">Nos solutions logicielles</h1>
<p class="subtitle">Des outils métiers conçus par <strong>Artistik</strong> pour gagner en efficacité et fiabilité.</p>

<h2 id="solumed" class="title is-3 mt-6">SoluMed</h2>
<h3 class="title is-5">Logiciel de gestion des cliniques / hôpitaux / polycliniques</h3>
<p>Dans un monde technologique en perpétuelle évolution, il est primordial pour les entreprises de disposer d’un <strong>système informatique</strong> performant, capable de travailler rapidement et efficacement et d’obtenir des résultats fiables. C’est dans ce cadre que la société Artistik a créé depuis <strong>2006</strong> le logiciel <strong>SoluMed</strong>, un outil de gestion pour votre établissement médical (clinique, centre de santé, groupe médical…). SoluMed s’appuie sur plusieurs modules.</p>

<h3 class="title is-5">Les fonctionnalités principales</h3>

<h4 class="title is-6">Gestion des patients (assurés ou non assurés)</h4>
<ul>
<li>Ouverture de dossier</li>
<li>Admission, réadmission</li>
<li>Gestion des hospitalisations</li>
<li>File d’attente et rendez-vous</li>
</ul>

<h4 class="title is-6">Gestion de la caisse</h4>
<ul>
<li>Encaissement patient</li>
<li>Édition de reçus (formats A4 et ticket caisse)</li>
<li>Point de caisse</li>
</ul>

<h4 class="title is-6">Gestion des assurances</h4>
<ul>
<li>Paramétrage des conventions</li>
<li>Recouvrement des factures</li>
</ul>

<h4 class="title is-6">Gestion de la facturation</h4>
<ul>
<li>Factures consultation et hospitalisation</li>
<li>Pro-formas</li>
<li>Recouvrement assurances</li>
</ul>

<h4 class="title is-6">Gestion des dépenses</h4>
<ul>
<li>Saisie des dépenses</li>
<li>Situation de trésorerie</li>
</ul>

<h4 class="title is-6">Gestion des honoraires</h4>
<ul>
<li>Paramétrage par médecin</li>
<li>Honoraires par médecin, ristournes</li>
</ul>

<h4 class="title is-6">Pharmacie locale</h4>
<ul>
<li>Approvisionnements, consommations par patient</li>
<li>Stock, inventaire, bons de commande</li>
</ul>

<h4 class="title is-6">Laboratoire</h4>
<ul>
<li>Résultats d’examen, réactifs</li>
<li>État financier du labo</li>
</ul>

<h4 class="title is-6">Numérisation du dossier médical</h4>
<ul>
<li>Saisie ou scan des dossiers</li>
<li>Recherche et édition</li>
</ul>

<hr class="my-6" />

<h2 id="lycol" class="title is-3">LyCol</h2>
<h3 class="title is-5">Logiciel de gestion pour écoles et universités</h3>
<p>Application développée par <strong>Artistik CM</strong> : gestion scolaire et universitaire pour le primaire, le secondaire, les grandes écoles et les universités. Espace collaboratif pour l’établissement, les enseignants, les parents, les élèves et étudiants. <strong>LyCol</strong> est disponible en web et mobile, avec des espaces dédiés aux managers et fondateurs.</p>

<h3 class="title is-5">Fonctionnalités principales</h3>

<h4 class="title is-6">Élèves / étudiants</h4>
<ul>
<li>Admissions, réadmissions, inscriptions</li>
<li>Indicateurs d’effectifs, listes de classe</li>
<li>Attestations, certificats, badges PVC</li>
<li>SMS, emploi du temps</li>
</ul>

<h4 class="title is-6">Caisse scolaire</h4>
<ul>
<li>Encaissements, reçus avec photo</li>
<li>Points de caisse, trésorerie, relances</li>
<li>Tableaux de bord, frais annexes, échéanciers</li>
<li>Contrôle d’accès biométrique, engagement parental</li>
</ul>

<h4 class="title is-6">Professeurs</h4>
<ul>
<li>Vacations, relevés, listes détaillées</li>
</ul>

<h4 class="title is-6">Notes</h4>
<ul>
<li>Saisie, relevés semestriels / trimestriels</li>
<li>Matrice des moyennes, LMD, statistiques, rapports</li>
</ul>

<h4 class="title is-6">Envoi de SMS</h4>
<ul>
<li>Informations, absences, rappels de paiement, notes, moyennes</li>
</ul>

<hr class="my-6" />

<h2 id="simba" class="title is-3">Simba</h2>
<h3 class="title is-5">Logiciel de gestion immobilière (loyers)</h3>
<p>La gestion multi-lots implique relances, quittances et suivi des impayés. <strong>Simba</strong> automatise ces tâches avec une interface simple tout en offrant des fonctions avancées : en quelques clics, structurez votre gestion locative.</p>

<h3 class="title is-5">Fonctionnalités principales</h3>
<ul>
<li>Gestion des appartements, bailleurs, locataires, agents</li>
<li>Contrat de bail, cautions, loyers et arriérés</li>
<li>Comptabilité recettes / dépenses</li>
<li>Listes et états par bâtiment, reçus, point de caisse</li>
<li>Relevés par locataire, états d’arriérés</li>
</ul>
</div>
</section>
<!-- /wp:html -->
HTML;

$about = <<<'HTML'
<!-- wp:html -->
<section class="section">
<div class="container content">
<h1 class="title is-2">Qui sommes-nous ?</h1>
<p><strong>Artistik</strong> conçoit et déploie des <strong>solutions logicielles web</strong> et des services associés (dont <strong>IPTV</strong>) pour les entreprises et les établissements qui souhaitent moderniser leur système d’information.</p>
<p>Depuis <strong>2006</strong>, nous développons des produits métiers — notamment <strong>SoluMed</strong> pour la santé, <strong>LyCol</strong> pour l’éducation et <strong>Simba</strong> pour l’immobilier locatif — avec une priorité : fiabilité, rapidité et ergonomie.</p>
<p>Nos sites et applications sont pensés pour être <strong>paramétrables</strong> par vos équipes dans l’administration WordPress, avec des formations adaptées et des ressources <strong>CSS / JS servies en local</strong> lorsque c’est requis.</p>
<p>Projet vitrine : <a href="https://artistik.cm" rel="noopener">artistik.cm</a> — contact : <a href="mailto:info@artistik.cm">info@artistik.cm</a></p>
</div>
</section>
<!-- /wp:html -->
HTML;

$contact = <<<'HTML'
<!-- wp:html -->
<section class="section">
<div class="container content">
<h1 class="title is-2">Contact</h1>
<p class="subtitle">Échangeons sur votre projet logiciel ou votre présence web.</p>
<div class="box" style="max-width:640px">
<ul class="is-size-5">
<li><strong>Site web :</strong> <a href="https://artistik.cm" rel="noopener">artistik.cm</a></li>
<li><strong>Courriel :</strong> <a href="mailto:info@artistik.cm">info@artistik.cm</a></li>
</ul>
<p class="mt-4 is-size-7 has-text-grey">Pour les formulaires avancés (demande de démo, devis), vous pourrez configurer <strong>Formidable Forms</strong> depuis l’administration WordPress.</p>
</div>
</div>
</section>
<!-- /wp:html -->
HTML;

$url_base = trailingslashit( (string) ( wp_parse_url( home_url(), PHP_URL_PATH ) ?: '/' ) );
$home     = str_replace( '/artistik_cm/', $url_base, $home );

$ids = array(
	'accueil'        => artistik_cm_ensure_page( 'accueil', 'Accueil', $home ),
	'nos-solutions'  => artistik_cm_ensure_page( 'nos-solutions', 'Nos solutions', $solutions ),
	'qui-sommes-nous'=> artistik_cm_ensure_page( 'qui-sommes-nous', 'Qui sommes-nous', $about ),
	'contact'        => artistik_cm_ensure_page( 'contact', 'Contact', $contact ),
);

update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $ids['accueil'] );

$menu_name   = 'Menu principal';
$menu_exists = wp_get_nav_menu_object( $menu_name );
if ( ! $menu_exists ) {
	$menu_id = wp_create_nav_menu( $menu_name );
} else {
	$menu_id = (int) $menu_exists->term_id;
}

$old_items = wp_get_nav_menu_items( $menu_id );
if ( is_array( $old_items ) ) {
	foreach ( $old_items as $obj ) {
		if ( $obj && ! empty( $obj->ID ) ) {
			wp_delete_post( (int) $obj->ID, true );
		}
	}
}

$order = array(
	$ids['accueil'],
	$ids['nos-solutions'],
	$ids['qui-sommes-nous'],
	$ids['contact'],
);

foreach ( $order as $page_id ) {
	wp_update_nav_menu_item(
		$menu_id,
		0,
		array(
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
		)
	);
}

$locations = get_theme_mod( 'nav_menu_locations', array() );
if ( ! is_array( $locations ) ) {
	$locations = array();
}
$locations['primary'] = $menu_id;
set_theme_mod( 'nav_menu_locations', $locations );

if ( wp_get_theme( 'artistik-cm' )->exists() ) {
	switch_theme( 'artistik-cm' );
	echo "Thème artistik-cm activé.\n";
}

echo "Pages Artistik CM : OK\n";
echo wp_json_encode( $ids, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) . "\n";
