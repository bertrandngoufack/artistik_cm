<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeModel extends Model
{
    protected $table = 'fee_types';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'description', 'amount', 'is_active', 'due_date', 'academic_year_id'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'amount' => 'required|numeric|greater_than[0]',
        'due_date' => 'required|valid_date'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getActiveFeeTypes()
    {
        return $this->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getFeesPaginated($page = 1, $perPage = 20)
    {
        return $this->orderBy('name', 'ASC')
                   ->paginate($perPage, 'default', $page);
    }

    public function getFeesPager()
    {
        return $this->pager;
    }

    public function getFeeTypesByAcademicYear($academicYearId)
    {
        return $this->where('academic_year_id', $academicYearId)
                   ->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getFeeStats()
    {
        return [
            'total' => $this->where('is_active', 1)->countAllResults(),
            'total_amount' => $this->select('SUM(amount) as total')->where('is_active', 1)->first()['total'] ?? 0,
            'by_academic_year' => $this->select('academic_years.name, COUNT(*) as count, SUM(fee_types.amount) as total')
                                     ->join('academic_years', 'academic_years.id = fee_types.academic_year_id')
                                     ->where('fee_types.is_active', 1)
                                     ->groupBy('academic_years.id')
                                     ->findAll()
        ];
    }
}




