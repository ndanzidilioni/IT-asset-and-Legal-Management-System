<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DeveloperAvailability;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Exports\UsersExport;
use App\Imports\UsersImport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if(!$user || $user->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $users = User::select('id','fname','mname','lname','email','username','role','status','privileges','created_at')->get();
        
        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    // return developers with availability and their assigned tasks
    public function developerOverview(Request $request){
        $user = $request->user();
        if(!$user || $user->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $developers = User::where('role','developer')->select('id','name','email')->get();

        $overview = $developers->map(function($dev){
            $availability = DeveloperAvailability::where('developer_id',$dev->id)->first();
            $tasks = Task::where('developer_id',$dev->id)->get(['id','title','status']);
            return [
                'id'=>$dev->id,
                'name'=>$dev->name,
                'email'=>$dev->email,
                'availability' => $availability ? $availability->availability : null,
                'tasks' => $tasks,
            ];
        });

        return response()->json($overview);
    }

    /**
     * Create a new user.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if(!$user || $user->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        try {
            $validated = $request->validate([
                'fname' => 'required|string|max:255',
                'mname' => 'nullable|string|max:255',
                'lname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'username' => 'required|string|max:255|unique:users',
                'password' => 'required|string|min:8',
                'role' => 'required|in:admin,user,ict,client,lawyer',
                'privileges' => 'nullable|array',
                'privileges.*' => 'nullable|string',
                'status' => 'required|in:active,inactive'
            ]);

            $user = User::create([
                'fname' => $validated['fname'],
                'mname' => $validated['mname'],
                'lname' => $validated['lname'],
                'email' => $validated['email'],
                'username' => $validated['username'],
                'password' => Hash::make($validated['password']),
                'role' => $validated['role'],
                'status' => $validated['status'],
                'privileges' => $validated['privileges'] ?? [],
                'must_change_password' => true, // Force password change on first login
                'password_changed_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'id' => $user->id,
                    'fname' => $user->fname,
                    'mname' => $user->mname,
                    'lname' => $user->lname,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'status' => $user->status,
                    'privileges' => $user->privileges,
                    'created_at' => $user->created_at
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Update a user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        try {
            $validated = $request->validate([
                'fname' => 'sometimes|required|string|max:255',
                'mname' => 'sometimes|nullable|string|max:255',
                'lname' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
                'password' => 'sometimes|nullable|string|min:8',
                'role' => 'sometimes|required|in:admin,user,ict,client,lawyer',
                'privileges' => 'sometimes|nullable|array',
                'privileges.*' => 'sometimes|string',
                'status' => 'sometimes|required|in:active,inactive'
            ]);

            // Prevent admin from deactivating themselves
            if (isset($validated['status']) && $validated['status'] === 'inactive' && $user->id === $currentUser->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot deactivate your own account'
                ], 422);
            }

            $updateData = [];
            if (isset($validated['fname'])) $updateData['fname'] = $validated['fname'];
            if (isset($validated['mname'])) $updateData['mname'] = $validated['mname'];
            if (isset($validated['lname'])) $updateData['lname'] = $validated['lname'];
            if (isset($validated['email'])) $updateData['email'] = $validated['email'];
            if (isset($validated['username'])) $updateData['username'] = $validated['username'];
            if (isset($validated['role'])) $updateData['role'] = $validated['role'];
            if (isset($validated['status'])) $updateData['status'] = $validated['status'];
            if (isset($validated['privileges'])) $updateData['privileges'] = $validated['privileges'];
            if (isset($validated['password']) && $validated['password']) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $user->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'id' => $user->id,
                    'fname' => $user->fname,
                    'mname' => $user->mname,
                    'lname' => $user->lname,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                    'status' => $user->status,
                    'privileges' => $user->privileges,
                    'created_at' => $user->created_at
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Delete a user.
     */
    public function destroy(User $user): JsonResponse
    {
        $currentUser = request()->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        // Prevent admin from deleting themselves
        if ($currentUser->id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete your own account'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    }

    /**
     * Get available privileges.
     */
    public function getPrivileges(Request $request): JsonResponse
    {
        $user = $request->user();
        if(!$user || $user->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        return response()->json([
            'success' => true,
            'data' => User::getAvailablePrivileges()
        ]);
    }

    /**
     * Update user privileges.
     */
    public function updatePrivileges(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $validated = $request->validate([
            'privileges' => 'required|array',
            'privileges.*' => 'string|in:' . implode(',', array_keys(User::getAvailablePrivileges()))
        ]);

        $user->privileges = $validated['privileges'];
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'User privileges updated successfully',
            'data' => [
                'id' => $user->id,
                'privileges' => $user->privileges
            ]
        ]);
    }

    /**
     * Reset user password (admin only).
     */
    public function resetPassword(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $validated = $request->validate([
            'password' => 'required|string|min:8'
        ]);

        // Validate password policy
        $passwordErrors = User::validatePasswordPolicy($validated['password']);
        if (!empty($passwordErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Password does not meet policy requirements',
                'errors' => ['password' => $passwordErrors]
            ], 422);
        }

        $user->password = Hash::make($validated['password']);
        $user->password_changed_at = now();
        $user->must_change_password = true; // Force user to change password on next login
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. User must change password on next login.'
        ]);
    }

    /**
     * Unlock user account.
     */
    public function unlockAccount(Request $request, User $user): JsonResponse
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $user->unlockAccount();

        return response()->json([
            'success' => true,
            'message' => 'User account unlocked successfully'
        ]);
    }

    /**
     * Export users to CSV.
     */
    public function exportCsv(Request $request)
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        try {
            return Excel::download(new UsersExport, 'users_' . date('Y-m-d_H-i-s') . '.csv');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export users to Excel.
     */
    public function exportExcel(Request $request)
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        try {
            return Excel::download(new UsersExport, 'users_' . date('Y-m-d_H-i-s') . '.xlsx');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import users from CSV.
     */
    public function importCsv(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));
            
            $results = $import->getResults();
            
            return response()->json([
                'success' => true,
                'message' => 'Import completed',
                'data' => [
                    'imported' => $results['success_count'],
                    'skipped' => $results['skip_count'],
                    'errors' => $results['error_count'],
                    'error_details' => $results['errors']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import users from Excel.
     */
    public function importExcel(Request $request): JsonResponse
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:2048'
        ]);

        try {
            $import = new UsersImport();
            Excel::import($import, $request->file('file'));
            
            $results = $import->getResults();
            
            return response()->json([
                'success' => true,
                'message' => 'Import completed',
                'data' => [
                    'imported' => $results['success_count'],
                    'skipped' => $results['skip_count'],
                    'errors' => $results['error_count'],
                    'error_details' => $results['errors']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download sample import template.
     */
    public function downloadTemplate(Request $request)
    {
        $currentUser = $request->user();
        if(!$currentUser || $currentUser->role !== 'admin'){
            return response()->json(['message'=>'Unauthorized'],403);
        }

        $format = $request->query('format', 'csv'); // csv or excel
        
        // Sample data
        $sampleData = [
            [
                'first_name' => 'John',
                'middle_name' => 'Michael',
                'last_name' => 'Doe',
                'email' => 'john.doe@example.com',
                'username' => 'johndoe',
                'password' => 'SecurePass123!',
                'role' => 'user',
                'status' => 'active',
                'privileges' => 'view_reports, manage_tasks'
            ],
            [
                'first_name' => 'Jane',
                'middle_name' => '',
                'last_name' => 'Smith',
                'email' => 'jane.smith@example.com',
                'username' => 'janesmith',
                'password' => 'AnotherPass456!',
                'role' => 'developer',
                'status' => 'active',
                'privileges' => 'developer_access, manage_tasks, view_reports'
            ]
        ];

        try {
            if ($format === 'excel') {
                return Excel::download(new \App\Exports\UserTemplateExport($sampleData), 'user_import_template.xlsx');
            } else {
                return Excel::download(new \App\Exports\UserTemplateExport($sampleData), 'user_import_template.csv');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Template download failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
