<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request){
        $data = $request->validate([
            'fname'=>'required|string|max:255',
            'mname'=>'nullable|string|max:255',
            'lname'=>'required|string|max:255',
            'email'=>'required|email|unique:users',
            'username'=>'required|string|max:255|unique:users',
            'password'=>'required|min:8',
            'role'=>'required|in:admin,user,developer,client'
        ]);

        // Validate password policy
        $passwordErrors = User::validatePasswordPolicy($data['password']);
        if (!empty($passwordErrors)) {
            return response()->json([
                'message' => 'Password does not meet policy requirements',
                'errors' => ['password' => $passwordErrors]
            ], 422);
        }

        $data['password'] = bcrypt($data['password']);
        $data['status'] = 'active';
        $data['password_changed_at'] = now();
        $data['must_change_password'] = false; // Self-registration doesn't require password change
        
        // Set default privileges based on role
        $data['privileges'] = $this->getDefaultPrivileges($data['role']);
        
        $user = User::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful. Please login with your credentials.',
            'user' => [
                'id' => $user->id,
                'fname' => $user->fname,
                'lname' => $user->lname,
                'email' => $user->email,
                'username' => $user->username,
                'role' => $user->role
            ]
        ]);
    }

    public function login(Request $request){
        $loginField = $request->input('login'); // Can be email or username
        $password = $request->input('password');
        
        // Determine if login field is email or username
        $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);
        
        // Find user first to check account status
        $user = $isEmail 
            ? User::where('email', $loginField)->first()
            : User::where('username', $loginField)->first();
            
        if (!$user) {
            return response()->json(['message'=>'Invalid credentials'],401);
        }
        
        // Check if account is locked
        if ($user->isLocked()) {
            $remainingMinutes = $user->getRemainingLockMinutes();
            $message = $remainingMinutes > 0 
                ? "Account is locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minute(s)."
                : 'Account is locked due to multiple failed login attempts. Please try again later.';
            
            return response()->json([
                'message' => $message,
                'locked_until' => $user->locked_until,
                'remaining_minutes' => $remainingMinutes
            ], 423);
        }
        
        // Check if account is active
        if ($user->status !== 'active') {
            return response()->json(['message'=>'Account is not active'],401);
        }
        
        // Attempt authentication
        $credentials = $isEmail 
            ? ['email' => $loginField, 'password' => $password]
            : ['username' => $loginField, 'password' => $password];
            
        if(!Auth::attempt($credentials)){
            $user->incrementFailedAttempts();
            return response()->json(['message'=>'Invalid credentials'],401);
        }
        
        // Reset failed attempts on successful login
        $user->resetFailedAttempts();
        
        // Check if user is active
        if($user->status === 'inactive'){
            Auth::logout();
            return response()->json(['message'=>'Your account has been deactivated. Please contact an administrator.'],403);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;

        // Log successful login
        AuditLog::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'action' => 'login',
            'description' => 'User logged in successfully',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_method' => 'POST',
            'request_url' => $request->fullUrl(),
            'metadata' => json_encode(['role' => $user->role]),
        ]);

        // Check if user must change password
        if ($user->must_change_password) {
            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'must_change_password' => true,
                'message' => 'You must change your password before continuing.',
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role,
                    'fname' => $user->fname,
                    'lname' => $user->lname
                ]
            ]);
        }

        return response()->json([
            'access_token'=>$token,
            'token_type'=>'Bearer',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'fname' => $user->fname,
                'lname' => $user->lname
            ]
        ]);
    }

    public function logout(Request $request){
        $user = $request->user();
        
        // Log logout activity
        AuditLog::create([
            'user_id' => $user->id,
            'username' => $user->username,
            'action' => 'logout',
            'description' => 'User logged out',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'request_method' => 'POST',
            'request_url' => $request->fullUrl(),
        ]);
        
        $user->tokens()->delete();
        return response()->json(['message'=>'Logged out']);
    }
    public function user(Request $request){
        return response()->json($request->user());
    }

    /**
     * Change user password (especially for first-time login)
     */
    public function changePassword(Request $request)
    {
        // Debug logging
        \Log::info('Change password attempt', [
            'has_user' => $request->user() !== null,
            'auth_check' => auth()->check(),
            'guard' => auth()->getDefaultDriver(),
            'session_id' => session()->getId(),
            'cookies' => $request->cookies->all(),
        ]);
        
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        // Verify current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        // Validate new password policy
        $passwordErrors = User::validatePasswordPolicy($request->new_password);
        if (!empty($passwordErrors)) {
            return response()->json([
                'success' => false,
                'message' => 'Password does not meet requirements.',
                'errors' => $passwordErrors
            ], 422);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_changed_at' => now(),
            'must_change_password' => false, // Clear the flag
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.'
        ]);
    }

    /**
     * Get default privileges based on user role.
     */
    private function getDefaultPrivileges(string $role): array
    {
        switch ($role) {
            case 'admin':
                return []; // Admins get all privileges by default
            case 'developer':
                return ['developer_access', 'manage_tasks', 'view_reports'];
            case 'client':
                return ['client_access', 'view_reports'];
            case 'user':
            default:
                return ['view_reports'];
        }
    }

    /**
     * Session (cookie) based login for SPA using Sanctum stateful auth.
     */
    public function loginWeb(Request $request)
    {
        // Debug logging
        \Log::info('Login attempt', [
            'session_id' => session()->getId(),
            'csrf_token' => csrf_token(),
            'request_token' => $request->header('X-XSRF-TOKEN'),
            'cookies' => $request->cookies->all(),
            'has_session' => $request->hasSession(),
        ]);

        $loginField = $request->input('login');
        $password = $request->input('password');

        $isEmail = filter_var($loginField, FILTER_VALIDATE_EMAIL);
        $user = $isEmail
            ? User::where('email', $loginField)->first()
            : User::where('username', $loginField)->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->isLocked()) {
            $remainingMinutes = $user->getRemainingLockMinutes();
            $message = $remainingMinutes > 0 
                ? "Account is locked due to multiple failed login attempts. Please try again in {$remainingMinutes} minute(s)."
                : 'Account is locked due to multiple failed login attempts. Please try again later.';
            
            return response()->json([
                'message' => $message,
                'locked_until' => $user->locked_until,
                'remaining_minutes' => $remainingMinutes
            ], 423);
        }

        if ($user->status !== 'active') {
            return response()->json(['message' => 'Account is not active'], 401);
        }

        $credentials = $isEmail
            ? ['email' => $loginField, 'password' => $password]
            : ['username' => $loginField, 'password' => $password];

        if (!Auth::attempt($credentials)) {
            $user->incrementFailedAttempts();
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $request->session()->regenerate();
        $user->resetFailedAttempts();

        if ($user->status === 'inactive') {
            Auth::logout();
            return response()->json(['message' => 'Your account has been deactivated. Please contact an administrator.'], 403);
        }

        // Return must_change_password flag for frontend to trigger modal
        return response()->json([
            'success' => true,
            'must_change_password' => (bool)$user->must_change_password,
            'message' => $user->must_change_password ? 'You must change your password before continuing.' : 'Login successful.',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'fname' => $user->fname,
                'mname' => $user->mname,
                'lname' => $user->lname,
                'full_name' => $user->full_name,
                'role' => $user->role,
                'status' => $user->status,
                'privileges' => $user->privileges
            ]
        ]);
    }

    /**
     * Session (cookie) based logout for SPA.
     */
    public function logoutWeb(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logged out']);
    }
}
