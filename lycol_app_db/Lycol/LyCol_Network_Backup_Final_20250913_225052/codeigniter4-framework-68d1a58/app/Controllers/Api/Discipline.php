<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\DisciplineModel;

class Discipline extends BaseController
{
    protected $disciplineModel;

    public function __construct()
    {
        $this->disciplineModel = new DisciplineModel();
    }

    public function getByMatriculeAndBirthYear($matricule, $birthYear)
    {
        $discipline = $this->disciplineModel->getDisciplineByMatriculeAndBirthYear($matricule, $birthYear);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $discipline
        ]);
    }
}




