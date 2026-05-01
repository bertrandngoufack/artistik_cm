<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsenceModel extends Model
{
    protected $table = 'absences';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'student_id', 'date', 'reason', 'justified', 'created_by'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'student_id' => 'required|integer',
        'date' => 'required|valid_date',
        'reason' => 'required|max_length[500]',
        'justified' => 'permit_empty|in_list[0,1]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getAbsencesPaginated($page = 1, $perPage = 20)
    {
        return $this->select('absences.*, students.first_name, students.last_name, students.matricule, classes.name as class_name')
                   ->join('students', 'students.id = absences.student_id')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->orderBy('absences.date', 'DESC')
                   ->paginate($perPage, 'default', $page);
    }

    public function getAbsencesPager()
    {
        return $this->pager;
    }

    public function getRecentAbsences($limit = 10)
    {
        return $this->select('absences.*, students.first_name, students.last_name, students.matricule')
                   ->join('students', 'students.id = absences.student_id')
                   ->orderBy('absences.date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getAbsencesByStudent($studentId)
    {
        return $this->where('student_id', $studentId)
                   ->orderBy('date', 'DESC')
                   ->findAll();
    }

    public function getAbsenceStats()
    {
        return [
            'total' => $this->countAllResults(),
            'this_month' => $this->where('MONTH(date)', date('m'))
                                ->where('YEAR(date)', date('Y'))
                                ->countAllResults(),
            'by_justification' => $this->select('justified, COUNT(*) as count')
                                ->groupBy('justified')
                                ->findAll(),
            'by_duration' => $this->select('period, COUNT(*) as count')
                                ->groupBy('period')
                                ->findAll()
        ];
    }

    public function createAbsence($data)
    {
        return $this->insert($data);
    }

    /**
     * Obtenir la tendance mensuelle des absences
     */
    public function getMonthlyTrend()
    {
        return $this->select('YEAR(date) as year, MONTH(date) as month, COUNT(*) as count')
                   ->groupBy('YEAR(date), MONTH(date)')
                   ->orderBy('year', 'DESC')
                   ->orderBy('month', 'DESC')
                   ->limit(12)
                   ->findAll();
    }

    /**
     * Obtenir les absences par classe
     */
    public function getAbsencesByClass()
    {
        return $this->select('classes.name as class_name, COUNT(*) as count')
                   ->join('students', 'students.id = absences.student_id')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->groupBy('classes.id')
                   ->orderBy('count', 'DESC')
                   ->findAll();
    }

    /**
     * Obtenir le taux d'absences justifiées
     */
    public function getJustifiedAbsenceRate()
    {
        $total = $this->countAllResults();
        if ($total == 0) {
            return 0;
        }
        
        $justified = $this->where('justified', 1)->countAllResults();
        return round(($justified / $total) * 100, 2);
    }

}




