<?php

namespace App\Models;

use CodeIgniter\Model;

class GradeModel extends Model
{
    protected $table = 'grades';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'exam_id', 'student_id', 'subject_id', 'marks_obtained', 'remarks'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'exam_id' => 'required|integer',
        'student_id' => 'required|integer',
        'subject_id' => 'required|integer',
        'marks_obtained' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[20]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getGradesByExam($examId)
    {
        return $this->select('grades.*, students.first_name, students.last_name, students.matricule')
                   ->join('students', 'students.id = grades.student_id')
                   ->where('grades.exam_id', $examId)
                   ->orderBy('students.last_name', 'ASC')
                   ->findAll();
    }

    public function getGradesByExamPaginated($examId, $limit = 20, $offset = 0)
    {
        return $this->select('grades.*, students.first_name, students.last_name, students.matricule')
                   ->join('students', 'students.id = grades.student_id')
                   ->where('grades.exam_id', $examId)
                   ->orderBy('students.last_name', 'ASC')
                   ->limit($limit)
                   ->offset($offset)
                   ->findAll();
    }

    public function getTotalGradesByExam($examId)
    {
        return $this->where('exam_id', $examId)->countAllResults();
    }

    public function getGradesByStudent($studentId)
    {
        return $this->select('grades.*, exams.name as exam_name, subjects.name as subject_name, subjects.coefficient')
                   ->join('exams', 'exams.id = grades.exam_id')
                   ->join('subjects', 'subjects.id = grades.subject_id')
                   ->where('grades.student_id', $studentId)
                   ->orderBy('exams.exam_date', 'DESC')
                   ->findAll();
    }

    public function getAverageScores()
    {
        return $this->select('subjects.name, subjects.coefficient, AVG(grades.marks_obtained) as average_score, 
                             MAX(grades.marks_obtained) as max_score, MIN(grades.marks_obtained) as min_score,
                             STDDEV(grades.marks_obtained) as std_deviation,
                             COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                             COUNT(*) as total')
                   ->join('exams', 'exams.id = grades.exam_id')
                   ->join('subjects', 'subjects.id = grades.subject_id')
                   ->groupBy('subjects.id')
                   ->orderBy('average_score', 'DESC')
                   ->findAll();
    }

    public function getPassRates()
    {
        return $this->select('subjects.name, COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed, COUNT(*) as total')
                   ->join('exams', 'exams.id = grades.exam_id')
                   ->join('subjects', 'subjects.id = grades.subject_id')
                   ->groupBy('subjects.id')
                   ->findAll();
    }

    public function getTopStudents($limit = 10)
    {
        return $this->select('students.id, students.first_name, students.last_name, students.matricule, 
                             classes.name as class_name,
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(grades.id) as exam_count')
                   ->join('students', 'students.id = grades.student_id')
                   ->join('classes', 'classes.id = students.current_class_id', 'left')
                   ->groupBy('students.id')
                   ->orderBy('average_score', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getGradeStats()
    {
        $total = $this->countAllResults();
        $average = $this->select('AVG(marks_obtained) as avg')->first()['avg'] ?? 0;
        $passed = $this->select('COUNT(CASE WHEN marks_obtained >= 10 THEN 1 END) as passed')->first()['passed'] ?? 0;
        
        return [
            'total' => $total,
            'average_score' => round($average, 2),
            'pass_rate' => $total > 0 ? round(($passed / $total) * 100, 1) : 0,
            'passed' => $passed,
            'failed' => $total - $passed
        ];
    }

    public function calculateStudentAverage($studentId, $period = null)
    {
        $query = $this->select('grades.marks_obtained, subjects.coefficient')
                     ->join('exams', 'exams.id = grades.exam_id')
                     ->join('subjects', 'subjects.id = grades.subject_id')
                     ->where('grades.student_id', $studentId);

        if ($period) {
            $query->where('exams.exam_date >=', $period['start'])
                  ->where('exams.exam_date <=', $period['end']);
        }

        $grades = $query->findAll();
        
        if (empty($grades)) {
            return 0;
        }

        $totalWeightedScore = 0;
        $totalCoefficient = 0;

        foreach ($grades as $grade) {
            $coefficient = $grade['coefficient'] ?? 1;
            $totalWeightedScore += $grade['marks_obtained'] * $coefficient;
            $totalCoefficient += $coefficient;
        }

        return $totalCoefficient > 0 ? round($totalWeightedScore / $totalCoefficient, 2) : 0;
    }

    public function validateGrade($marks, $totalMarks = 20)
    {
        // Validation stricte des notes (0-20)
        if ($marks < 0 || $marks > $totalMarks) {
            return false;
        }

        // Validation que la note est un nombre valide
        if (!is_numeric($marks)) {
            return false;
        }

        return true;
    }

    public function calculatePercentage($marks, $totalMarks = 20)
    {
        return $totalMarks > 0 ? round(($marks / $totalMarks) * 100, 2) : 0;
    }

    public function getStudentRanking($studentId, $classId = null)
    {
        $query = $this->select('students.id, AVG(grades.marks_obtained) as average_score')
                     ->join('students', 'students.id = grades.student_id')
                     ->groupBy('students.id')
                     ->orderBy('average_score', 'DESC');

        if ($classId) {
            $query->where('students.current_class_id', $classId);
        }

        $rankings = $query->findAll();
        
        foreach ($rankings as $index => $ranking) {
            if ($ranking['id'] == $studentId) {
                return $index + 1;
            }
        }

        return null;
    }

    public function getPerformanceByClass()
    {
        return $this->select('classes.name as class_name, 
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                             COUNT(*) as total')
                   ->join('students', 'students.id = grades.student_id')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->groupBy('classes.id')
                   ->orderBy('average_score', 'DESC')
                   ->findAll();
    }

    public function getPerformanceByGender()
    {
        return $this->select('students.gender, 
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                             COUNT(*) as total')
                   ->join('students', 'students.id = grades.student_id')
                   ->groupBy('students.gender')
                   ->orderBy('average_score', 'DESC')
                   ->findAll();
    }

    public function getBestClass()
    {
        return $this->select('classes.name as class_name, 
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                             COUNT(*) as total,
                             ROUND((COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) / COUNT(*)) * 100, 1) as pass_rate')
                   ->join('students', 'students.id = grades.student_id')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->groupBy('classes.id')
                   ->orderBy('average_score', 'DESC')
                   ->limit(1)
                   ->first();
    }

    public function getTopClasses($limit = 5)
    {
        return $this->select('classes.name as class_name, 
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                             COUNT(*) as total,
                             ROUND((COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) / COUNT(*)) * 100, 1) as pass_rate')
                   ->join('students', 'students.id = grades.student_id')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->groupBy('classes.id')
                   ->orderBy('average_score', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getPerformanceBySubject()
    {
        return $this->select('subjects.name as subject_name, 
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) as passed,
                             COUNT(*) as total')
                   ->join('subjects', 'subjects.id = grades.subject_id')
                   ->groupBy('subjects.id')
                   ->orderBy('average_score', 'DESC')
                   ->findAll();
    }

    public function getAverageScoresForChart()
    {
        return $this->select('subjects.name, AVG(grades.marks_obtained) as average_score')
                   ->join('subjects', 'subjects.id = grades.subject_id')
                   ->groupBy('subjects.id')
                   ->orderBy('average_score', 'DESC')
                   ->limit(10)
                   ->findAll();
    }

    public function getPassRatesForChart()
    {
        return $this->select('subjects.name, 
                             ROUND((COUNT(CASE WHEN grades.marks_obtained >= 10 THEN 1 END) / COUNT(*)) * 100, 1) as pass_rate')
                   ->join('subjects', 'subjects.id = grades.subject_id')
                   ->groupBy('subjects.id')
                   ->orderBy('pass_rate', 'DESC')
                   ->limit(10)
                   ->findAll();
    }

    public function getPerformanceTrendForChart()
    {
        return $this->select('DATE(exams.exam_date) as exam_date, 
                             AVG(grades.marks_obtained) as average_score')
                   ->join('exams', 'exams.id = grades.exam_id')
                   ->groupBy('DATE(exams.exam_date)')
                   ->orderBy('exam_date', 'ASC')
                   ->limit(20)
                   ->findAll();
    }

    public function getPerformanceByGenderForChart()
    {
        return $this->select('students.gender, 
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(*) as total_students')
                   ->join('students', 'students.id = grades.student_id')
                   ->groupBy('students.gender')
                   ->orderBy('average_score', 'DESC')
                   ->findAll();
    }

    public function getTopClassesForChart()
    {
        return $this->select('classes.name as class_name, 
                             AVG(grades.marks_obtained) as average_score,
                             COUNT(*) as total_grades')
                   ->join('students', 'students.id = grades.student_id')
                   ->join('classes', 'classes.id = students.current_class_id')
                   ->groupBy('classes.id')
                   ->orderBy('average_score', 'DESC')
                   ->limit(10)
                   ->findAll();
    }

    /**
     * Obtenir les statistiques de performance
     */
    public function getPerformanceStatistics()
    {
        return [
            'overall_average' => $this->select('AVG(marks_obtained) as avg')->first()['avg'] ?? 0,
            'overall_pass_rate' => $this->getPassRate(),
            'by_subject' => $this->getPerformanceBySubject(),
            'by_class' => $this->getTopClasses(10),
            'by_gender' => $this->getPerformanceByGenderForChart(),
            'top_students' => $this->getTopStudents(10),
            'performance_trend' => $this->getPerformanceTrendForChart()
        ];
    }

    /**
     * Obtenir le taux de réussite global
     */
    private function getPassRate()
    {
        $total = $this->countAllResults();
        if ($total == 0) {
            return 0;
        }
        
        $passed = $this->where('marks_obtained >=', 10)->countAllResults();
        return round(($passed / $total) * 100, 1);
    }

    /**
     * Obtenir les notes récentes
     */
    public function getRecentGrades($limit = 5)
    {
        return $this->select('grades.*, students.first_name, students.last_name, students.matricule, subjects.name as subject_name')
                   ->join('students', 'students.id = grades.student_id')
                   ->join('subjects', 'subjects.id = grades.subject_id', 'left')
                   ->orderBy('grades.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }
}




