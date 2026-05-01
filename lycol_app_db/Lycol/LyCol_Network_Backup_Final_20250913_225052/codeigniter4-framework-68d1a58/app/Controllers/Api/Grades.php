<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\GradeModel;

class Grades extends BaseController
{
    protected $gradeModel;

    public function __construct()
    {
        $this->gradeModel = new GradeModel();
    }

    public function getByMatriculeAndBirthYear($matricule, $birthYear)
    {
        $grades = $this->gradeModel->getGradesByMatriculeAndBirthYear($matricule, $birthYear);
        
        return $this->response->setJSON([
            'success' => true,
            'data' => $grades
        ]);
    }
}




