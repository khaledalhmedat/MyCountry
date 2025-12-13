<?php

namespace App\Repositories\implement;

use App\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    protected $model;

    public function __construct(Employee $employee)
    {
        $this->model = $employee;
    }

    public function createEmployee(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        return $this->model->create($data);
    }

    public function findEmployeeByEmail($email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function findEmployeeById($id)
    {
        return $this->model->with('type')->find($id);
    }

    public function updateEmployee($id, array $data)
    {
        $employee = $this->model->find($id);
        
        if (!$employee) {
            return null;
        }

        if (isset($data['password']) && $data['password']) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $employee->update($data);
        return $employee;
    }

    public function deleteEmployee($id)
    {
        $employee = $this->model->find($id);
        
        if ($employee) {
            return $employee->delete();
        }
        
        return false;
    }

    public function getAllEmployees()
    {
        return $this->model->with('type')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getEmployeesByType($typeId)
    {
        return $this->model->where('type_id', $typeId)
            ->with('type')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}