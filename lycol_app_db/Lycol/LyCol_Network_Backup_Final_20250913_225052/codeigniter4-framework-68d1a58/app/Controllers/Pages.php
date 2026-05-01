<?php

namespace App\Controllers;

class Pages extends BaseController
{
    public function about()
    {
        $data = [
            'title' => 'À propos - LyCol',
            'content' => 'LyCol est une solution de gestion scolaire complète adaptée au système éducatif camerounais.'
        ];

        return view('pages/about', $data);
    }

    public function contact()
    {
        $data = [
            'title' => 'Contact - LyCol',
            'content' => 'Contactez-nous pour plus d\'informations sur LyCol.'
        ];

        return view('pages/contact', $data);
    }

    public function help()
    {
        $data = [
            'title' => 'Aide - LyCol',
            'content' => 'Centre d\'aide et de support pour LyCol.'
        ];

        return view('pages/help', $data);
    }

    public function privacy()
    {
        $data = [
            'title' => 'Confidentialité - LyCol',
            'content' => 'Politique de confidentialité de LyCol.'
        ];

        return view('pages/privacy', $data);
    }

    public function terms()
    {
        $data = [
            'title' => 'Conditions d\'utilisation - LyCol',
            'content' => 'Conditions d\'utilisation de LyCol.'
        ];

        return view('pages/terms', $data);
    }
}




