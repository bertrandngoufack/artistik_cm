<?php

namespace App\Models;

use CodeIgniter\Model;

class TemplateModel extends Model
{
    protected $table = 'message_templates';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name', 'subject', 'content', 'message_type', 'is_active'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[100]',
        'subject' => 'required|min_length[2]|max_length[200]',
        'content' => 'required|min_length[10]',
        'message_type' => 'required|in_list[EMAIL,SMS,WHATSAPP]'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getTemplatesPaginated($page = 1, $perPage = 20)
    {
        return $this->orderBy('name', 'ASC')
                   ->paginate($perPage, 'default', $page);
    }

    public function getTemplatesPager()
    {
        return $this->pager;
    }

    public function getActiveTemplates()
    {
        return $this->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }

    public function getTemplatesByType($type)
    {
        return $this->where('message_type', $type)
                   ->where('is_active', 1)
                   ->orderBy('name', 'ASC')
                   ->findAll();
    }
}




