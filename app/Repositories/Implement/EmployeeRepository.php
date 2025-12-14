<?php

namespace App\Repositories\implement;

use App\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use App\Models\Complaint;


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

      public function updateComplaintStatus($complaintId, $status, $employeeId)
    {
        $complaint = Complaint::find($complaintId);
        
        if (!$complaint) {
            return null;
        }
        
        // تحديث الحالة
        $complaint->status = $status;
        $complaint->save();
        
        return $complaint;
    }
    
    /**
     * إضافة ملاحظات للشكوى
     */
    public function addComplaintNotes($complaintId, $notes, $employeeId)
    {
        $complaint = Complaint::find($complaintId);
        
        if (!$complaint) {
            return null;
        }
        
        // الحصول على اسم الموظف
        $employee = Employee::find($employeeId);
        $employeeName = $employee ? $employee->name : 'موظف';
        
        // إضافة الملاحظات مع التاريخ واسم الموظف
        $currentNotes = $complaint->notes ? $complaint->notes . "\n\n" : "";
        $timestamp = now()->format('Y-m-d H:i');
        $newNote = "[{$timestamp}] - {$employeeName}: {$notes}";
        
        $complaint->notes = $currentNotes . $newNote;
        $complaint->save();
        
        return $complaint;
    }

     public function getComplaintsByEmployeeType($employeeId)
    {
        // الحصول على بيانات الموظف
        $employee = Employee::find($employeeId);
        
        if (!$employee || !$employee->type_id) {
            return collect(); // إرجاع مجموعة فارغة
        }
        
        // جلب الشكاوى حسب type_id الخاص بالموظف
        $complaints = Complaint::where('type_id', $employee->type_id)
            ->with(['user', 'type']) // تحميل بيانات المستخدم والنوع
            ->orderBy('created_at', 'desc') // ترتيب من الأحدث للأقدم
            ->get();
        
        return $complaints;
    }
}