<?php

namespace App\Models;

use CodeIgniter\Model;

class DisciplineModel extends Model
{
    protected $table = 'discipline_incidents';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'student_id', 'incident_date', 'description', 'sanction', 'recorded_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'student_id' => 'required|integer',
        'incident_date' => 'required|valid_date',
        'description' => 'required|max_length[1000]',
        'sanction' => 'required|max_length[500]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getIncidentsPaginated($page = 1, $perPage = 20)
    {
        return $this->select('discipline_incidents.*, students.first_name, students.last_name, students.matricule, classes.name as class_name')
                   ->join('students', 'students.id = discipline_incidents.student_id')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->orderBy('discipline_incidents.incident_date', 'DESC')
                   ->paginate($perPage, 'default', $page);
    }

    public function getIncidentsPager()
    {
        return $this->pager;
    }

    public function getDisciplineByStudent($studentId)
    {
        return $this->where('student_id', $studentId)
                   ->orderBy('incident_date', 'DESC')
                   ->findAll();
    }

    public function getDisciplineStats()
    {
        return [
            'total' => $this->countAllResults(),
            'this_month' => $this->where('MONTH(incident_date)', date('m'))
                                ->where('YEAR(incident_date)', date('Y'))
                                ->countAllResults()
        ];
    }
}




