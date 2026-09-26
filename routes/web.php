<?php

use App\Http\Controllers\Auth\GitHubController;
use App\Http\Controllers\CareerProfileController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\GithubSyncController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\RepositoryController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');
Route::view('connect/github', 'auth.github-connect')->name('github.connect');

Route::get('auth/github', [GitHubController::class, 'redirect'])
    ->name('auth.github.redirect');
Route::get('auth/github/callback', [GitHubController::class, 'callback'])
    ->name('auth.github.callback');
Route::post('github/sync', [GithubSyncController::class, 'store'])
    ->middleware('auth')
    ->name('github.sync');

Route::get('p/{subdomain}', [PortfolioController::class, 'show'])->name('portfolio.public');
Route::get('cv/share/{token}', [CareerProfileController::class, 'shared'])->name('cv.shared');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('repositories', [RepositoryController::class, 'index'])->name('repositories.index');
    Route::put('repositories/selection', [RepositoryController::class, 'updateSelection'])->name('repositories.selection');
    Route::get('analysis', [CareerProfileController::class, 'analysis'])->name('analysis.index');
    Route::post('analysis', [CareerProfileController::class, 'analyze'])->name('analysis.run');
    Route::get('career-profile', [CareerProfileController::class, 'show'])->name('career-profile.index');
    Route::put('career-profile', [CareerProfileController::class, 'update'])->name('career-profile.update');
    Route::get('cv', [CvController::class, 'show'])->name('cv.index');
    Route::put('cv', [CvController::class, 'update'])->name('cv.update');
    Route::get('cv/download', [CvController::class, 'download'])->name('cv.download');
    Route::post('cv/share', [CareerProfileController::class, 'share'])->name('cv.share');
    Route::delete('cv/share', [CareerProfileController::class, 'revokeShare'])->name('cv.share.revoke');
    Route::view('settings', 'settings.index')->name('settings.index');
    Route::get('portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');
    Route::get('portfolio/create', [PortfolioController::class, 'create'])->name('portfolio.create');
    Route::post('portfolio', [PortfolioController::class, 'store'])->name('portfolio.store');
    Route::get('portfolio/{portfolio}/edit', [PortfolioController::class, 'edit'])->name('portfolio.edit');
    Route::put('portfolio/{portfolio}', [PortfolioController::class, 'update'])->name('portfolio.update');
    Route::get('portfolio/{portfolio}/preview', [PortfolioController::class, 'preview'])->name('portfolio.preview');
    Route::post('portfolio/{portfolio}/publish', [PortfolioController::class, 'publish'])->name('portfolio.publish');
    Route::post('portfolio/{portfolio}/unpublish', [PortfolioController::class, 'unpublish'])->name('portfolio.unpublish');
    Route::post('portfolio/{portfolio}/regenerate', [PortfolioController::class, 'regenerate'])->name('portfolio.regenerate');
});

Route::get('dashboard', function () {
    $user = request()->user();

    return view('dashboard', [
        'latestSync' => $user->githubSyncs()->latest()->first(),
        'projectCount' => $user->projects()->count(),
        'latestProjects' => $user->projects()->latest('pushed_at')->take(4)->get(),
        'portfolio' => $user->portfolios()->latest()->first(),
    ]);
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';
