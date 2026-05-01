<?php

namespace App\Controllers;

class Errors extends BaseController
{
    public function show404()
    {
        $data = [
            'title' => 'Page non trouvée',
            'message' => 'La page que vous recherchez n\'existe pas.'
        ];

        return view('errors/404', $data);
    }

    public function show403()
    {
        $data = [
            'title' => 'Accès interdit',
            'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.'
        ];

        return view('errors/403', $data);
    }

    public function show500()
    {
        $data = [
            'title' => 'Erreur serveur',
            'message' => 'Une erreur interne s\'est produite. Veuillez réessayer plus tard.'
        ];

        return view('errors/500', $data);
    }
}




