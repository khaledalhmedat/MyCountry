<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\EmployeeRepositoryInterface;

class DashboardController extends Controller
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
        $this->middleware('auth:sanctum');
    }

    
    public function updateComplaintStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = Auth::user();
            
            $updatedComplaint = $this->employeeRepository->updateComplaintStatus(
                $id, 
                $request->status, 
                $employee->id
            );
            
            if (!$updatedComplaint) {
                return response()->json([
                    'success' => false,
                    'message' => 'الشكوى غير موجودة'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث الحالة بنجاح',
                'data' => $updatedComplaint
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function addComplaintNotes(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|min:3'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'خطأ في البيانات',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $employee = Auth::user();
            
            $updatedComplaint = $this->employeeRepository->addComplaintNotes(
                $id, 
                $request->notes, 
                $employee->id
            );
            
            if (!$updatedComplaint) {
                return response()->json([
                    'success' => false,
                    'message' => 'الشكوى غير موجودة'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة الملاحظات بنجاح',
                'data' => $updatedComplaint
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

      public function getEmployeeComplaints(Request $request)
    {
        try {
            $employee = Auth::user();
            
            if (!$employee->type_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'لم يتم تعيين نوع شكاوى لهذا الموظف'
                ], 400);
            }
            
            $complaints = $this->employeeRepository->getComplaintsByEmployeeType($employee->id);
            
            $stats = [
                'total' => $complaints->count(),
                'new' => $complaints->where('status', 'new')->count(),
                'in_progress' => $complaints->where('status', 'in_progress')->count(),
                'resolved' => $complaints->where('status', 'resolved')->count(),
                'closed' => $complaints->where('status', 'closed')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'complaints' => $complaints,
                    'statistics' => $stats,
                    'employee_type' => $employee->type_id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء جلب الشكاوى',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}