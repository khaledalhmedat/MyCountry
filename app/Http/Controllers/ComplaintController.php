<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Repositories\Interfaces\ComplaintRepositoryInterface;

class ComplaintController extends Controller
{
    protected $complaintRepository;

    public function __construct(ComplaintRepositoryInterface $complaintRepository)
    {
        $this->complaintRepository = $complaintRepository;
        $this->middleware('auth:sanctum')->except([]); 
    }

    
    public function submitComplaint(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'يجب تسجيل الدخول أولاً'
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:types,id',
            'destination' => 'required|string|max:255',
            'site' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'documents' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'البيانات المدخلة غير صحيحة',
                'errors' => $validator->errors()
            ], 422);
        }

  if ($request->hasFile('image')) {
                $image = $request->file('image');
                $dest = 'image/';

                $data = time() . '.' . $image->getClientOriginalExtension();
                $image->move($dest,$data);
            }

        try {
            $complaintData = [
                'user_id' => $user->id,
                'type_id' => $request->type_id,
                'destination' => $request->destination,
                'site' => $request->site,
                'description' => $request->description,
                'status' => 'new',
                'image' => '/image/' . $data ?? ''
            ];

          

            if ($request->hasFile('documents')) {
                $docPath = $request->file('documents')->store('complaints/documents', 'public');
                $complaintData['documents'] = $docPath;
            }

            $complaint = $this->complaintRepository->createComplaint($complaintData);

            return response()->json([
                'success' => true,
                'message' => 'تم تقديم الشكوى بنجاح',
                'data' => $complaint
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تقديم الشكوى',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    
    public function getUserComplaints()
    {
        $user = Auth::user();
        $complaints = $this->complaintRepository->getUserComplaints($user->id);

        return response()->json([
            'success' => true,
            'data' => $complaints
        ]);
    }


    public function getComplaintDetails($id)
    {
        $complaint = $this->complaintRepository->findComplaint($id);
        
        if (!$complaint) {
            return response()->json([
                'success' => false,
                'message' => 'الشكوى غير موجودة'
            ], 404);
        }

        if ($complaint->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بالوصول لهذه الشكوى'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $complaint
        ]);
    }
}