<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\AbsenceModel;

class Absences extends BaseController
{
    protected $absenceModel;

    public function __construct()
    {
        $this->absenceModel = new AbsenceModel();
    }

    public function getByMatriculeAndBirthYear($matricule, $birthYear)
    {
        $absences = $this->absenceModel->getAbsencesByMatriculeAndBirthYear($matricule, $birthYear);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $absences
        ]);
    }
}




