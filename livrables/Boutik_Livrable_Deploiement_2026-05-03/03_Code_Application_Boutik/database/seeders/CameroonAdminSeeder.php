<?php

namespace Database\Seeders;

use App\Business;
use App\Currency;
use App\User;
use App\Utils\BusinessUtil;
use App\Utils\ModuleUtil;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * Seeder de création d'un commerçant + admin par défaut pour le Cameroun.
 *
 * Idempotent : si l'admin (username = boutik_admin) existe déjà, ne fait rien.
 *
 * Identifiants créés :
 *   Username : boutik_admin
 *   Password : Boutik@2026
 *   URL      : http://localhost:8080/boutik/login
 */
class CameroonAdminSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('username', 'boutik_admin')->exists()) {
            $this->command->info('CameroonAdminSeeder : compte boutik_admin déjà présent, rien à faire.');
            return;
        }

        DB::beginTransaction();

        try {
            // 1) Devise XAF (FCFA) — créée si absente
            $xaf = Currency::firstOrCreate(
                ['code' => 'XAF'],
                [
                    'country' => 'Cameroon',
                    'currency' => 'CFA Franc BEAC',
                    'symbol' => 'FCFA',
                    'thousand_separator' => ' ',
                    'decimal_separator' => '.',
                ]
            );

            // 2) Owner / Admin
            $owner_details = [
                'surname' => 'M.',
                'first_name' => 'Admin',
                'last_name' => 'Boutik',
                'username' => 'boutik_admin',
                'email' => 'admin@boutik.cm',
                'password' => 'Boutik@2026',
                'language' => 'fr',
            ];
            $user = User::create_user($owner_details);

            // 3) Business (Boutik Demo Cameroun)
            $businessUtil = new BusinessUtil();

            $business_details = [
                'name' => 'Boutik Demo Cameroun',
                'currency_id' => $xaf->id,
                'time_zone' => 'Africa/Douala',
                'fy_start_month' => 1,
                'accounting_method' => 'fifo',
                'tax_label_1' => 'TVA',
                'tax_number_1' => 'P000000000000',
                'tax_label_2' => null,
                'tax_number_2' => null,
                'owner_id' => $user->id,
                'enabled_modules' => [
                    'purchases', 'add_sale', 'pos_sale',
                    'stock_transfers', 'stock_adjustment', 'expenses',
                ],
            ];

            $business = $businessUtil->createNewBusiness($business_details);

            // 4) Lier user et business
            $user->business_id = $business->id;
            $user->save();

            // 5) Ressources par défaut (rôles, permissions, plan compte, etc.)
            $businessUtil->newBusinessDefaultResources($business->id, $user->id);

            // 6) Premier point de vente (siège)
            $business_location = [
                'name' => 'Siège Douala',
                'country' => 'Cameroon',
                'state' => 'Littoral',
                'city' => 'Douala',
                'zip_code' => '00237',
                'landmark' => 'Akwa, Boulevard de la Liberté',
                'website' => 'https://boutik.cm',
                'mobile' => '+237 6XX XXX XXX',
                'alternate_number' => null,
            ];
            $new_location = $businessUtil->addLocation($business->id, $business_location);

            Permission::create(['name' => 'location.' . $new_location->id]);

            // 7) Hook modules (notifications de bienvenue, etc.)
            $moduleUtil = new ModuleUtil();
            try {
                $moduleUtil->getModuleData('after_business_created', ['business' => $business]);
            } catch (\Throwable $e) {
                // Modules absents : on ignore.
            }

            DB::commit();

            $this->command->info('');
            $this->command->info('============================================================');
            $this->command->info('  COMPTE ADMIN BOUTIK CRÉÉ AVEC SUCCÈS');
            $this->command->info('============================================================');
            $this->command->info('  Entreprise   : Boutik Demo Cameroun');
            $this->command->info('  Devise       : XAF (FCFA)');
            $this->command->info('  Fuseau       : Africa/Douala');
            $this->command->info('  Site         : Siège Douala (Littoral)');
            $this->command->info('  ----------------------------------------------------------');
            $this->command->info('  Username     : boutik_admin');
            $this->command->info('  Password     : Boutik@2026');
            $this->command->info('  URL          : http://localhost:8080/boutik/login');
            $this->command->info('============================================================');
            $this->command->info('');
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error('CameroonAdminSeeder : ' . $e->getMessage());
            $this->command->error('Fichier : ' . $e->getFile() . ':' . $e->getLine());
            throw $e;
        }
    }
}
