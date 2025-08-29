<?php

use App\Events\CampaignProgressUpdated;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\EmailValidationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SmtpSettingController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/campaigns');
});

// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/test-broadcast/{campaignId}', function ($campaignId) {
    $stats = [
        'pending' => rand(0, 5),
        'queued'  => rand(0, 5),
        'sent'    => rand(1, 10),
        'failed'  => rand(0, 2),
    ];

    $line = "[" . now()->format('H:i:s') . "] Test broadcast fired.";

    event(new CampaignProgressUpdated($campaignId, $stats, $line));

    return "Test event for campaign {$campaignId} dispatched!";
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dashboard', function () {
        return redirect('/campaigns');
    });
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');

    Route::post('/campaigns/{campaign}/upload', [CampaignController::class, 'uploadRecipients'])->name('campaigns.upload');
    Route::post('/campaigns/{campaign}/schedule', [CampaignController::class, 'schedule'])->name('campaigns.schedule');
    Route::post('/campaigns/{campaign}/start', [CampaignController::class, 'startNow'])->name('campaigns.start');
    Route::post('/campaigns/{campaign}/pause', [CampaignController::class, 'pause'])->name('campaigns.pause');
    Route::post('/campaigns/{campaign}/resume', [CampaignController::class, 'resume'])->name('campaigns.resume');
    Route::post('/campaigns/{campaign}/dispatch-next', [CampaignController::class, 'dispatchNext'])->name('campaigns.dispatch-next');

    Route::delete('/campaigns/{campaign}/recipients', [CampaignController::class, 'clearRecipients'])
        ->name('campaigns.recipients.clear');

    Route::get('/campaigns/progress', [CampaignController::class, 'progress']);

    // Route::post('/campaigns/{campaign}/dispatch-batch', [CampaignController::class, 'dispatchNextBatch'])
    //     ->name('campaigns.dispatchBatch');

    Route::get('/validation',           [EmailValidationController::class, 'create'])->name('validation.create');
    Route::post('/validation',           [EmailValidationController::class, 'store'])->name('validation.store');
    Route::get('/validation/{batch}',   [EmailValidationController::class, 'show'])->name('validation.show');
    Route::post('/validation/{batch}/list', [EmailValidationController::class, 'storeList'])->name('validation.storeList');

    Route::resource('smtp', SmtpSettingController::class);
});

require __DIR__ . '/auth.php';
