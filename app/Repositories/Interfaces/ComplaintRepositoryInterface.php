<?php

namespace App\Repositories\Interfaces;

interface ComplaintRepositoryInterface
{
    public function createComplaint(array $data);
    public function getUserComplaints($userId);
    public function findComplaint($id);
    public function updateComplaintStatus($id, $status);
    public function addNotesToComplaint($id, $notes);
}