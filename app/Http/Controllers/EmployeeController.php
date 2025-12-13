<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Repositories\Interfaces\EmployeeRepositoryInterface;

class EmployeeController extends Controller
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    
    public function createEmployee(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:employees,email',
            'password' => 'required|string|min:8|confirmed',
            'type_id' => 'required|exists:types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات المدخلة غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employeeData = $request->only(['name', 'email', 'password', 'type_id']);
            $employee = $this->employeeRepository->createEmployee($employeeData);

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء حساب الموظف بنجاح',
                'data' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'type_id' => $employee->type_id,
                    'created_at' => $employee->created_at
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء حساب الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function getAllEmployees(Request $request)
    {
        try {
            $employees = $this->employeeRepository->getAllEmployees();

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الموظفين',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   
    public function getEmployee($id)
    {
        try {
            $employee = $this->employeeRepository->findEmployeeById($id);

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $employee
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function updateEmployee(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:employees,email,' . $id,
            'password' => 'sometimes|string|min:8|confirmed',
            'type_id' => 'sometimes|exists:types,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات المدخلة غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = $this->employeeRepository->updateEmployee($id, $request->all());

            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث بيانات الموظف بنجاح',
                'data' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'type_id' => $employee->type_id,
                    'updated_at' => $employee->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث بيانات الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function deleteEmployee($id)
    {
        try {
            $result = $this->employeeRepository->deleteEmployee($id);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'الموظف غير موجود'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الموظف بنجاح'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء حذف الموظف',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function getEmployeesByType($typeId)
    {
        try {
            $employees = $this->employeeRepository->getEmployeesByType($typeId);

            return response()->json([
                'success' => true,
                'data' => $employees
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب بيانات الموظفين',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}