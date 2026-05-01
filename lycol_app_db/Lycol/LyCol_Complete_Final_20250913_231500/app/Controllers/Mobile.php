<?php

namespace App\Controllers;

class Mobile extends BaseController
{
    public function grades()
    {
        $data = [
            'title' => 'Notes - Interface Mobile'
        ];

        return view('mobile/grades', $data);
    }

    public function enterGrades($examId)
    {
        $data = [
            'title' => 'Saisie des Notes - Mobile',
            'exam_id' => $examId
        ];

        return view('mobile/enter_grades', $data);
    }

    public function storeGrades()
    {
        // Logique de sauvegarde des notes
        return redirect()->to('mobile/grades')->with('success', 'Notes enregistrées avec succès');
    }

    public function absences()
    {
        $data = [
            'title' => 'Absences - Interface Mobile'
        ];

        return view('mobile/absences', $data);
    }

    public function createAbsence()
    {
        $data = [
            'title' => 'Nouvelle Absence - Mobile'
        ];

        return view('mobile/create_absence', $data);
    }

    public function storeAbsence()
    {
        // Logique de sauvegarde des absences
        return redirect()->to('mobile/absences')->with('success', 'Absence enregistrée avec succès');
    }

    public function profile()
    {
        $data = [
            'title' => 'Profil - Interface Mobile'
        ];

        return view('mobile/profile', $data);
    }
}




