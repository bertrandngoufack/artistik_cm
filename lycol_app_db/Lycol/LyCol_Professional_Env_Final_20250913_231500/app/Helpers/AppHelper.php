<?php

/**
 * Helper pour l'application KISSAI SCHOOL
 * Gestion centralisée des paramètres d'apparence
 */

if (!function_exists('get_app_setting')) {
    /**
     * Récupérer un paramètre d'application
     */
    function get_app_setting($key, $default = null) {
        static $settings = null;
        
        if ($settings === null) {
            $settings = load_app_settings();
        }
        
        return $settings[$key] ?? $default;
    }
}

if (!function_exists('load_app_settings')) {
    /**
     * Charger tous les paramètres d'application depuis la base de données
     */
    function load_app_settings() {
        $cache = \Config\Services::cache();
        
        return $cache->remember('app_settings', function() {
            try {
                $db = \Config\Database::connect();
                
                if (!$db->tableExists('settings')) {
                    return get_default_app_settings();
                }
                
                $settings = $db->table('settings')
                    ->where('module', 'appearance')
                    ->get()
                    ->getResultArray();
                
                $result = get_default_app_settings();
                
                foreach ($settings as $setting) {
                    $result[$setting['setting_key']] = $setting['setting_value'];
                }
                
                return $result;
                
            } catch (\Exception $e) {
                log_message('error', 'Erreur dans load_app_settings: ' . $e->getMessage());
                return get_default_app_settings();
            }
        }, 300); // Cache 5 minutes
    }
}

if (!function_exists('get_default_app_settings')) {
    /**
     * Paramètres d'application par défaut
     */
    function get_default_app_settings() {
        return [
            'app_name' => 'KISSAI SCHOOL',
            'app_logo' => 'assets/images/logo.png',
            'app_favicon' => 'assets/images/favicon.ico',
            'primary_color' => '#3273dc',
            'secondary_color' => '#00d1b2',
            'app_description' => 'Système de gestion scolaire KISSAI SCHOOL',
            'app_keywords' => 'école, gestion, scolaire, KISSAI'
        ];
    }
}

if (!function_exists('app_name')) {
    /**
     * Récupérer le nom de l'application
     */
    function app_name() {
        return get_app_setting('app_name', 'KISSAI SCHOOL');
    }
}

if (!function_exists('app_logo')) {
    /**
     * Récupérer le chemin du logo
     */
    function app_logo() {
        return base_url(get_app_setting('app_logo', 'assets/images/logo.png'));
    }
}

if (!function_exists('app_favicon')) {
    /**
     * Récupérer le chemin du favicon
     */
    function app_favicon() {
        return base_url(get_app_setting('app_favicon', 'assets/images/favicon.ico'));
    }
}

if (!function_exists('primary_color')) {
    /**
     * Récupérer la couleur primaire
     */
    function primary_color() {
        return get_app_setting('primary_color', '#3273dc');
    }
}

if (!function_exists('secondary_color')) {
    /**
     * Récupérer la couleur secondaire
     */
    function secondary_color() {
        return get_app_setting('secondary_color', '#00d1b2');
    }
}

if (!function_exists('app_description')) {
    /**
     * Récupérer la description de l'application
     */
    function app_description() {
        return get_app_setting('app_description', 'Système de gestion scolaire KISSAI SCHOOL');
    }
}

if (!function_exists('clear_app_settings_cache')) {
    /**
     * Vider le cache des paramètres d'application
     */
    function clear_app_settings_cache() {
        $cache = \Config\Services::cache();
        $cache->delete('app_settings');
    }
}

if (!function_exists('get_css_variables')) {
    /**
     * Générer les variables CSS pour les couleurs
     */
    function get_css_variables() {
        $primary = primary_color();
        $secondary = secondary_color();
        
        return "
        :root {
            --primary-color: {$primary};
            --secondary-color: {$secondary};
        }
        ";
    }
}

if (!function_exists('render_app_meta')) {
    /**
     * Rendre les balises meta pour l'application
     */
    function render_app_meta() {
        $name = app_name();
        $description = app_description();
        $favicon = app_favicon();
        
        return "
        <title>{$name}</title>
        <meta name=\"description\" content=\"{$description}\">
        <link rel=\"icon\" type=\"image/x-icon\" href=\"{$favicon}\">
        <link rel=\"shortcut icon\" type=\"image/x-icon\" href=\"{$favicon}\">
        ";
    }
}

if (!function_exists('render_app_header')) {
    /**
     * Rendre l'en-tête de l'application avec logo et nom
     */
    function render_app_header() {
        $name = app_name();
        $logo = app_logo();
        
        return "
        <div class=\"app-header\" style=\"display: flex; align-items: center; gap: 10px;\">
            <img src=\"{$logo}\" alt=\"{$name}\" style=\"height: 40px;\">
            <h1 style=\"margin: 0; color: var(--primary-color);\">{$name}</h1>
        </div>
        ";
    }
}




