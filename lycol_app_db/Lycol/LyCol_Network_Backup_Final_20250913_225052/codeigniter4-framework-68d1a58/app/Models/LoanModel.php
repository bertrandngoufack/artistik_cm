<?php

namespace App\Models;

use CodeIgniter\Model;

class LoanModel extends Model
{
    protected $table = 'book_loans';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'book_id', 'member_id', 'member_type', 'loan_date', 'due_date', 'return_date', 'status', 'notes'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'book_id' => 'required|integer',
        'member_id' => 'required|integer',
        'member_type' => 'required|in_list[STUDENT,STAFF]',
        'loan_date' => 'required|valid_date',
        'due_date' => 'required|valid_date'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getLoansPaginated($page = 1, $perPage = 20)
    {
        return $this->select('book_loans.*, books.title as book_title, books.author')
                   ->join('books', 'books.id = book_loans.book_id')
                   ->orderBy('book_loans.loan_date', 'DESC')
                   ->findAll();
    }

    public function getLoansPager()
    {
        return null;
    }

    public function getRecentLoans($limit = 10)
    {
        return $this->select('book_loans.*, books.title as book_title')
                   ->join('books', 'books.id = book_loans.book_id')
                   ->orderBy('book_loans.loan_date', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getOverdueLoans()
    {
        return $this->select('book_loans.*, books.title as book_title')
                   ->join('books', 'books.id = book_loans.book_id')
                   ->where('book_loans.status', 'BORROWED')
                   ->where('book_loans.due_date <', date('Y-m-d'))
                   ->orderBy('book_loans.due_date', 'ASC')
                   ->findAll();
    }

    public function getMembers()
    {
        return $this->select('borrower_name, COUNT(*) as total_loans')
                   ->groupBy('borrower_name')
                   ->orderBy('total_loans', 'DESC')
                   ->findAll();
    }

    public function getLoanStats()
    {
        return [
            'total' => $this->countAllResults(),
            'active' => $this->where('status', 'BORROWED')->countAllResults(),
            'overdue' => $this->getOverdueLoans()->count()
        ];
    }
}




