<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LeaveApplicationController as AdminLeaveApplicationController;
use App\Http\Controllers\Admin\LeaveTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\LeaveApplicationController as EmployeeLeaveApplicationController;
use App\Http\Controllers\Manager\DashboardController as ManagerDashboardController;
use App\Http\Controllers\Manager\LeaveReviewController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->dashboardRoute())
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route(auth()->user()->dashboardRoute());
    })->name('dashboard');

    Route::middleware('role:employee')->prefix('employee')->name('employee.')->group(function () {
        Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
        Route::get('/leaves/create', [EmployeeLeaveApplicationController::class, 'create'])->name('leaves.create');
        Route::post('/leaves', [EmployeeLeaveApplicationController::class, 'store'])->name('leaves.store');
        Route::delete('/leaves/{leave_application}', [EmployeeLeaveApplicationController::class, 'destroy'])->name('leaves.destroy');
    });

    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
        Route::patch('/leaves/{leave_application}/review', [LeaveReviewController::class, 'update'])->name('leaves.review');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', UserController::class)->except(['show']);
        Route::resource('leave-types', LeaveTypeController::class)->except(['show']);
        Route::get('/leaves/export', [AdminLeaveApplicationController::class, 'export'])->name('leaves.export');
        Route::get('/leaves', [AdminLeaveApplicationController::class, 'index'])->name('leaves.index');
        Route::patch('/leaves/{leave_application}/status', [AdminLeaveApplicationController::class, 'updateStatus'])->name('leaves.status');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
