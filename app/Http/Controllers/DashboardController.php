<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\EmployeeRepositoryInterface;
use App\Models\Complaint;

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
        
        $complaint = \App\Models\Complaint::find($id);
        
        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'الشكوى غير موجودة'
            ], 404);
        }
        
        $oldStatus = $complaint->status;
        
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
        
        $timestamp = now()->format('Y-m-d H:i');
        $statusChangeNote = "[{$timestamp}] {$employee->name}: ";
        $statusChangeNote .= "تم تغيير حالة الشكوى من '{$oldStatus}' إلى '{$request->status}'";
        
        $this->employeeRepository->addComplaintNotes(
            $id,
            $statusChangeNote,
            $employee->id
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحالة بنجاح',
            'data' => [
                'complaint' => $updatedComplaint,
                'change_log' => [
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'changed_by' => $employee->name,
                    'timestamp' => $timestamp
                ]
            ]
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



    public function updateComplaintStates(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'status' => 'required|in:new,in_progress,resolved,closed'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'خطأ في البيانات'
        ], 422);
    }

    try {
        $employee = Auth::user();
        $complaint = Complaint::find($id);
        
        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'الشكوى غير موجودة'
            ], 404);
        }
        
        if ($complaint->locked_by && $complaint->locked_by != $employee->id) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الشكوى قيد المعالجة من قبل موظف آخر'
            ]);
        }
        
        $oldStatus = $complaint->status;
        
        $complaint->locked_by = $employee->id;
        $complaint->status = $request->status;
        $complaint->save();
        
        if ($complaint->notes) {
            $complaint->notes .= "\n\n";
        }
        
        $timestamp = now()->format('Y-m-d H:i');
        $statusChangeLog = "[{$timestamp}] - {$employee->name}: ";
        $statusChangeLog .= "تم تغيير الحالة من '{$oldStatus}' إلى '{$request->status}'";
        
        $complaint->notes .= $statusChangeLog;
        $complaint->save();
                
        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الحالة بنجاح',
            'data' => [
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'change_log' => $statusChangeLog
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'حدث خطأ'
        ], 500);
    }
}
}