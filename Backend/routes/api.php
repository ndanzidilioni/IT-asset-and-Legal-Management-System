<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DeveloperAvailabilityController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\ITAssetController;
use App\Http\Controllers\DropdownOptionController;
use App\Http\Controllers\ICTDashboardController;
use App\Http\Controllers\LocationHierarchyController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DemandNoteController;
use App\Http\Controllers\LegalClientController;
use App\Http\Controllers\LegalCaseController;
use App\Http\Controllers\LegalContractController;
use App\Http\Controllers\CourtScheduleController;
use App\Http\Controllers\LegalInvoiceController;
use App\Http\Controllers\LegalTimeEntryController;
use App\Http\Controllers\LegalDocumentController;
use App\Http\Controllers\CourtProceedingController;
use App\Http\Controllers\ContractDeletionRequestController;
use App\Http\Controllers\CaseDeletionRequestController;
use App\Http\Controllers\DemandNoteDeletionRequestController;

Route::post('register',[AuthController::class,'register']);
Route::post('login',[AuthController::class,'login']);

// Test endpoint to verify server is responding (bypasses all middleware)
Route::get('test', function() {
    return response()->json([
        'message' => 'Server is responding', 
        'timestamp' => now()->toDateTimeString(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version()
    ]);
});

// Test endpoint that tries database connection
Route::get('test-db', function() {
    try {
        $start = microtime(true);
        \DB::connection()->getPdo();
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        return response()->json([
            'message' => 'Database connection successful',
            'elapsed_ms' => $elapsed,
            'connection' => config('database.default')
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ], 500);
    }
});

// Public IT Asset statistics endpoint (for dashboard)
Route::get('it-assets/statistics',[ITAssetController::class,'statistics']);

// Public Demand Notes endpoints (for testing)
Route::get('demand-notes',[DemandNoteController::class,'index']);
Route::get('demand-notes/statistics',[DemandNoteController::class,'statistics']);
Route::get('demand-notes/{demandNote}',[DemandNoteController::class,'show']);

// Public Legal Cases endpoint (for testing)
Route::get('cases/test',[LegalCaseController::class,'test']);

// Public Legal Contracts endpoint (for testing)
Route::get('contracts/test',[LegalContractController::class,'test']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('user',[AuthController::class,'user']);
    Route::post('change-password',[AuthController::class,'changePassword']);
    Route::get('users',[UserController::class,'index']);
    Route::post('users',[UserController::class,'store']);
    Route::put('users/{user}',[UserController::class,'update']);
    Route::delete('users/{user}',[UserController::class,'destroy']);
    Route::get('users/privileges/available',[UserController::class,'getPrivileges']);
    Route::put('users/{user}/privileges',[UserController::class,'updatePrivileges']);
    Route::post('users/{user}/reset-password',[UserController::class,'resetPassword']);
    Route::post('users/{user}/unlock',[UserController::class,'unlockAccount']);
    Route::get('developer-overview',[UserController::class,'developerOverview']);
    Route::post('logout',[AuthController::class,'logout']);
    // schedules
    Route::get('schedules',[\App\Http\Controllers\ScheduleController::class,'index']);
    Route::post('schedules',[\App\Http\Controllers\ScheduleController::class,'store']);
    Route::get('schedules/{id}',[\App\Http\Controllers\ScheduleController::class,'show']);
    Route::put('schedules/{id}',[\App\Http\Controllers\ScheduleController::class,'update']);
    Route::delete('schedules/{id}',[\App\Http\Controllers\ScheduleController::class,'destroy']);
    Route::get('schedules/free-slots',[\App\Http\Controllers\ScheduleController::class,'freeSlots']);
        Route::post('schedules/book',[App\Http\Controllers\ScheduleController::class,'book']);
    Route::get('tasks',[TaskController::class,'index']);
    Route::post('tasks',[TaskController::class,'store']);
    Route::post('tasks/request',[TaskController::class,'clientStore']);
    Route::put('tasks/{id}',[TaskController::class,'update']);
    Route::delete('tasks/{id}',[TaskController::class,'destroy']);
    Route::post('tasks/{id}/reject',[TaskController::class,'reject']);
    Route::post('availability',[DeveloperAvailabilityController::class,'store']);
    Route::get('availability',[DeveloperAvailabilityController::class,'show']);
    
    // Inquiry routes
    Route::get('inquiries',[InquiryController::class,'index']);
    Route::get('inquiries/report',[InquiryController::class,'report']);
    Route::post('inquiries',[InquiryController::class,'store']);
    Route::get('inquiries/{inquiry}',[InquiryController::class,'show']);
    Route::put('inquiries/{inquiry}',[InquiryController::class,'update']);
    Route::delete('inquiries/{inquiry}',[InquiryController::class,'destroy']);
    Route::post('inquiries/{inquiry}/close',[InquiryController::class,'close']);
    Route::post('inquiries/{inquiry}/open',[InquiryController::class,'open']);
    
    // IT Asset routes
    Route::get('it-assets',[ITAssetController::class,'index']);
    Route::post('it-assets',[ITAssetController::class,'store']);
    Route::get('it-assets/export/csv',[ITAssetController::class,'exportCsv']);
    Route::post('it-assets/import/csv',[ITAssetController::class,'importCsv']);
    Route::get('it-assets/template',[ITAssetController::class,'downloadTemplate']);
    // statistics endpoint moved outside auth middleware (see line 19)
    Route::get('it-assets/{itAsset}',[ITAssetController::class,'show']);
    Route::put('it-assets/{itAsset}',[ITAssetController::class,'update']);
    Route::delete('it-assets/{itAsset}',[ITAssetController::class,'destroy']);
    
    // Dropdown options routes
    Route::get('dropdown-options',[DropdownOptionController::class,'getAllOptions']);
    Route::get('dropdown-options/{type}',[DropdownOptionController::class,'getOptions']);
    Route::post('dropdown-options',[DropdownOptionController::class,'store']);
    Route::put('dropdown-options/{dropdownOption}',[DropdownOptionController::class,'update']);
    Route::delete('dropdown-options/{dropdownOption}',[DropdownOptionController::class,'destroy']);
    Route::post('dropdown-options/{dropdownOption}/toggle',[DropdownOptionController::class,'toggle']);
    
    // ICT Dashboard routes
    Route::get('ict-dashboard',[ICTDashboardController::class,'index']);
    Route::get('ict-dashboard/reports',[ICTDashboardController::class,'getReports']);
    
    // Location Hierarchy routes for cascading dropdowns
    Route::get('location-hierarchy/buildings',[LocationHierarchyController::class,'getBuildings']);
    Route::get('location-hierarchy/floors',[LocationHierarchyController::class,'getFloors']);
    Route::get('location-hierarchy/departments',[LocationHierarchyController::class,'getDepartments']);
    Route::get('location-hierarchy/rooms',[LocationHierarchyController::class,'getRooms']);
    Route::get('location-hierarchy/options',[LocationHierarchyController::class,'getHierarchicalOptions']);
    Route::post('location-hierarchy/validate',[LocationHierarchyController::class,'validateCombination']);
    
    // Audit Log routes (Admin only)
    Route::get('audit-logs',[AuditLogController::class,'index']);
    Route::get('audit-logs/statistics',[AuditLogController::class,'statistics']);
    Route::get('audit-logs/{auditLog}',[AuditLogController::class,'show']);
    Route::get('audit-logs/export/csv',[AuditLogController::class,'exportCsv']);
    
    // Demand Notes routes (Admin and Lawyer)
    Route::post('demand-notes',[DemandNoteController::class,'store']);
    Route::get('demand-notes/export/csv',[DemandNoteController::class,'exportCsv']);
    Route::put('demand-notes/{demandNote}',[DemandNoteController::class,'update']);
    Route::delete('demand-notes/{demandNote}',[DemandNoteController::class,'destroy']);
    Route::post('demand-notes/{demandNote}/payments',[DemandNoteController::class,'addPayment']);
    
    // Demand Note Deletion Requests (Approval System)
    Route::get('demand-note-deletion-requests',[DemandNoteDeletionRequestController::class,'index']);
    Route::get('demand-note-deletion-requests/pending',[DemandNoteDeletionRequestController::class,'pending']);
    Route::post('demand-note-deletion-requests',[DemandNoteDeletionRequestController::class,'store']);
    Route::post('demand-note-deletion-requests/{id}/approve',[DemandNoteDeletionRequestController::class,'approve']);
    Route::post('demand-note-deletion-requests/{id}/reject',[DemandNoteDeletionRequestController::class,'reject']);
    Route::delete('demand-note-deletion-requests/{id}',[DemandNoteDeletionRequestController::class,'destroy']);
    
    // Legal Clients routes
    Route::get('clients',[LegalClientController::class,'index']);
    Route::post('clients',[LegalClientController::class,'store']);
    Route::get('clients/statistics',[LegalClientController::class,'statistics']);
    Route::get('clients/{id}',[LegalClientController::class,'show']);
    Route::put('clients/{id}',[LegalClientController::class,'update']);
    Route::delete('clients/{id}',[LegalClientController::class,'destroy']);
    
    // Legal Cases routes
    Route::get('cases',[LegalCaseController::class,'index']);
    Route::post('cases',[LegalCaseController::class,'store']);
    Route::get('cases/statistics',[LegalCaseController::class,'statistics']);
    Route::get('cases/dashboard',[LegalCaseController::class,'dashboard']);
    Route::get('cases/{id}',[LegalCaseController::class,'show']);
    Route::put('cases/{id}',[LegalCaseController::class,'update']);
    Route::delete('cases/{id}',[LegalCaseController::class,'destroy']);
    
    // Case Deletion Requests (Approval System)
    Route::get('case-deletion-requests',[CaseDeletionRequestController::class,'index']);
    Route::get('case-deletion-requests/pending',[CaseDeletionRequestController::class,'pending']);
    Route::post('case-deletion-requests',[CaseDeletionRequestController::class,'store']);
    Route::post('case-deletion-requests/{id}/approve',[CaseDeletionRequestController::class,'approve']);
    Route::post('case-deletion-requests/{id}/reject',[CaseDeletionRequestController::class,'reject']);
    Route::delete('case-deletion-requests/{id}',[CaseDeletionRequestController::class,'destroy']);
    
    // Legal Contracts routes
    Route::get('contracts',[LegalContractController::class,'index']);
    Route::post('contracts',[LegalContractController::class,'store']);
    Route::get('contracts/statistics',[LegalContractController::class,'statistics']);
    Route::get('contracts/years',[LegalContractController::class,'getYears']);
    Route::get('contracts/{id}',[LegalContractController::class,'show']);
    Route::get('contracts/{id}/documents',[LegalContractController::class,'getDocuments']);
    Route::put('contracts/{id}',[LegalContractController::class,'update']);
    Route::delete('contracts/{id}',[LegalContractController::class,'destroy']);
    
    // Contract Deletion Requests (Approval System)
    Route::get('contract-deletion-requests',[ContractDeletionRequestController::class,'index']);
    Route::get('contract-deletion-requests/pending',[ContractDeletionRequestController::class,'pending']);
    Route::post('contract-deletion-requests',[ContractDeletionRequestController::class,'store']);
    Route::post('contract-deletion-requests/{id}/approve',[ContractDeletionRequestController::class,'approve']);
    Route::post('contract-deletion-requests/{id}/reject',[ContractDeletionRequestController::class,'reject']);
    Route::delete('contract-deletion-requests/{id}',[ContractDeletionRequestController::class,'destroy']);
    
    // Court Schedules routes
    Route::get('court-schedules',[CourtScheduleController::class,'index']);
    Route::post('court-schedules',[CourtScheduleController::class,'store']);
    Route::get('court-schedules/hearings',[CourtScheduleController::class,'getHearings']);
    Route::get('court-schedules/deadlines',[CourtScheduleController::class,'getDeadlines']);
    Route::get('court-schedules/today',[CourtScheduleController::class,'getToday']);
    Route::get('court-schedules/{id}',[CourtScheduleController::class,'show']);
    Route::put('court-schedules/{id}',[CourtScheduleController::class,'update']);
    Route::delete('court-schedules/{id}',[CourtScheduleController::class,'destroy']);
    
    // Legal Invoices routes
    Route::get('invoices',[LegalInvoiceController::class,'index']);
    Route::post('invoices',[LegalInvoiceController::class,'store']);
    Route::get('invoices/summary',[LegalInvoiceController::class,'getSummary']);
    Route::get('invoices/{id}',[LegalInvoiceController::class,'show']);
    Route::put('invoices/{id}',[LegalInvoiceController::class,'update']);
    Route::delete('invoices/{id}',[LegalInvoiceController::class,'destroy']);
    
    // Legal Time Entries routes
    Route::get('time-entries',[LegalTimeEntryController::class,'index']);
    Route::post('time-entries',[LegalTimeEntryController::class,'store']);
    Route::put('time-entries/{id}',[LegalTimeEntryController::class,'update']);
    Route::delete('time-entries/{id}',[LegalTimeEntryController::class,'destroy']);
    
    // Legal Documents routes
    Route::get('documents',[LegalDocumentController::class,'index']);
    Route::post('documents',[LegalDocumentController::class,'store']);
    Route::get('documents/{id}',[LegalDocumentController::class,'show']);
    Route::get('documents/{id}/download',[LegalDocumentController::class,'download']);
    Route::delete('documents/{id}',[LegalDocumentController::class,'destroy']);
    
    // Court Proceedings routes
    Route::get('court-proceedings',[CourtProceedingController::class,'index']);
    Route::post('court-proceedings',[CourtProceedingController::class,'store']);
    Route::get('court-proceedings/case/{caseId}',[CourtProceedingController::class,'getByCaseId']);
    Route::get('court-proceedings/{id}',[CourtProceedingController::class,'show']);
    Route::put('court-proceedings/{id}',[CourtProceedingController::class,'update']);
    Route::delete('court-proceedings/{id}',[CourtProceedingController::class,'destroy']);
});

// Public inquiry submission route (no authentication required)
Route::post('inquiries/submit',[InquiryController::class,'store']);
