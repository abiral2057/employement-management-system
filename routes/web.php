<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CheckController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LateTimeController;
use App\Http\Controllers\LeaveController;
use App\Http\Controllers\OverTimeController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ShiftRosterController;
use App\Http\Controllers\PaymentDueController;
use App\Http\Controllers\EmployeeDocumentController;
use App\Http\Controllers\UserController;
use App\Models\Payroll;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Route::get('/admin', function () {
//     return view('layouts.admin');
// });
// Route::middleware('auth')->prefix('admin')->group( function () {
Route::middleware('superadmin')->prefix('super')->group( function () {

    Route::get('/', [AdminController::class, 'index'])->name('super.dashboard');
    Route::get('/sample', [AdminController::class, 'sample'])->name('view.sample');
    Route::get('/performance-report', [AdminController::class, 'performanceReport'])->name('super.performance.report');
    Route::resource('employee', EmployeeController::class);
    Route::resource('department', DepartmentController::class);
    Route::resource('designation', DesignationController::class);
    // Route::resource('attendance', AttendanceController::class);
    Route::resource('leaves', LeaveController::class);
    // Route::resource('salary', SalaryController::class);
    Route::resource('payroll',PayrollController::class);
    Route::resource('roles',RoleController::class );
    Route::resource('user',UserController::class );
    Route::resource('attendance',AttendanceController::class );
    Route::resource('schedule',ScheduleController::class );
    Route::post('/check',[CheckController::class,'CheckStore'])->name('check.store');
    Route::get('/report',[CheckController::class,'sheetReport'])->name('sheet.report');
    Route::get('/gross-salary', [PayrollController::class, 'grossSalary'])->name('gross.salary');
    Route::get('/latetime',[LateTimeController::class,'index'])->name('attendance.latetime');
    Route::post('/latetime',[LateTimeController::class,'lateTime'])->name('late.time');
    Route::get('/overtime',[OverTimeController::class,'index'])->name('attendance.overtime');
    Route::post('/overtime',[OverTimeController::class,'overTime'])->name('over.time');
    Route::get('/barcode', [AttendanceController::class, 'barcode'])->name('attd.barcode');
    // Route::get('/report', [AttendanceController::class, 'report'])->name('attendance.report');
    Route::resource('/leaves',LeaveController::class);
    // Route::post('/check/store', [PayrollController::class, ])->name('check.store');
    Route::post('/calculate', [PayrollController::class, 'calculatePayroll'])->name('calculate.payroll');
    Route::get('/sheet-report', [PayrollController::class, 'sheetReport'])->name('payroll.report');
    Route::post('/generate', [PayrollController::class, 'generateReport'])->name('generate.payroll');

    // Employee Search routes
    Route::get('/employee/search', [EmployeeController::class, 'search'])->name('employee.search.ajax');
    Route::get('/employee-search', [EmployeeController::class, 'searchPage'])->name('employee.search');

});
Route::middleware('admin')->prefix('admin')->group( function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/performance-report', [AdminController::class, 'performanceReport'])->name('admin.performance.report');
    Route::prefix('department')->group(function() {
        Route::get('/', [DepartmentController::class, 'index'])->name('admin.department.index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('admin.department.create');
        Route::post('/', [DepartmentController::class, 'store'])->name('admin.department.store');
        Route::get('/{id}/edit', [DepartmentController::class, 'edit'])->name('admin.department.edit');
        Route::put('/{id}', [DepartmentController::class, 'update'])->name('admin.department.update');
        Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('admin.department.destroy');
    });
    Route::prefix('designation')->group(function() {
        Route::get('/', [DesignationController::class, 'index'])->name('admin.designation.index');
        Route::get('/create', [DesignationController::class, 'create'])->name('admin.designation.create');
        Route::post('/', [DesignationController::class, 'store'])->name('admin.designation.store');
        Route::get('/{id}/edit', [DesignationController::class, 'edit'])->name('admin.designation.edit');
        Route::put('/{id}', [DesignationController::class, 'update'])->name('admin.designation.update');
        Route::delete('/{id}', [DesignationController::class, 'destroy'])->name('admin.designation.destroy');
    });
    Route::prefix('employee')->group(function() {
        Route::get('/', [EmployeeController::class, 'index'])->name('admin.employee.index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('admin.employee.create');
        Route::post('/', [EmployeeController::class, 'store'])->name('admin.employee.store');
        Route::get('/{id}/show', [EmployeeController::class, 'show'])->name('admin.employee.show');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('admin.employee.edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('admin.employee.update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('admin.employee.destroy');
    });
    Route::prefix('schedule')->group(function() {
        Route::get('/', [ScheduleController::class, 'index'])->name('admin.schedule.index');
        Route::get('/create', [ScheduleController::class, 'create'])->name('admin.schedule.create');
        Route::post('/', [ScheduleController::class, 'store'])->name('admin.schedule.store');
        Route::get('/{id}/edit', [ScheduleController::class, 'edit'])->name('admin.schedule.edit');
        Route::put('/{id}', [ScheduleController::class, 'update'])->name('admin.schedule.update');
        Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('admin.schedule.destroy');
    });
    // Route::prefix('attendance')->group(function() {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::post('/check', [CheckController::class, 'CheckStore'])->name('admin.check.store');
        Route::get('/report', [CheckController::class, 'sheetReport'])->name('admin.sheet.report');
    // });
    Route::prefix('leaves')->group(function() {
        Route::get('/', [LeaveController::class, 'index'])->name('admin.leaves.index');
        Route::get('/create', [LeaveController::class, 'create'])->name('admin.leaves.create');
        Route::post('/', [LeaveController::class, 'store'])->name('admin.leaves.store');
        Route::get('/{id}/edit', [LeaveController::class, 'edit'])->name('admin.leaves.edit');
        Route::put('/{id}', [LeaveController::class, 'update'])->name('admin.leaves.update');
        Route::delete('/{id}', [LeaveController::class, 'destroy'])->name('admin.leaves.destroy');
    });
    // Route::resource('users',UserController::class );
    Route::prefix('users')->group(function() {
        Route::get('/', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/', [UserController::class, 'store'])->name('admin.users.store');
        Route::get('/{id}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/{id}', [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('admin.users.destroy');
    });
    Route::prefix('payroll')->group(function() {
        
        Route::get('/', [PayrollController::class, 'index'])->name('admin.payroll.index');
        Route::get('/create', [PayrollController::class, 'create'])->name('admin.payroll.create');
        

    });
    Route::post('/calculate', [PayrollController::class, 'calculatePayroll'])->name('admin.calculate.payroll');

    // Employee Search routes
    Route::get('/employee/search', [EmployeeController::class, 'search'])->name('admin.employee.search.ajax');
    Route::get('/employee-search', [EmployeeController::class, 'searchPage'])->name('admin.employee.search');
});

Route::middleware('moderator')->prefix('moderator')->group( function () {
    Route::get('/', [AdminController::class, 'index'])->name('moderator.dashboard');
    Route::prefix('schedule')->group(function() {
        Route::get('/', [ScheduleController::class, 'index'])->name('moderator.schedule.index');
        Route::get('/create', [ScheduleController::class, 'create'])->name('moderator.schedule.create');
        Route::post('/', [ScheduleController::class, 'store'])->name('moderator.schedule.store');
        Route::get('/{id}/edit', [ScheduleController::class, 'edit'])->name('moderator.schedule.edit');
        Route::put('/{id}', [ScheduleController::class, 'update'])->name('moderator.schedule.update');
        Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('moderator.schedule.destroy');
    });
    // Route::prefix('attendance')->group(function() {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('moderator.attendance.index');
        Route::post('/check', [CheckController::class, 'CheckStore'])->name('moderator.check.store');
        Route::get('/report', [CheckController::class, 'sheetReport'])->name('moderator.sheet.report');
    // });
});

Route::middleware('hr')->prefix('hr-manager')->group( function () {
    Route::get('/', [AdminController::class, 'index'])->name('hr.dashboard');
    Route::prefix('department')->group(function() {
        Route::get('/', [DepartmentController::class, 'index'])->name('hr.department.index');
        Route::get('/create', [DepartmentController::class, 'create'])->name('hr.department.create');
        Route::post('/', [DepartmentController::class, 'store'])->name('hr.department.store');
        Route::get('/{id}/edit', [DepartmentController::class, 'edit'])->name('hr.department.edit');
        Route::put('/{id}', [DepartmentController::class, 'update'])->name('hr.department.update');
        Route::delete('/{id}', [DepartmentController::class, 'destroy'])->name('hr.department.destroy');
    });
    Route::prefix('designation')->group(function() {
        Route::get('/', [DesignationController::class, 'index'])->name('hr.designation.index');
        Route::get('/create', [DesignationController::class, 'create'])->name('hr.designation.create');
        Route::post('/', [DesignationController::class, 'store'])->name('hr.designation.store');
        Route::get('/{id}/edit', [DesignationController::class, 'edit'])->name('hr.designation.edit');
        Route::put('/{id}', [DesignationController::class, 'update'])->name('hr.designation.update');
        Route::delete('/{id}', [DesignationController::class, 'destroy'])->name('hr.designation.destroy');
    });
    Route::prefix('employee')->group(function() {
        Route::get('/', [EmployeeController::class, 'index'])->name('hr.employee.index');
        Route::get('/create', [EmployeeController::class, 'create'])->name('hr.employee.create');
        Route::get('/{id}/show', [EmployeeController::class, 'show'])->name('hr.employee.show');
        Route::post('/', [EmployeeController::class, 'store'])->name('hr.employee.store');
        Route::get('/{id}/edit', [EmployeeController::class, 'edit'])->name('hr.employee.edit');
        Route::put('/{id}', [EmployeeController::class, 'update'])->name('hr.employee.update');
        Route::delete('/{id}', [EmployeeController::class, 'destroy'])->name('hr.employee.destroy');
    });
    Route::prefix('leaves')->group(function() {
        Route::get('/', [LeaveController::class, 'index'])->name('hr.leaves.index');
        Route::get('/create', [LeaveController::class, 'create'])->name('hr.leaves.create');
        Route::post('/', [LeaveController::class, 'store'])->name('hr.leaves.store');
        Route::get('/{id}/edit', [LeaveController::class, 'edit'])->name('hr.leaves.edit');
        Route::put('/{id}', [LeaveController::class, 'update'])->name('hr.leaves.update');
        Route::delete('/{id}', [LeaveController::class, 'destroy'])->name('hr.leaves.destroy');
    });

    // Employee Search routes
    Route::get('/employee/search', [EmployeeController::class, 'search'])->name('hr.employee.search.ajax');
    Route::get('/employee-search', [EmployeeController::class, 'searchPage'])->name('hr.employee.search');

    // Payroll Management for HR Manager
    Route::prefix('payroll')->group(function() {
        Route::get('/', [PayrollController::class, 'index'])->name('hr.payroll.index');
        Route::get('/create', [PayrollController::class, 'create'])->name('hr.payroll.create');
    });
    Route::post('/calculate', [PayrollController::class, 'calculatePayroll'])->name('hr.calculate.payroll');

    // Schedule & Shift Management for HR Manager
    Route::prefix('schedule')->group(function() {
        Route::get('/', [ScheduleController::class, 'index'])->name('hr.schedule.index');
        Route::get('/create', [ScheduleController::class, 'create'])->name('hr.schedule.create');
        Route::post('/', [ScheduleController::class, 'store'])->name('hr.schedule.store');
        Route::get('/{id}/edit', [ScheduleController::class, 'edit'])->name('hr.schedule.edit');
        Route::put('/{id}', [ScheduleController::class, 'update'])->name('hr.schedule.update');
        Route::delete('/{id}', [ScheduleController::class, 'destroy'])->name('hr.schedule.destroy');
    });

    // Attendance & Reports for HR Manager
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('hr.attendance.index');
    Route::post('/check', [CheckController::class, 'CheckStore'])->name('hr.check.store');
    Route::get('/report', [CheckController::class, 'sheetReport'])->name('hr.sheet.report');
    Route::get('/performance-report', [AdminController::class, 'performanceReport'])->name('hr.performance.report');
});

Route::middleware('payroll')->prefix('manager')->group( function () {
    Route::get('/', [AdminController::class, 'index'])->name('payroll.dashboard');
    Route::prefix('payroll')->group(function() {
        
        Route::get('/', [PayrollController::class, 'index'])->name('manager.payroll.index');
        Route::get('/create', [PayrollController::class, 'create'])->name('manager.payroll.create');
        

    });
    Route::post('/calculate', [PayrollController::class, 'calculatePayroll'])->name('manager.calculate.payroll');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Shift Roster (Day / Night / Extra Shifts)
    Route::get('/shifts/roster', [ShiftRosterController::class, 'index'])->name('shifts.roster');
    Route::post('/shifts/roster', [ShiftRosterController::class, 'store'])->name('shifts.store');
    Route::post('/shifts/roster/quick-extra', [ShiftRosterController::class, 'quickAssignExtra'])->name('shifts.extra.quick');
    Route::post('/shifts/roster/{id}/status', [ShiftRosterController::class, 'updateStatus'])->name('shifts.status.update');
    Route::delete('/shifts/roster/{id}', [ShiftRosterController::class, 'destroy'])->name('shifts.destroy');

    // Overtime Management
    Route::get('/overtime-manager', [OverTimeController::class, 'index'])->name('attendance.overtime');
    Route::post('/overtime/store', [OverTimeController::class, 'store'])->name('attendance.overtime.store');
    Route::post('/overtime/{id}/approve', [OverTimeController::class, 'approve'])->name('attendance.overtime.approve');
    Route::post('/overtime/{id}/reject', [OverTimeController::class, 'reject'])->name('attendance.overtime.reject');
    Route::post('/overtime/{id}/payout', [OverTimeController::class, 'payout'])->name('attendance.overtime.payout');
    Route::delete('/overtime/{id}', [OverTimeController::class, 'destroy'])->name('attendance.overtime.destroy');

    // Late Time Management
    Route::get('/latetime-manager', [LateTimeController::class, 'index'])->name('attendance.latetime');
    Route::delete('/latetime/{id}', [LateTimeController::class, 'destroy'])->name('attendance.latetime.destroy');

    // Payment Due, Advance Salary & Reminder Center
    Route::get('/payroll/due', [PaymentDueController::class, 'index'])->name('payroll.due');
    Route::post('/payroll/due/{id}/pay', [PaymentDueController::class, 'recordPayment'])->name('payroll.due.pay');
    Route::post('/payroll/advance-payment', [PaymentDueController::class, 'recordAdvancePayment'])->name('payroll.advance-payment');
    Route::post('/payroll/due/{id}/reminder', [PaymentDueController::class, 'sendReminder'])->name('payroll.due.send-reminder');
    Route::post('/payroll/due/batch-reminder', [PaymentDueController::class, 'batchReminder'])->name('payroll.due.batch-reminder');

    // Transaction History & Disbursement Statements
    Route::get('/payroll/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('payroll.transactions');
    Route::get('/payroll/transactions/{id}/voucher', [\App\Http\Controllers\TransactionController::class, 'voucher'])->name('payroll.transactions.voucher');

    // Payroll Locking & CSV Export
    Route::post('/payroll/{id}/toggle-lock', [PayrollController::class, 'toggleLock'])->name('payroll.toggle-lock');
    Route::get('/payroll-export-csv', [PayrollController::class, 'exportCsv'])->name('payroll.export.csv');

    // Employee Document Management (Section 12)
    Route::post('/employee/{id}/documents', [EmployeeDocumentController::class, 'store'])->name('employee.documents.store');
    Route::get('/employee/documents/{id}/download', [EmployeeDocumentController::class, 'download'])->name('employee.documents.download');
    Route::delete('/employee/documents/{id}', [EmployeeDocumentController::class, 'destroy'])->name('employee.documents.destroy');

    // Leave Approval & Fast Rejection Workflows (Section 4)
    Route::post('/leaves/{id}/approve', [LeaveController::class, 'approve'])->name('leaves.quick.approve');
    Route::post('/leaves/{id}/reject', [LeaveController::class, 'reject'])->name('leaves.quick.reject');

    // Notification Center & Actions
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/clear-all', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('notifications.clear-all');
    Route::get('/notifications/fetch', [\App\Http\Controllers\NotificationController::class, 'fetchNavbar'])->name('notifications.fetch');
});

require __DIR__.'/auth.php';
