<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Guru\DashboardController as GuruDashboard;
use App\Http\Controllers\Student\DashboardController as StudentDashboard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Auth Routes
Auth::routes();

// Waiting Approval Page
Route::get('/waiting-approval', function () {
    return view('auth.waiting-approval');
})->name('auth.waiting-approval');

// Student Auth Routes
Route::prefix('student')->name('student.')->group(function () {
    Route::get('/login', [App\Http\Controllers\Student\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Student\Auth\LoginController::class, 'login'])->name('login.post');
    Route::post('/logout', [App\Http\Controllers\Student\Auth\LoginController::class, 'logout'])->name('logout');
});

// ============================================
// ADMIN ROUTES
// ============================================
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // User Management
    Route::get('/users', [App\Http\Controllers\Admin\UserController::class, 'index'])->name('users.index');
    Route::get('/users/pending', [App\Http\Controllers\Admin\UserController::class, 'pending'])->name('users.pending');
    Route::get('/users/{user}', [App\Http\Controllers\Admin\UserController::class, 'show'])->name('users.show');
    Route::post('/users/{user}/approve', [App\Http\Controllers\Admin\UserController::class, 'approve'])->name('users.approve');
    Route::post('/users/{user}/reject', [App\Http\Controllers\Admin\UserController::class, 'reject'])->name('users.reject');
    Route::post('/users/{user}/toggle-status', [App\Http\Controllers\Admin\UserController::class, 'toggleStatus'])->name('users.toggle-status');

    // ============================================================
    // SISTEM KREDIT - Subscription management di-nonaktifkan
    // ============================================================
    // Route::get('/subscriptions', [App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('subscriptions.index');
    // Route::get('/subscriptions/{subscription}', [App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->name('subscriptions.show');
    // Route::post('/subscriptions/{subscription}/approve', [App\Http\Controllers\Admin\SubscriptionController::class, 'approve'])->name('subscriptions.approve');
    // Route::post('/subscriptions/{subscription}/reject', [App\Http\Controllers\Admin\SubscriptionController::class, 'reject'])->name('subscriptions.reject');

    // Settings
    Route::get('/settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [App\Http\Controllers\Admin\SettingController::class, 'update'])->name('settings.update');

    // Categories
    Route::resource('categories', App\Http\Controllers\Admin\CategoryController::class);

    // Analytics
    Route::get('/analytics', [App\Http\Controllers\Admin\AnalyticsController::class, 'index'])->name('analytics.index');
});

// ============================================
// GURU ROUTES
// ============================================
Route::prefix('guru')->name('guru.')->middleware(['auth', 'role:guru', 'check.approved'])->group(function () {
    Route::get('/dashboard', [GuruDashboard::class, 'index'])->name('dashboard');

    // Students
    Route::get('/students', [App\Http\Controllers\Guru\StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [App\Http\Controllers\Guru\StudentController::class, 'create'])->middleware('check.limit:student')->name('students.create');
    Route::post('/students', [App\Http\Controllers\Guru\StudentController::class, 'store'])->middleware('check.limit:student')->name('students.store');
    Route::get('/students/{student}', [App\Http\Controllers\Guru\StudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit', [App\Http\Controllers\Guru\StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [App\Http\Controllers\Guru\StudentController::class, 'update'])->name('students.update');
    Route::delete('/students/{student}', [App\Http\Controllers\Guru\StudentController::class, 'destroy'])->name('students.destroy');

    // Import Students
    Route::get('/students/import/template', [App\Http\Controllers\Guru\StudentController::class, 'downloadTemplate'])->name('students.import.template');
    Route::post('/students/import', [App\Http\Controllers\Guru\StudentController::class, 'import'])->middleware('check.limit:student')->name('students.import');

    // ============================================================
    // KELAS DINONAKTIFKAN - Semua fitur kelas di-nonaktifkan
    // ============================================================
    // Classes
    // Route::get('/classes', [App\Http\Controllers\Guru\ClassController::class, 'index'])->name('classes.index');
    // Route::get('/classes/create', [App\Http\Controllers\Guru\ClassController::class, 'create'])->middleware('check.limit:class')->name('classes.create');
    // Route::post('/classes', [App\Http\Controllers\Guru\ClassController::class, 'store'])->middleware('check.limit:class')->name('classes.store');
    // Route::get('/classes/{class}', [App\Http\Controllers\Guru\ClassController::class, 'show'])->name('classes.show');
    // Route::get('/classes/{class}/edit', [App\Http\Controllers\Guru\ClassController::class, 'edit'])->name('classes.edit');
    // Route::put('/classes/{class}', [App\Http\Controllers\Guru\ClassController::class, 'update'])->name('classes.update');
    // Route::delete('/classes/{class}', [App\Http\Controllers\Guru\ClassController::class, 'destroy'])->name('classes.destroy');

    // Questions
    Route::get('/questions', [App\Http\Controllers\Guru\QuestionController::class, 'index'])->name('questions.index');
    Route::get('/questions/create', [App\Http\Controllers\Guru\QuestionController::class, 'create'])->middleware('check.limit:question')->name('questions.create');
    Route::post('/questions', [App\Http\Controllers\Guru\QuestionController::class, 'store'])->middleware('check.limit:question')->name('questions.store');
    Route::get('/questions/{question}', [App\Http\Controllers\Guru\QuestionController::class, 'show'])->name('questions.show');
    Route::get('/questions/{question}/edit', [App\Http\Controllers\Guru\QuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/questions/{question}', [App\Http\Controllers\Guru\QuestionController::class, 'update'])->name('questions.update');
    Route::delete('/questions/{question}', [App\Http\Controllers\Guru\QuestionController::class, 'destroy'])->name('questions.destroy');

    // Import Questions
    Route::get('/questions/import/template', [App\Http\Controllers\Guru\QuestionController::class, 'downloadTemplate'])->name('questions.import.template');
    Route::post('/questions/import', [App\Http\Controllers\Guru\QuestionController::class, 'import'])->middleware('check.limit:question')->name('questions.import');

    // Test Packages
    Route::get('/packages', [App\Http\Controllers\Guru\PackageController::class, 'index'])->name('packages.index');
    Route::get('/packages/create', [App\Http\Controllers\Guru\PackageController::class, 'create'])->middleware('check.limit:package')->name('packages.create');
    Route::get('/packages/get-questions', [App\Http\Controllers\Guru\PackageController::class, 'getQuestions'])->name('packages.get-questions');
    Route::get('/packages/get-random-questions', [App\Http\Controllers\Guru\PackageController::class, 'getRandomQuestions'])->name('packages.get-random-questions');
    Route::post('/packages', [App\Http\Controllers\Guru\PackageController::class, 'store'])->middleware('check.limit:package')->name('packages.store');
    Route::get('/packages/{package}', [App\Http\Controllers\Guru\PackageController::class, 'show'])->name('packages.show');
    Route::get('/packages/{package}/edit', [App\Http\Controllers\Guru\PackageController::class, 'edit'])->name('packages.edit');
    Route::put('/packages/{package}', [App\Http\Controllers\Guru\PackageController::class, 'update'])->name('packages.update');
    Route::delete('/packages/{package}', [App\Http\Controllers\Guru\PackageController::class, 'destroy'])->name('packages.destroy');

    // Results & Analytics
    Route::get('/results', [App\Http\Controllers\Guru\ResultController::class, 'index'])->name('results.index');
    Route::get('/results/package/{package}', [App\Http\Controllers\Guru\ResultController::class, 'package'])->name('results.package');
    Route::get('/results/student/{student}', [App\Http\Controllers\Guru\ResultController::class, 'student'])->name('results.student');
    Route::get('/results/export/{package}', [App\Http\Controllers\Guru\ResultController::class, 'export'])->name('results.export');

    // ============================================================
    // SISTEM KREDIT - Ganti Subscription menjadi Credits
    // ============================================================
    // Route::get('/subscription', [App\Http\Controllers\Guru\SubscriptionController::class, 'index'])->name('subscription.index');
    // Route::get('/subscription/pricing', [App\Http\Controllers\Guru\SubscriptionController::class, 'pricing'])->name('subscription.pricing');
    // Route::post('/subscription/upgrade', [App\Http\Controllers\Guru\SubscriptionController::class, 'upgrade'])->name('subscription.upgrade');
    // Route::post('/subscription/{subscription}/cancel', [App\Http\Controllers\Guru\SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    // Route::get('/subscription/success', [App\Http\Controllers\Guru\SubscriptionController::class, 'success'])->name('subscription.success');

    // Credits (Top-up)
    Route::get('/credits', [App\Http\Controllers\Guru\CreditController::class, 'index'])->name('credits.index');
    Route::get('/credits/topup', [App\Http\Controllers\Guru\CreditController::class, 'topup'])->name('credits.topup');
    Route::post('/credits/purchase', [App\Http\Controllers\Guru\CreditController::class, 'purchase'])->name('credits.purchase');
    Route::get('/credits/success', [App\Http\Controllers\Guru\CreditController::class, 'success'])->name('credits.success');

    // Profile
    Route::get('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Guru\ProfileController::class, 'update'])->name('profile.update');
});

// ============================================
// STUDENT ROUTES
// ============================================
Route::prefix('student')->name('student.')->middleware('auth:student')->group(function () {
    Route::get('/dashboard', [StudentDashboard::class, 'index'])->name('dashboard');

    // Test
    Route::get('/test/{package}/start', [App\Http\Controllers\Student\TestController::class, 'start'])->name('test.start');
    Route::post('/test/create-attempt', [App\Http\Controllers\Student\TestController::class, 'createAttempt'])->name('test.create-attempt');
    Route::get('/test/{attempt}/work', [App\Http\Controllers\Student\TestController::class, 'work'])->name('test.work');
    Route::post('/test/{attempt}/save-answer', [App\Http\Controllers\Student\TestController::class, 'saveAnswer'])->name('test.save-answer');
    Route::post('/test/{attempt}/submit', [App\Http\Controllers\Student\TestController::class, 'submit'])->name('test.submit');
    Route::get('/test/{attempt}/continue', [App\Http\Controllers\Student\TestController::class, 'continue'])->name('test.continue');

    // Results
    Route::get('/result/{attempt}', [App\Http\Controllers\Student\ResultController::class, 'show'])->name('test.result');
    Route::get('/result/{attempt}/review', [App\Http\Controllers\Student\ResultController::class, 'review'])->name('test.review');
    Route::get('/history', [App\Http\Controllers\Student\ResultController::class, 'history'])->name('test.history');

    // Profile
    Route::get('/profile', [App\Http\Controllers\Student\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\Student\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [App\Http\Controllers\Student\ProfileController::class, 'updatePassword'])->name('profile.password');
});
