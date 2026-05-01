<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'student_id', 'fee_type_id', 'amount_paid', 'payment_date', 
        'payment_method', 'reference_number', 'notes', 'academic_year', 
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'student_id' => 'required|integer',
        'fee_type_id' => 'required|integer',
        'amount_paid' => 'required|numeric|greater_than[0]',
        'payment_date' => 'required|valid_date',
        'payment_method' => 'required|in_list[CASH,CHECK,BANK_TRANSFER,MOBILE_MONEY]',
        'academic_year' => 'required|max_length[9]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getPaymentWithDetails($id)
    {
        return $this->select('payments.*, students.first_name, students.last_name, students.matricule, fee_types.name as fee_type_name, users.username as recorded_by_name')
                   ->join('students', 'students.id = payments.student_id')
                   ->join('fee_types', 'fee_types.id = payments.fee_type_id')
                   ->join('users', 'users.id = payments.recorded_by', 'left')
                   ->where('payments.id', $id)
                   ->first();
    }

    public function getPaymentsPaginated($page = 1, $perPage = 20)
    {
        return $this->select('payments.*, students.first_name, students.last_name, students.matricule, fee_types.name as fee_type_name')
                   ->join('students', 'students.id = payments.student_id')
                   ->join('fee_types', 'fee_types.id = payments.fee_type_id')
                   ->orderBy('payments.payment_date', 'DESC')
                   ->paginate($perPage, 'default', $page);
    }

    public function getPaymentsPager()
    {
        return $this->pager;
    }

    public function getRecentPayments($limit = 10)
    {
        return $this->select('payments.*, students.first_name, students.last_name, students.matricule, fee_types.name as fee_type_name')
                   ->join('students', 'students.id = payments.student_id')
                   ->join('fee_types', 'fee_types.id = payments.fee_type_id')
                   ->orderBy('payments.payment_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getPendingPayments()
    {
        return $this->select('payments.*, students.first_name, students.last_name, students.matricule, fee_types.name as fee_type_name')
                   ->join('students', 'students.id = payments.student_id')
                   ->join('fee_types', 'fee_types.id = payments.fee_type_id')
                   ->where('payments.status', 'PENDING')
                   ->orderBy('payments.payment_date', 'ASC')
                   ->findAll();
    }

    public function getPendingPaymentsCount()
    {
        return $this->where('payment_date <', date('Y-m-d'))->countAllResults();
    }

    public function getOverduePaymentsCount()
    {
        return $this->where('payment_date <', date('Y-m-d'))
                   ->countAllResults();
    }

    public function getTotalRevenue()
    {
        $result = $this->select('SUM(amount_paid) as total')
                      ->first();
        return $result['total'] ?? 0;
    }

    public function getPaidPaymentsCount()
    {
        return $this->countAllResults();
    }

    public function getRecentPaymentsWithDetails($limit = 5)
    {
        $builder = \Config\Database::connect()->table('payments');
        $builder->select('payments.*, CONCAT(students.first_name, " ", students.last_name) as student_name, fee_types.name as fee_type_name');
        $builder->join('students', 'students.id = payments.student_id', 'left');
        $builder->join('fee_types', 'fee_types.id = payments.fee_type_id', 'left');
        $builder->orderBy('payments.payment_date', 'DESC');
        $builder->limit($limit);
        
        return $builder->get()->getResultArray();
    }

    public function getMonthlyRevenue()
    {
        $result = $this->select('SUM(amount_paid) as total')
                      ->where('MONTH(payment_date)', date('m'))
                      ->where('YEAR(payment_date)', date('Y'))
                      ->first();
        return $result['total'] ?? 0;
    }

    public function getPaymentMethodDistribution()
    {
        return $this->select('payment_method, COUNT(*) as count, SUM(amount_paid) as total')
                   ->groupBy('payment_method')
                   ->findAll();
    }

    public function getRevenueStats($period = 'current_month')
    {
        $query = $this->select('DATE(payment_date) as date, SUM(amount_paid) as total');

        switch ($period) {
            case 'current_month':
                $query->where('MONTH(payment_date)', date('m'))
                      ->where('YEAR(payment_date)', date('Y'));
                break;
            case 'current_year':
                $query->where('YEAR(payment_date)', date('Y'));
                break;
            case 'last_month':
                $query->where('MONTH(payment_date)', date('m', strtotime('-1 month')))
                      ->where('YEAR(payment_date)', date('Y', strtotime('-1 month')));
                break;
        }

        return $query->groupBy('DATE(payment_date)')
                    ->orderBy('date', 'ASC')
                    ->findAll();
    }

    public function getPaymentMethodStats($period = 'current_month')
    {
        $query = $this->select('payment_method, COUNT(*) as count, SUM(amount_paid) as total');

        switch ($period) {
            case 'current_month':
                $query->where('MONTH(payment_date)', date('m'))
                      ->where('YEAR(payment_date)', date('Y'));
                break;
            case 'current_year':
                $query->where('YEAR(payment_date)', date('Y'));
                break;
        }

        return $query->groupBy('payment_method')
                    ->findAll();
    }

    public function getFeeTypeStats($period = 'current_month')
    {
        $query = $this->select('fee_types.name, COUNT(*) as count, SUM(payments.amount_paid) as total')
                     ->join('fee_types', 'fee_types.id = payments.fee_type_id');

        switch ($period) {
            case 'current_month':
                $query->where('MONTH(payments.payment_date)', date('m'))
                      ->where('YEAR(payments.payment_date)', date('Y'));
                break;
            case 'current_year':
                $query->where('YEAR(payments.payment_date)', date('Y'));
                break;
        }

        return $query->groupBy('fee_types.id')
                    ->orderBy('total', 'DESC')
                    ->findAll();
    }



    public function createPayment($data)
    {
        return $this->insert($data);
    }

    public function updatePayment($id, $data)
    {
        return $this->update($id, $data);
    }

    public function deletePayment($id)
    {
        return $this->delete($id);
    }

    /**
     * Obtenir la distribution des types de frais
     */
    public function getFeeTypeDistribution()
    {
        return $this->select('fee_types.name, COUNT(*) as count, SUM(payments.amount_paid) as total')
                   ->join('fee_types', 'fee_types.id = payments.fee_type_id')
                   ->groupBy('fee_types.id')
                   ->orderBy('total', 'DESC')
                   ->findAll();
    }

    /**
     * Obtenir les paiements en retard
     */
    public function getOutstandingPayments()
    {
        return $this->select('payments.*, students.first_name, students.last_name, students.matricule, fee_types.name as fee_type_name')
                   ->join('students', 'students.id = payments.student_id')
                   ->join('fee_types', 'fee_types.id = payments.fee_type_id')
                   ->where('payments.payment_date <', date('Y-m-d'))
                   ->orderBy('payments.payment_date', 'ASC')
                   ->findAll();
    }
}


