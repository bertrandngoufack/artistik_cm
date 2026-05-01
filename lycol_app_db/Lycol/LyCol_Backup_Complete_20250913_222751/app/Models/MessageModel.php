<?php

namespace App\Models;

use CodeIgniter\Model;

class MessageModel extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'title', 'content', 'recipient_type', 'recipient_ids', 'sender_id', 'status', 'sent_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'title' => 'required|min_length[2]|max_length[200]',
        'content' => 'required|min_length[10]',
        'recipient_type' => 'required|in_list[ALL,STUDENTS,PARENTS,STAFF,SPECIFIC]',
        'recipient_ids' => 'permit_empty',
        'sender_id' => 'required|integer'
    ];

    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    public function getMessagesPaginated($page = 1, $perPage = 20)
    {
        return $this->select('messages.*, users.username as sender_name')
                   ->join('users', 'users.id = messages.sender_id', 'left')
                   ->orderBy('messages.created_at', 'DESC')
                   ->paginate($perPage, 'default', $page);
    }

    public function getMessagesPager()
    {
        return $this->pager;
    }

    public function getRecentMessages($limit = 10)
    {
        return $this->select('messages.*, users.username as sender_name')
                   ->join('users', 'users.id = messages.sender_id', 'left')
                   ->orderBy('messages.created_at', 'DESC')
                   ->limit($limit)
                   ->findAll();
    }

    public function getSubscribers()
    {
        return $this->select('recipient_type, COUNT(*) as message_count')
                   ->groupBy('recipient_type')
                   ->orderBy('message_count', 'DESC')
                   ->findAll();
    }

    public function getMessageStats()
    {
        return [
            'total' => $this->countAllResults(),
            'sent' => $this->where('status', 'SENT')->countAllResults(),
            'pending' => $this->where('status', 'PENDING')->countAllResults(),
            'failed' => $this->where('status', 'FAILED')->countAllResults()
        ];
    }
}




