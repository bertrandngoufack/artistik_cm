# Audit architectural — Boutik (Laravel POS multi-magasins)

> Audit réalisé en lecture statique du dépôt `web/Boutik/` (Laravel 9, ~17 000 lignes critiques, 299 migrations, ~60 contrôleurs).
> Auteur : architecte logiciel Laravel, mandaté par Artistik CM.
> Dernière mise à jour : 2026-05-03.

---

## 1. Identification du socle réel

`Boutik` n'est **pas** une application développée *ex nihilo* pour le Cameroun. C'est un **fork / rebranding du progiciel commercial « Ultimate POS »** (éditeur *The Web Fosters / Ultimate Fosters*, distribué sur CodeCanyon).

Preuves directes dans le dépôt :

- `readme.md` : *« Ultimate POS is a POS application by Ultimate Fosters … licensed under the Codecanyon license »*.
- `.env.example` : `APP_NAME="Ultimate POS"`, `APP_KEY=base64:W8UqtE9LHZW+gRag78o4BCbN1M0w4HdaIFdLqHJ/9PA=`, `APP_TIMEZONE="Asia/Kolkata"`.

> **Implication juridique majeure** : la licence CodeCanyon (Standard / Extended) restreint la redistribution. Vendre `Boutik` à des commerçants camerounais comme *votre* produit nécessite, au minimum, une **Extended License par installation client** ou un **OEM agreement** avec l'éditeur. Sans cela, Artistik est en zone de risque légal.

---

## 2. Stack technique

| Couche | Choix |
|---|---|
| Framework | **Laravel 9.51** (PHP ≥ 8.0) |
| Modules | `nwidart/laravel-modules ^9.0` (architecture en plug-ins) |
| Auth & API | `laravel/passport 11.6.1` (OAuth2), `laravel/ui 4.x` |
| Permissions | `spatie/laravel-permission ^5.5` |
| Audit trail | `spatie/laravel-activitylog ^4.4` |
| Backups | `spatie/laravel-backup ^8.0` (+ Dropbox / S3) |
| Tableaux | `yajra/laravel-datatables-oracle ^9.19` |
| PDF / impression | `barryvdh/laravel-dompdf`, `mpdf/mpdf`, `milon/barcode` |
| Excel | `maatwebsite/excel ^3.1.8` |
| Charts | `consoletvs/charts ^6.5` |
| Front | AdminLTE (jQuery + Bootstrap) — rendu *server-side* en Blade, **pas de SPA** |
| Realtime | `pusher/pusher-php-server` |
| SMS / WhatsApp | `aloha/twilio` |
| IA | `openai-php/laravel` (module *AiAssistance*) |
| eCommerce | WooCommerce REST (`automattic/woocommerce`) |

**Passerelles de paiement embarquées** : Stripe, PayPal (`srmklive/paypal`), Razorpay (Inde), **Paystack** (Nigeria/AO), **Pesapal** (Kenya/AE), MyFatoorah (Golfe), Knox/Pesapal.

**Aucune intégration native MTN MoMo, Orange Money Cameroun, Express Union, YUP, Camtel Money, Smobilpay.**

---

## 3. Architecture applicative

### 3.1 Couches

Architecture **monolithique modulaire** :

```
app/
├── Http/Controllers/    ~60 contrôleurs « fat »
├── Http/Middleware/     17 middlewares (multi-tenant, langue, fuseau, sidebar)
├── Utils/               12 services métier (TransactionUtil, ProductUtil, …)
├── *.php (racine)       Modèles Eloquent (60+ entités)
├── Events/ Listeners/   Pour les paiements et alertes
└── Notifications/ Mail/ Templating de notifs
Modules/                  (NON inclus dans ce dépôt — voir §3.4)
```

Pattern dominant : **Controller → Util (service métier) → Eloquent Model**. Pas de Repository, pas de Service Layer formalisé, pas d'Action classes, pas de DTO.

Volumétries critiques :

```
6569 app/Utils/TransactionUtil.php
3033 app/Http/Controllers/SellPosController.php
4105 app/Http/Controllers/ReportController.php
2690 app/Utils/ProductUtil.php
```

> **Verdict** : ces fichiers sont des bombes à dette technique. Toute évolution réglementaire (TVA, retenues, FNE/DGI, OHADA) **passera par `TransactionUtil`**. Refactor par bounded contexts (`Pricing/`, `Tax/`, `Inventory/`, `Payment/`, `Accounting/`) **avant** tout chantier compliance.

### 3.2 Multi-tenant (multi-magasins / multi-sociétés)

Multi-tenant **logique**, pas physique :

- Table racine `business` (id auto-incrémenté).
- Toute table métier porte une colonne `business_id` (filtrage applicatif).
- Une `business` ⇒ N `business_locations` (points de vente, entrepôts).
- Hiérarchie : `User → business_id → BusinessLocation`.

Isolation **mise en cache en session** au login via `App\Http\Middleware\SetSessionData`.

> **Risque sécurité** : il n'y a **pas** de Global Scope Eloquent forçant le filtrage par `business_id`. La sécurité repose sur la discipline du développeur dans chaque requête. À renforcer via un `BelongsToBusiness` Trait + Global Scope.

### 3.3 Authentification & autorisation

Middlewares clés (`app/Http/Kernel.php`) : `auth`, `language`, `timezone`, `SetSessionData`, `setData` (IsInstalled), `AdminSidebarMenu`, `superadmin`, `CheckUserLogin`.

- **Spatie Permission** : rôles dynamiques + permissions granulaires.
- **Passport** : OAuth2 pour les API.
- Rôles « système » : Admin, Caissier, et **Superadmin** (cross-business pour la console SaaS).
- `CheckUserLogin` : un compte = une session active (anti-partage de licences).

### 3.4 Système de modules (CRITIQUE)

Activés via `modules_statuses.json` — 21 modules tous déclarés `true` :

```
Essentials, Accounting, AssetManagement, Cms, Connector, Crm, Ecommerce,
FieldForce, Manufacturing, ProductCatalogue, Project, Repair, Spreadsheet,
Superadmin, Woocommerce, AiAssistance, Hms, InboxReport, CustomDashboard,
Gym, ZatcaIntegrationKsa
```

> **Anomalie de packaging bloquante** : le dossier `Modules/` **n'existe pas** dans le dépôt. Les 21 modules sont *déclarés activés* mais leur code source est absent. Le `package:discover` Composer **échouera** sur `Modules\Essentials\Providers\…` au boot. Soit ces modules sont des add-ons payants à acheter séparément, soit ils ont été retirés du commit.
>
> **Action** : pour le déploiement initial, il faudra mettre tous ces modules à `false` dans `modules_statuses.json`, sinon Laravel ne démarrera pas. Voir procédure de déploiement §4.

---

## 4. Modèle de données

### 4.1 Volume

- **299 migrations** entre `2014_10_12` et `2025_09_19`.
- ~60 modèles Eloquent.
- Tables clés : `business`, `business_locations`, `users`, `roles`, `permissions`, `currencies`, `tax_rates`, `group_sub_taxes`, `units`, `brands`, `categories`, `products`, `variations`, `variation_location_details`, `variation_group_prices`, `selling_price_groups`, `discounts`, `contacts`, `customer_groups`, `transactions` (table polymorphe centrale), `purchase_lines`, `transaction_sell_lines`, `transaction_sell_lines_purchase_lines` (traçabilité FIFO/LIFO/AVCO), `transaction_payments`, `cash_registers`, `cash_register_transactions`, `cash_denominations`, `accounts`, `account_transactions`, `account_types`, `expense_categories`, `invoice_layouts`, `invoice_schemes`, `reference_counts`, `printers`, `barcodes`, `dashboard_configurations`, `notification_templates`.

### 4.2 Cœur transactionnel

Anti-pattern *Single Table Inheritance* assumé : la table `transactions` regroupe :

- types : `purchase, sell, expense, stock_adjustment, sell_transfer, purchase_transfer, opening_stock, sell_return, opening_balance, purchase_return, payroll, expense_refund, sales_order, purchase_order`
- statuts : `received, pending, ordered, draft, final, in_transit, completed`

Avantage : un seul moteur de calcul. Inconvénient : table énorme, indexation délicate (cf. migration `2021_02_11_172217_add_indexing_for_multiple_columns`), et chaque évolution métier ajoute une colonne nullable.

### 4.3 Stock multi-sites

- `variation_location_details(variation_id, location_id, qty_available)` = vérité unique du stock.
- Méthode comptable au choix : **FIFO / LIFO / AVCO** (`business.accounting_method`).
- `transaction_sell_lines_purchase_lines` = jointure traçant chaque vente vers les lots d'achat consommés (essentiel pour le coût réel et les retours).

### 4.4 Caisse

- `cash_registers` ouverts/fermés par caissier.
- `cash_denominations` (ajoutées en 2022) → fond de caisse par coupures (configurable 1000/2000/5000/10000 FCFA).
- `cash_register_transactions` = chaque mouvement.

---

## 5. Moyens de paiement (état actuel)

`App\Utils\Util::payment_types()` :

```php
$payment_types = [
    'cash' => __('lang_v1.cash'),
    'card' => __('lang_v1.card'),
    'cheque' => __('lang_v1.cheque'),
    'bank_transfer' => __('lang_v1.bank_transfer'),
    'other' => __('lang_v1.other'),
];
$payment_types['custom_pay_1..7'] = ... // étiquettes libres
```

Seuls 5 modes natifs + **7 emplacements custom** que l'on peut renommer (« MTN MoMo », « Orange Money », « Express Union », « Wave ») — **mais qui restent de simples étiquettes** : aucune intégration API, aucun callback de confirmation, aucune réconciliation automatique. Saisie manuelle obligatoire.

Les passerelles `Stripe`, `PayPal`, `Paystack`, `Pesapal`, `Razorpay`, `MyFatoorah` servent la **facturation distante** (lien `/pay/{token}`), **pas le POS terminal**.

---

## 6. POS, ventes & flux caissier

`SellPosController` (3 033 lignes) gère :

- Mode AJAX rapide (recherche produit / scan code-barres).
- Client *walk-in* par défaut (`Default Customer`).
- Brouillon ↔ Devis ↔ Facture finale.
- Paiements multiples sur une même vente.
- Lien public d'invoice (signed URL) pour règlement à distance.
- Service staff (restau/coiffure/garage) avec timer.
- Impression directe (CUPS / printer driver via plugin) + ESC/POS via vues `receipts`.
- *Express checkout* et *offline mode* (cache navigateur + sync).

Vues POS : `resources/views/sale_pos/{create,edit,product_row,partials,receipts}.blade.php`.

---

## 7. Comptabilité & rapports

- Plan de comptes via `Account` + `AccountType` + `AccountTransaction` (mini-grand-livre interne, **pas un vrai ERP**).
- Méthode comptable de stock : FIFO / LIFO / AVCO.
- 4 105 lignes de `ReportController` : ventes, achats, stock, profit/perte, payés/impayés, taxes, registres, agents commerciaux, clients/fournisseurs.
- Module séparé `Accounting` (référencé mais code absent — voir §3.4) pour la comptabilité avancée.

> **Verdict** : `Boutik` ne produit **pas** : DSF, États 901/902, déclarations TVA mensuelles, factures normalisées DGI, intégration FNE/SYDONIA, format PCG OHADA. Voir §10.

---

## 8. Internationalisation

- 18 langues présentes dans `lang/` dont **`fr`** complet.
- Champ `language` par utilisateur + middleware `Language`.
- Le français est **bien couvert** mais avec des termes tunisiens / nord-africains (« Wilaya » pour state, « TPS » pour taxe) qu'il faudra adapter au vocabulaire camerounais (« Région », « TVA », « Centime »).

---

## 9. Sécurité, robustesse & DX

| Point | État | Commentaire |
|---|---|---|
| CSRF | ✅ | Middleware Laravel actif |
| XSS | ⚠️ | Quelques `{!! !!}` à auditer dans Blade |
| SQL injection | ✅ | Eloquent + `DB::raw` paramétré |
| Mass assignment | ⚠️ | `protected $guarded = ['id']` ouvre tous les autres champs |
| Tests | ❌ | `tests/` quasi-vide pour 17k lignes critiques |
| Logs | ✅ | Daily rotation + `arcanedev/log-viewer` |
| Backups | ✅ | `spatie/laravel-backup` (S3 / Dropbox) |
| Debugbar prod | ⚠️ | OK si `APP_DEBUG=false` strict |
| Activity log | ✅ | `spatie/activitylog` |
| Captcha | ✅ | Google reCAPTCHA optionnel |
| Disposable email | ✅ | `propaganistas/laravel-disposable-email` |
| Rate limiting | ⚠️ | Seulement `throttle:api` standard, durcir login + `/pay/{token}` |
| Versioning API | ❌ | Pas de `/v1/`, bloquant si SDK partenaires |

---

## 10. Écarts par rapport à un POS *réellement camerounais*

| Exigence Cameroun | État `Boutik` | Action requise |
|---|---|---|
| **Devise FCFA / XAF** | ❌ Absente du seeder `CurrenciesTableSeeder.php` | Patch seeder : insérer XAF (symbole `FCFA`, milliers `' '`, `precision = 0`) |
| **Fuseau Africa/Douala** | ❌ Default `Asia/Kolkata` | Forcer `APP_TIMEZONE=Africa/Douala` + valeur par défaut migration |
| **TVA 19,25 %** | ⚠️ Tables génériques `tax_rates` / `group_sub_taxes` | Seeder TVA 19,25 %, AIR 5,5/2,2/1,1 %, Précompte achats, droit d'accises, TSPP carburants |
| **Retenues à la source / précompte** | ❌ | Étendre `payment_types` + workflow d'attestation de retenue |
| **Facture normalisée DGI Cameroun (FNE)** | ❌ `invoice_scheme` libre, QR code générique | **Module dédié connecteur FNE / e-Facture DGI** : signature, NIU, QR DGI, archivage 10 ans. *Bloquant* pour les contribuables BIC/IS soumis à la facture normalisée |
| **MTN MoMo Collections API** | ❌ | SDK MTN MoMo (Collection RequestToPay + Disbursement, callbacks IPN, table `momo_transactions`) |
| **Orange Money Cameroun** | ❌ | Web Payment v2 + Money Web Payment ; OAuth2 + IPN |
| **Express Union, YUP, Wave (CM), Camtel Money** | ❌ | Wrapper unifié *PaymentGateway* (Strategy) |
| **Numéro NIU (contribuable)** | ⚠️ Champs `tax_number_1/2` réutilisables | Validation regex format NIU CM (`P/M + 12 chiffres`) |
| **CNPS / déclaration sociale** | ❌ | Hors scope POS — module Accounting/HRM |
| **OHADA SYSCOHADA révisé (PCG)** | ❌ | Seeder PCG OHADA + comptes 411/401/701/607/445… |
| **Affichage prix en FCFA (sans décimales)** | ⚠️ `currency_precision` setting depuis 2022 | Forcer `0` à l'install Cameroun |
| **Imprimantes thermiques 80 mm BLE/USB** | ✅ | RAS, tester EPSON TM-T20 et Xprinter répandus |
| **Mode hors-ligne réel** | ⚠️ Cache navigateur | Pas un PWA service-worker complet, à renforcer |
| **SMS notification clients** | ⚠️ Twilio (cher en CM) | Brancher Nexah, BulkSmsCM, Smobilpay SMS |

---

## 11. Recommandations stratégiques (par ordre de priorité)

### P0 — Avant tout déploiement client

1. **Régler la question de licence** UltimatePOS (CodeCanyon).
2. **Désactiver/restaurer les modules** absents pour que l'app boote.
3. **Localisation socle Cameroun** : XAF, `Africa/Douala`, TVA 19,25 %, vocabulaire FR-CM.
4. **Sécuriser la mass assignment** sur `Transaction`, `Product`, `Contact`.
5. **Forcer un Global Scope `business_id`** sur tous les modèles tenant-aware.

### P1 — Mois 1 à 3

6. Module **Mobile Money** unifié (Strategy) : MTN + Orange + Express Union + Smobilpay.
7. Module **FNE / Facture normalisée DGI**.
8. **Plan comptable OHADA-SYSCOHADA révisé**.
9. **Refactor TransactionUtil** en *Domain Services* (`Pricing/`, `Tax/`, `Inventory/`, `Payment/`, `Accounting/`).
10. **Couverture de tests** Feature sur le flux POS critique.

### P2 — Trimestre 2

11. Vrai **PWA offline-first** (Service Worker + IndexedDB + queue de sync).
12. **API v1 versionnée** + Scribe doc.
13. Connecteurs locaux : Nexah/BulkSmsCM (SMS), Yango/Glovo (livraison).
14. **Tableau de bord BI** patron : ventes/caissier, marge brute/catégorie, rotation stock.
15. **Anti-fraude caisse** : verrouillage post-clôture, double-validation remises élevées.

### P3 — Long terme

16. Migration vers **Filament v3** ou **Inertia.js + Vue/React**.
17. **Docker Compose officiel** + CI/CD (GitHub Actions) + observabilité (Telescope/Pulse/Sentry).
18. Marketplace de modules Artistik.

---

## 12. Synthèse en 5 points

1. **Boutik = UltimatePOS rebrandé**, Laravel 9 + nwidart-modules + AdminLTE. Solide produit générique, mais **non camerounais en l'état**.
2. **Architecture** : monolithe modulaire multi-tenant *par session* avec god-classes (`TransactionUtil` 6.5k lignes). Dette technique élevée mais maîtrisable.
3. **Modules avancés (`Modules/`) absents du dépôt** — anomalie bloquante à régler avant déploiement.
4. **Aucune intégration native** Mobile Money CM, FCFA, TVA Cameroun, OHADA, FNE/DGI.
5. **Trois chantiers indispensables** pour tenir la promesse marketing : (a) localisation FCFA/TVA/OHADA, (b) Mobile Money réel, (c) facture normalisée DGI.
