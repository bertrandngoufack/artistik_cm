<?php
/**
 * Pass 4 : traductions EN pour les nouvelles solutions Boutik / Pastra / Smily
 * + nouveau hero lead + nouvelle stat (2016 / Année de création d'Artistik).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $wpdb;

$default_language = 'fr_FR';
$language_code    = 'en_US';

$trp   = TRP_Translate_Press::get_trp_instance();
$query = $trp->get_component( 'query' );

$query->check_table( $default_language, $language_code );

/* ============================================================
 * Regular dictionary : strings issues du contenu (CPT, menu, etc.)
 * ============================================================ */
$dictionary = [

    /* ---- Hero lead remplacé : nouveaux fragments ---- */
    'Depuis'                                        => 'Since',
    ', Artistik conçoit des logiciels métiers fiables pour la' => ', Artistik builds reliable business software for',
    'santé'                                         => 'healthcare',
    ', l’'                                          => ', ',
    'éducation'                                     => 'education',
    ', l’immobilier, le'                            => ', real estate,',
    'commerce'                                      => 'commerce',
    ', l’agropastoral et la'                        => ', agropastoral and',
    'dentisterie'                                   => 'dentistry',
    '. SoluMed, LyCol, Simba, Boutik, Pastra et Smily — six suites complètes, évolutives et adaptées à votre organisation.'
        => '. SoluMed, LyCol, Simba, Boutik, Pastra and Smily — six comprehensive, scalable suites tailored to your organisation.',

    /* ---- Nouvelle stat ---- */
    'Année de création d’Artistik'                  => 'Artistik’s founding year',

    /* ---- Badges nouvelles solutions ---- */
    'Commerce'                                      => 'Commerce',
    'Agropastoral'                                  => 'Agropastoral',
    'Dentaire'                                      => 'Dental',

    /* ---- Sous-titres tuiles & sections ---- */
    'Logiciel de gestion commerciale (POS) — supermarchés, pharmacies, magasins'
        => 'Commerce management software (POS) — supermarkets, pharmacies, shops',
    'Logiciel de gestion du bétail, troupeaux, volailles & transhumance géolocalisée'
        => 'Livestock, herd, poultry & geolocated transhumance management software',
    'Logiciel de gestion du bétail, troupeaux, volailles &#038; transhumance géolocalisée'
        => 'Livestock, herd, poultry & geolocated transhumance management software',
    'Logiciel intelligent pour cabinets dentaires — patients, agenda, odontogramme, IA'
        => 'AI-powered software for dental clinics — patients, calendar, odontogram, AI',

    /* ---- SVG labels ---- */
    'Illustration commerce'      => 'Commerce illustration',
    'Illustration agropastoral'  => 'Agropastoral illustration',
    'Illustration dentaire'      => 'Dental illustration',

    /* ---- Intros produits ---- */
    'est la solution Artistik pour les'             => 'is the Artistik solution for',
    'commerces multi-points de vente'               => 'multi-store retailers',
    ': supermarchés, pharmacies, boutiques, grandes surfaces. Gérez vos magasins, votre stock, vos achats, vos ventes et vos rapports financiers depuis une même interface — en ligne ou hors connexion.'
        => ': supermarkets, pharmacies, shops, hypermarkets. Manage your stores, stock, purchases, sales and financial reports from a single interface — online or offline.',

    'est la première solution Artistik dédiée aux'  => 'is the Artistik solution dedicated to',
    'éleveurs'                                      => 'livestock farmers',
    ': bovins, ovins, caprins, volailles. Avec son module' => ': cattle, sheep, goats, poultry. With its',
    'géolocalisation & transhumance'                => 'geolocation & transhumance',
    'géolocalisation &#038; transhumance'           => 'geolocation & transhumance',
    ', Pastra suit chaque animal et chaque troupeau en temps réel — y compris hors ligne — pour sécuriser le patrimoine, prévenir les conflits et piloter la productivité.'
        => 'module, Pastra tracks every animal and every herd in real time — even offline — to secure your assets, prevent conflicts and drive productivity.',

    'est la suite Artistik dédiée aux'              => 'is the Artistik suite dedicated to',
    'cabinets et cliniques dentaires'               => 'dental practices and clinics',
    '. Une plateforme complète pour gérer dossiers patients, agenda, facturation, odontogramme, imagerie DICOM et téléconsultation — avec des modules'
        => '. A complete platform to manage patient records, calendar, billing, odontogram, DICOM imaging and telemedicine — with modules',
    'assistés par IA'                               => 'powered by AI',

    /* ============================================================
     * Modules — Boutik
     * ============================================================ */
    'Multi-magasins'                                          => 'Multi-stores',
    'Plusieurs sociétés ou enseignes gérées dans une même installation' => 'Multiple companies or brands managed in a single install',
    'Aucune limite sur le nombre de magasins'                 => 'No limit on the number of stores',
    'Stocks, achats et ventes cloisonnés par entité'          => 'Stock, purchases and sales isolated per entity',

    'Points de vente & entrepôts'                             => 'Storefronts & warehouses',
    'Points de vente &#038; entrepôts'                        => 'Storefronts & warehouses',
    'Plusieurs lieux par société (magasin, entrepôt)'         => 'Multiple locations per company (store, warehouse)',
    'Gestion simultanée de tous les sites'                    => 'Manage all sites simultaneously',
    'Mise en page de la facture personnalisable par site'     => 'Customisable invoice layout per site',

    'Utilisateurs & rôles'                                    => 'Users & roles',
    'Utilisateurs &#038; rôles'                               => 'Users & roles',
    'Système de rôles et permissions complet'                 => 'Complete role and permission system',
    'Rôles prédéfinis : Admin, Caissier'                      => 'Predefined roles: Admin, Cashier',
    'Création de rôles sur mesure'                            => 'Custom role creation',

    'Contacts (clients & fournisseurs)'                       => 'Contacts (customers & suppliers)',
    'Contacts (clients &#038; fournisseurs)'                  => 'Contacts (customers & suppliers)',
    'Statut client, fournisseur ou les deux'                  => 'Customer, supplier or both',
    'Historique des transactions par contact'                 => 'Transaction history per contact',
    'Soldes débiteur / créditeur'                             => 'Credit / debit balances',
    'Échéances de paiement avec alerte automatique'           => 'Payment due dates with automatic alerts',

    'Produits & catalogue'                                    => 'Products & catalog',
    'Produits &#038; catalogue'                               => 'Products & catalog',
    'Produits simples ou avec variantes'                      => 'Single or variable products',
    'Marques, catégories, sous-catégories, unités'            => 'Brands, categories, sub-categories, units',
    'SKU manuel ou auto-généré avec préfixe'                  => 'Manual or auto-generated SKU with prefix',
    'Alerte de stock bas'                                     => 'Low stock alerts',
    'Calcul auto du prix de vente (achat + marge)'            => 'Auto selling price (cost + margin)',

    'Achats'                                                  => 'Purchases',
    'Saisie rapide multi-magasins'                            => 'Quick multi-store entry',
    'Achats payés / dus + alertes d’échéance'                 => 'Paid / due purchases + due-date alerts',
    'Remises et taxes'                                        => 'Discounts and taxes',

    'Ventes (POS)'                                            => 'Sales (POS)',
    'Interface caissier ultra rapide (Ajax)'                  => 'Ultra-fast cashier interface (Ajax)',
    'Client de passage par défaut'                            => 'Default walk-in customer',
    'Ajout d’un nouveau client depuis l’écran de caisse'      => 'Add a new customer from the POS screen',
    'Brouillon ou facture finale'                             => 'Draft or final invoice',
    'Multiples modes de paiement'                             => 'Multiple payment methods',
    'Express checkout, fonctionne hors ligne'                 => 'Express checkout, works offline',

    'Saisie facile et catégorisation'                         => 'Easy entry and categorisation',
    'Analyse par catégorie et magasin'                        => 'Analysis by category and store',

    'Rapports'                                                => 'Reports',
    'Achats / Ventes / Taxes / Contacts'                      => 'Purchases / Sales / Taxes / Contacts',
    'Rapports de stock et de dépenses'                        => 'Stock and expense reports',
    'Produits tendance par marque, catégorie, période'        => 'Trending products by brand, category, period',
    'Rapport caisse, rapport représentants'                   => 'Cash register report, sales rep report',

    'Configuration & extras'                                  => 'Configuration & extras',
    'Configuration &#038; extras'                             => 'Configuration & extras',
    'Devise, fuseau, exercice, marge globale'                 => 'Currency, timezone, fiscal year, global margin',
    'Codes-barres et étiquettes prédéfinis'                   => 'Predefined barcodes and labels',
    'Multilingue, ajustement de stock, mode hors ligne'       => 'Multilingual, stock adjustment, offline mode',
    'Installation en 3 étapes'                                => '3-step installation',

    /* ============================================================
     * Modules — Pastra
     * ============================================================ */
    'Configuration générale'                                  => 'General configuration',
    'Paramètres ferme : nom, devise, dates'                   => 'Farm settings: name, currency, dates',
    'Unités de mesure (kg, litre, tête, etc.)'                => 'Units of measure (kg, litre, head, etc.)',
    'Multilingue (français, anglais, foulfouldé)'             => 'Multilingual (French, English, Fulfulde)',
    'Sauvegarde et restauration de la base'                   => 'Database backup and restore',
    'Cartes hors ligne pour zones sans réseau'                => 'Offline maps for areas without network',
    'Profils utilisateurs (admin, berger, vétérinaire…)'      => 'User profiles (admin, herder, vet…)',

    'Géolocalisation & transhumance'                          => 'Geolocation & transhumance',
    'Géolocalisation &#038; transhumance'                     => 'Geolocation & transhumance',
    'Définition d’une zone d’élevage (polygone)'              => 'Define a grazing zone (polygon)',
    'Départ / arrivée de transhumance (date, GPS)'            => 'Transhumance departure / arrival (date, GPS)',
    'Suivi GPS temps réel (collier compatible)'               => 'Real-time GPS tracking (compatible collar)',
    'Alerte de sortie de zone (clôture virtuelle)'            => 'Out-of-zone alert (virtual fence)',
    'Historique des déplacements par troupeau ou animal'      => 'Movement history per herd or animal',
    'Carte interactive : points d’eau, pâturages, marchés'    => 'Interactive map: waterholes, pastures, markets',
    'Déclaration de dommage géolocalisée (photo)'             => 'Geolocated damage report (photo)',
    'Dernière position connue de chaque animal'               => 'Last known position of each animal',
    'Export carte (PDF / image)'                              => 'Map export (PDF / image)',

    'Bétail (livestock)'                                      => 'Livestock',
    'Enregistrement (achat, naissance, race, sexe, âge)'      => 'Recording (purchase, birth, breed, sex, age)',
    'Identifiant unique : boucle, puce, QR'                   => 'Unique ID: ear tag, chip, QR',
    'Géolocalisation actuelle (lat/long, village)'            => 'Current geolocation (lat/long, village)',
    'Suivi par lot et par variant (sous-races)'               => 'Tracking by batch and variant (sub-breeds)',
    'Recherche par ID, nom, localisation, propriétaire'       => 'Search by ID, name, location, owner',

    'Plusieurs achats par facture'                            => 'Multiple purchases per invoice',
    'Fournisseurs avec paiement comptant / crédit / échéancier' => 'Suppliers with cash / credit / scheduled payment',
    'Affectation auto à une étable ou un campement'           => 'Auto-assign to a shed or camp',
    'Géolocalisation du lieu d’achat'                         => 'Geolocation of the purchase location',
    'Historique par fournisseur et par zone'                  => 'History per supplier and zone',

    'Étables & campements'                                    => 'Sheds & camps',
    'Étables &#038; campements'                               => 'Sheds & camps',
    'Étable fixe ou campement mobile'                         => 'Fixed shed or mobile camp',
    'Localisation GPS de chaque structure'                    => 'GPS location of each facility',
    'Transferts d’animaux entre étables'                      => 'Animal transfers between sheds',
    'Décès (cause, date, position GPS)'                       => 'Deaths (cause, date, GPS position)',
    'Capacité maximale et historique d’occupation'            => 'Maximum capacity and occupancy history',

    'Vaccins'                                                 => 'Vaccines',
    'Catalogue vaccins (nom, type, fabricant)'                => 'Vaccine catalog (name, type, manufacturer)',
    'Dosage paramétrable'                                     => 'Configurable dosage',
    'Affectation par animal ou par lot'                       => 'Assign per animal or batch',
    'Vaccins périmés / gaspillés'                             => 'Expired / wasted vaccines',
    'Calendrier vaccinal avec alertes'                        => 'Vaccination calendar with alerts',
    'Tournées de vaccination géolocalisées'                   => 'Geolocated vaccination rounds',

    'Alimentation'                                            => 'Feeding',
    'Catalogue d’aliments'                                    => 'Feed catalog',
    'Stock entrée / sortie / seuil d’alerte'                  => 'Stock in / out / alert threshold',
    'Localisation du stockage'                                => 'Storage location',
    'Suivi de consommation par animal / lot / zone'           => 'Consumption tracking per animal / batch / zone',

    'Production & reproduction'                               => 'Production & reproduction',
    'Production &#038; reproduction'                          => 'Production & reproduction',
    'Catégories de produits (lait, œufs, cuir, fumier…)'      => 'Product categories (milk, eggs, leather, manure…)',
    'Production journalière ajoutée au stock'                 => 'Daily production added to stock',
    'Pertes / gaspillages'                                    => 'Losses / wastage',
    'Vente directe depuis le stock'                           => 'Direct sale from stock',
    'Saillie, gestation, mise-bas géolocalisée'               => 'Mating, gestation, geolocated births',

    'Ventes'                                                  => 'Sales',
    'Vente bétail vivant (par étable / campement)'            => 'Live livestock sales (per shed / camp)',
    'Vente produits (lait, œufs, cuir, etc.)'                 => 'Product sales (milk, eggs, leather, etc.)',
    'Géolocalisation du lieu de vente'                        => 'Geolocation of the sale location',
    'Facturation auto et historique'                          => 'Auto invoicing and history',

    'Paiements'                                               => 'Payments',
    'Fournisseurs, clients, salaires'                         => 'Suppliers, customers, salaries',
    'Génération de reçus'                                     => 'Receipt generation',
    'Localisation du paiement (mobile money, espèces…)'       => 'Payment location (mobile money, cash…)',

    'Rapports & analyses'                                     => 'Reports & analytics',
    'Rapports &#038; analyses'                                => 'Reports & analytics',
    'Achats / ventes / décès par zone et par période'         => 'Purchases / sales / deaths by zone and period',
    'État du stock bétail par zone géographique'              => 'Livestock stock status per geographical zone',
    'Rapport de transhumance, distances, durées'              => 'Transhumance report, distances, durations',
    'Heatmap des zones de pâturage'                           => 'Heatmap of grazing zones',
    'Rapport conflits agriculteurs-éleveurs'                  => 'Farmer–herder conflict report',
    'Bilan financier, export PDF / Excel'                     => 'Financial statement, PDF / Excel export',

    /* ============================================================
     * Modules — Smily
     * ============================================================ */
    'Patients'                                                => 'Patients',
    'Dossiers centralisés : antécédents, allergies, assurance, urgence' => 'Centralised records: history, allergies, insurance, emergency',
    'Recherche avancée, filtres et actions en masse'          => 'Advanced search, filters and bulk actions',
    'Historique de soins consolidé'                           => 'Consolidated treatment history',

    'Rendez-vous'                                             => 'Appointments',
    'Vues calendrier flexibles'                               => 'Flexible calendar views',
    'Prise, modification et reprogrammation'                  => 'Booking, editing and rescheduling',
    'Rappels automatiques (SMS / e-mail)'                     => 'Automatic reminders (SMS / e-mail)',
    'Synchronisation Google Agenda'                           => 'Google Calendar sync',
    'Suggestions de créneaux par IA'                          => 'AI slot suggestions',

    'Praticiens & équipe'                                     => 'Practitioners & staff',
    'Praticiens &#038; équipe'                                => 'Practitioners & staff',
    'Profils détaillés et plannings'                          => 'Detailed profiles and schedules',
    'Affectation de rôles'                                    => 'Role assignment',
    'Analytique de performance par praticien'                 => 'Performance analytics per practitioner',
    'Actions en masse, statuts'                               => 'Bulk actions, statuses',

    'Multi-cabinets'                                          => 'Multi-clinics',
    'Gestion de plusieurs sites depuis une interface'         => 'Manage multiple sites from a single interface',
    'Coordonnées, horaires, paramètres opérationnels par site' => 'Contact, hours, operational settings per site',

    'Facturation & devis'                                     => 'Billing & quotes',
    'Facturation &#038; devis'                                => 'Billing & quotes',
    'Factures avec remises, taxes, modes de paiement'         => 'Invoices with discounts, taxes, payment methods',
    'Téléchargement PDF'                                      => 'PDF download',
    'Suivi des soldes restants'                               => 'Outstanding balance tracking',

    'Saisie et suivi par patient'                             => 'Entry and tracking per patient',
    'Méthodes et statuts'                                     => 'Methods and statuses',
    'Lié à la facturation et à l’analytique'                  => 'Linked to billing and analytics',

    'Saisie, catégorisation et budget'                        => 'Entry, categorisation and budget',
    'Widgets et graphiques de synthèse'                       => 'Summary widgets and charts',

    'Adhésions / forfaits'                                    => 'Memberships / plans',
    'Plans d’adhésion patients'                               => 'Patient membership plans',
    'Bénéfices, paiements, renouvellements'                   => 'Benefits, payments, renewals',
    'Indicateurs colorés'                                     => 'Colour-coded indicators',

    'Prescriptions'                                           => 'Prescriptions',
    'Création et suivi (médicament, posologie, renouvellements)' => 'Create and track (drug, dosage, refills)',
    'Assistant IA pour la sécurité de prescription'           => 'AI assistant for safe prescribing',

    'Médicaments'                                             => 'Medications',
    'Inventaire avec dates de péremption et lots'             => 'Inventory with expiry dates and batches',
    'Import / export en masse'                                => 'Bulk import / export',
    'Filtres avancés'                                         => 'Advanced filters',

    'Certificats médicaux'                                    => 'Medical certificates',
    'Émission, diagnostic, recommandations'                   => 'Issue, diagnosis, recommendations',
    'Export PDF, lien avec patients et praticiens'            => 'PDF export, linked to patients and practitioners',

    'Patients, médicaments, finances, opérations'             => 'Patients, medications, finance, operations',
    'Graphiques interactifs et exports'                       => 'Interactive charts and exports',

    'Paramètres'                                              => 'Settings',
    'Localisation, horaires, branding'                        => 'Localisation, hours, branding',
    'Intégrations IA et téléconsultation'                     => 'AI and telemedicine integrations',

    'Odontogramme'                                            => 'Odontogram',
    'Schéma dentaire interactif'                              => 'Interactive dental chart',
    'Conditions, traitements et historique'                   => 'Conditions, treatments and history',
    'Prédictions IA et visualisation 3D des mâchoires'        => 'AI predictions and 3D jaw visualisation',

    'Examens de laboratoire'                                  => 'Lab tests',
    'Création, import et analyse de résultats'                => 'Create, upload and analyse results',
    'Analyse de rapport assistée par IA'                      => 'AI-powered report analysis',
    'Export PDF'                                              => 'PDF export',

    'Téléconsultation'                                        => 'Telemedicine',
    'Consultations à distance'                                => 'Remote consultations',
    'Intégration Google Meet (vidéo / audio / chat)'          => 'Google Meet integration (video / audio / chat)',

    'Maintenance équipements'                                 => 'Equipment maintenance',
    'Suivi du matériel'                                       => 'Equipment tracking',
    'Planification des interventions'                         => 'Service scheduling',
    'Affectation des techniciens et historique'               => 'Technician assignment and history',

    'Visualiseur DICOM'                                       => 'DICOM viewer',
    'Lecture des images dentaires associées au patient'       => 'View dental images linked to the patient',

    'Marketing'                                               => 'Marketing',
    'Gestion de campagnes intégrée'                           => 'Integrated campaign management',

    /* ---- Form select options ---- */
    'Boutik (commerce / POS)'                  => 'Boutik (commerce / POS)',
    'Pastra (agropastoral / élevage)'          => 'Pastra (agropastoral / livestock)',
    'Smily (dentaire)'                         => 'Smily (dental)',
];

$query->insert_strings( array_keys( $dictionary ), $language_code, 0 );
$existing = $query->get_string_ids( array_keys( $dictionary ), $language_code );

$rows = [];
foreach ( $dictionary as $original => $translated ) {
    if ( ! isset( $existing[ $original ] ) ) continue;
    $rows[] = [
        'id'          => (int) $existing[ $original ]->id,
        'original'    => $original,
        'translated'  => $translated,
        'status'      => 2,
        'block_type'  => 0,
        'original_id' => 0,
    ];
}
if ( $rows ) {
    $ids = wp_list_pluck( $rows, 'id' );
    $existing_with_orig = $wpdb->get_results(
        "SELECT id, original_id FROM wp_trp_dictionary_fr_fr_en_us WHERE id IN (" . implode( ',', $ids ) . ')',
        OBJECT_K
    );
    foreach ( $rows as &$r ) {
        if ( isset( $existing_with_orig[ $r['id'] ]->original_id ) ) {
            $r['original_id'] = (int) $existing_with_orig[ $r['id'] ]->original_id;
        }
    }
    unset( $r );
    $query->update_strings( $rows, $language_code );
}
WP_CLI::log( 'Regular dictionary : ' . count( $rows ) . ' lignes mises à jour.' );

/* ============================================================
 * Purge des transients TP
 * ============================================================ */
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_trp_%' OR option_name LIKE '_transient_timeout_trp_%'" );
wp_cache_flush();

WP_CLI::success( 'Pass 4 OK : traductions Boutik / Pastra / Smily / hero ajoutées.' );
