<?php

namespace App\Repositories\Interfaces;

interface EmployeeRepositoryInterface
{
    public function createEmployee(array $data);
    public function findEmployeeByEmail($email);
    public function findEmployeeById($id);
    public function updateEmployee($id, array $data);
    public function deleteEmployee($id);
    public function getAllEmployees();
    public function getEmployeesByType($typeId);
}