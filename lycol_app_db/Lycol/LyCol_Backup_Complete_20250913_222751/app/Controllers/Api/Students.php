<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\StudentModel;

class Students extends BaseController
{
    protected $studentModel;

    public function __construct()
    {
        $this->studentModel = new StudentModel();
    }

    public function getByMatriculeAndBirthYear($matricule, $birthYear)
    {
        $student = $this->studentModel->getStudentByMatriculeAndBirthYear($matricule, $birthYear);
        
        if (!$student) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Élève non trouvé'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $student
        ]);
    }
}




