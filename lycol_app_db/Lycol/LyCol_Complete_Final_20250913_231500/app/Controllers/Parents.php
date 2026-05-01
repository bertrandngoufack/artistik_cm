<?php

namespace App\Controllers;

class Parents extends BaseController
{
    public function dashboard()
    {
        $data = [
            'title' => 'Espace Parents - LyCol'
        ];

        return view('parents/dashboard', $data);
    }

    public function grades()
    {
        $data = [
            'title' => 'Notes de l\'élève - LyCol'
        ];

        return view('parents/grades', $data);
    }

    public function absences()
    {
        $data = [
            'title' => 'Absences de l\'élève - LyCol'
        ];

        return view('parents/absences', $data);
    }

    public function payments()
    {
        $data = [
            'title' => 'Paiements - LyCol'
        ];

        return view('parents/payments', $data);
    }

    public function discipline()
    {
        $data = [
            'title' => 'Discipline - LyCol'
        ];

        return view('parents/discipline', $data);
    }

    public function profile()
    {
        $data = [
            'title' => 'Profil Parent - LyCol'
        ];

        return view('parents/profile', $data);
    }
}




