<?php

namespace App\Repositories\Implement;

use App\Repositories\Interfaces\ComplaintRepositoryInterface;
use App\Models\Complaint;
use App\Models\Employee;

class ComplaintRepository implements ComplaintRepositoryInterface
{
    protected $model;

    public function __construct(Complaint $complaint)
    {
        $this->model = $complaint;
    }

    public function createComplaint(array $data)
    {
        return $this->model->create($data);
    }

    public function getUserComplaints($userId)
    {
        return $this->model->where('user_id', $userId)
            ->with('type')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function findComplaint($id)
    {
        return $this->model->with(['user', 'type'])->find($id);
    }

    

  

    
}