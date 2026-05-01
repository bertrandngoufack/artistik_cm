<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Services\AcademicYearService;

class ExamModel extends Model
{
    protected $table = 'exams';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'exam_type', 'class_id', 'exam_date', 'total_marks', 'coefficient', 'status'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    protected $academicYearService;

    public function __construct()
    {
        parent::__construct();
        $this->academicYearService = new AcademicYearService();
    }

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'exam_type' => 'required|in_list[CONTINUOUS,MIDTERM,FINAL,COMPETITIVE]',
        'class_id' => 'required|integer',
        'exam_date' => 'required|valid_date',
        'total_marks' => 'required|numeric|greater_than[0]|less_than_equal_to[20]',
        'coefficient' => 'required|numeric|greater_than[0]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getRecentExams($limit = 10)
    {
        return $this->select('exams.*, classes.name as class_name')
                   ->join('classes', 'classes.id = exams.class_id', 'left')
                   ->orderBy('exams.exam_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getRecentExamsByAcademicYear($limit = 10, $academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear(); // Année par défaut
        }
        
        return $this->select('exams.*, classes.name as class_name')
                   ->join('classes', 'classes.id = exams.class_id', 'left')
                   ->where('exams.academic_year', $academicYear)
                   ->orderBy('exams.exam_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getExamsByClass($classId)
    {
        return $this->select('exams.*')
                   ->where('exams.class_id', $classId)
                   ->orderBy('exams.exam_date', 'DESC')
                   ->findAll();
    }

    public function getCompletedExams()
    {
        return $this->where('status', 'COMPLETED')
                   ->orderBy('exam_date', 'DESC')
                   ->findAll();
    }

    public function getExamsPaginated($perPage = 20)
    {
        return $this->select('exams.*, classes.name as class_name')
                   ->join('classes', 'classes.id = exams.class_id', 'left')
                   ->orderBy('exams.exam_date', 'DESC')
                   ->paginate($perPage);
    }

    public function getExamsPaginatedByAcademicYear($academicYear = null, $perPage = 20)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear(); // Année par défaut
        }
        
        return $this->select('exams.*, classes.name as class_name')
                   ->join('classes', 'classes.id = exams.class_id', 'left')
                   ->where('exams.academic_year', $academicYear)
                   ->orderBy('exams.exam_date', 'DESC')
                   ->paginate($perPage);
    }

    public function getExamsPager()
    {
        return $this->pager;
    }

    public function getExamWithDetails($id)
    {
        return $this->select('exams.*, classes.name as class_name')
                   ->join('classes', 'classes.id = exams.class_id', 'left')
                   ->where('exams.id', $id)
                   ->first();
    }

    public function getExamStats()
    {
        $totalExams = $this->countAllResults();
        $completedExams = $this->where('status', 'COMPLETED')->countAllResults();
        $scheduledExams = $this->where('status', 'SCHEDULED')->countAllResults();
        
        return [
            'totalExams' => $totalExams,
            'completedExams' => $completedExams,
            'scheduledExams' => $scheduledExams,
            'totalGrades' => $this->getTotalGrades(),
            'averageScore' => $this->getAverageScore(),
            'passRate' => $this->getPassRate()
        ];
    }

    public function getExamStatsByAcademicYear($academicYear = null)
    {
        if (!$academicYear) {
            $academicYear = $this->academicYearService->getCurrentAcademicYear(); // Année par défaut
        }
        
        $totalExams = $this->where('academic_year', $academicYear)->countAllResults();
        $completedExams = $this->where('academic_year', $academicYear)->where('status', 'COMPLETED')->countAllResults();
        $scheduledExams = $this->where('academic_year', $academicYear)->where('status', 'SCHEDULED')->countAllResults();
        
        return [
            'totalExams' => $totalExams,
            'completedExams' => $completedExams,
            'scheduledExams' => $scheduledExams,
            'totalGrades' => $this->getTotalGradesByAcademicYear($academicYear),
            'averageScore' => $this->getAverageScoreByAcademicYear($academicYear),
            'passRate' => $this->getPassRateByAcademicYear($academicYear)
        ];
    }

    private function getTotalGrades()
    {
        $db = \Config\Database::connect();
        $result = $db->table('grades')->countAllResults();
        return $result;
    }

    private function getAverageScore()
    {
        $db = \Config\Database::connect();
        $result = $db->table('grades')->selectAvg('marks_obtained')->get()->getRow();
        return $result ? round($result->marks_obtained, 2) : 0;
    }

    private function getPassRate()
    {
        $db = \Config\Database::connect();
        $total = $db->table('grades')->countAllResults();
        $passed = $db->table('grades')->where('marks_obtained >=', 10)->countAllResults();
        
        return $total > 0 ? round(($passed / $total) * 100, 1) : 0;
    }

    private function getTotalGradesByAcademicYear($academicYear)
    {
        $db = \Config\Database::connect();
        $result = $db->table('grades')
                    ->join('exams', 'exams.id = grades.exam_id')
                    ->where('exams.academic_year', $academicYear)
                    ->countAllResults();
        return $result;
    }

    private function getAverageScoreByAcademicYear($academicYear)
    {
        $db = \Config\Database::connect();
        $result = $db->table('grades')
                    ->selectAvg('grades.marks_obtained')
                    ->join('exams', 'exams.id = grades.exam_id')
                    ->where('exams.academic_year', $academicYear)
                    ->get()->getRow();
        return $result ? round($result->marks_obtained, 2) : 0;
    }

    private function getPassRateByAcademicYear($academicYear)
    {
        $db = \Config\Database::connect();
        $totalGrades = $db->table('grades')
                         ->join('exams', 'exams.id = grades.exam_id')
                         ->where('exams.academic_year', $academicYear)
                         ->countAllResults();
        
        $passedGrades = $db->table('grades')
                          ->join('exams', 'exams.id = grades.exam_id')
                          ->where('exams.academic_year', $academicYear)
                          ->where('grades.marks_obtained >=', 10)
                          ->countAllResults();
        
        return $totalGrades > 0 ? round(($passedGrades / $totalGrades) * 100, 1) : 0;
    }

    /**
     * Obtenir les statistiques des examens
     */
    public function getExamStatistics()
    {
        return [
            'total' => $this->countAllResults(),
            'by_type' => $this->select('exam_type, COUNT(*) as count')
                            ->groupBy('exam_type')
                            ->findAll(),
            'by_status' => $this->select('status, COUNT(*) as count')
                              ->groupBy('status')
                              ->findAll(),
            'by_class' => $this->select('classes.name as class_name, COUNT(*) as count')
                             ->join('classes', 'classes.id = exams.class_id')
                             ->groupBy('classes.id')
                             ->orderBy('count', 'DESC')
                             ->findAll(),
            'recent_exams' => $this->select('exams.*, classes.name as class_name')
                                 ->join('classes', 'classes.id = exams.class_id')
                                 ->orderBy('exams.exam_date', 'DESC')
                                 ->limit(5)
                                 ->findAll()
        ];
    }
}




