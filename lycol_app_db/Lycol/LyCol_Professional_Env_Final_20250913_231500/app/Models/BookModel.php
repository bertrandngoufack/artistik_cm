<?php

namespace App\Models;

use CodeIgniter\Model;

class BookModel extends Model
{
    protected $table = 'books';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'title', 'author', 'isbn', 'category', 'total_copies', 'available_copies', 'location', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title' => 'required|min_length[2]|max_length[200]',
        'author' => 'required|min_length[2]|max_length[100]',
        'isbn' => 'permit_empty|max_length[20]',
        'total_copies' => 'required|integer|greater_than[0]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getBooksPaginated($page = 1, $perPage = 20)
    {
        return $this->where('is_active', 1)
                   ->orderBy('title', 'ASC')
                   ->findAll();
    }

    public function getBooksPager()
    {
        return null;
    }

    public function getAvailableBooks()
    {
        return $this->where('is_active', 1)
                   ->where('available_copies >', 0)
                   ->orderBy('title', 'ASC')
                   ->findAll();
    }

    public function getBookStats()
    {
        return [
            'total' => $this->where('is_active', 1)->countAllResults(),
            'total_copies' => $this->select('SUM(total_copies) as total')->where('is_active', 1)->first()['total'] ?? 0
        ];
    }
}




