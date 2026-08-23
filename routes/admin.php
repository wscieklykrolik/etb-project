<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminMatchController;
use App\Http\Controllers\Admin\AdminNotificationController;
use App\Http\Controllers\Admin\AcademyCalendarNoteController;
use App\Http\Controllers\Admin\AcademyGroupController;
use App\Http\Controllers\Admin\AcademyMessageController;
use App\Http\Controllers\Admin\AcademyTrainerController;
use App\Http\Controllers\Admin\AcademyTrainingController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClubSectionController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\FaqQuestionController;
use App\Http\Controllers\Admin\LeagueTableController;
use App\Http\Controllers\Admin\MatchSuggestionController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductFilterController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SiteLogoController;
use App\Http\Controllers\Admin\ThreeXThreeTournamentDrawController;
use App\Http\Controllers\Admin\ThreeXThreeTournamentGroupController;
use App\Http\Controllers\Admin\ThreeXThreeTournamentMatchController;
use App\Http\Controllers\Admin\UserEmailExportController;
use App\Http\Controllers\Admin\UserRoleController;
use App\Http\Controllers\Admin\UserSearchController;
use App\Http\Controllers\SponsorController;
use App\Http\Controllers\SponsorCategoryController;
use App\Http\Controllers\TeamStaffController;
use App\Http\Controllers\ThreeXThreeMemberController;
use App\Http\Controllers\ThreeXThreeTournamentController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,employee'])->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::middleware('can:manage-matches')->group(function () {
        Route::get('/admin/matches/create', [AdminMatchController::class, 'create'])->name('admin.matches.create');
        Route::post('/admin/matches', [AdminMatchController::class, 'store'])->name('admin.matches.store');
    });

    Route::get('/admin/match-suggestions/locations', [MatchSuggestionController::class, 'locations'])->name('admin.match-suggestions.locations');
    Route::get('/admin/match-suggestions/opponents', [MatchSuggestionController::class, 'opponents'])->name('admin.match-suggestions.opponents');
    Route::patch('/admin/notifications/read-all', [AdminNotificationController::class, 'readAll'])->name('admin.notifications.read-all');
    Route::patch('/admin/notifications/{notification}/read', [AdminNotificationController::class, 'read'])->name('admin.notifications.read');
    Route::patch('/admin/notifications/{notification}/accept', [AdminNotificationController::class, 'accept'])->name('admin.notifications.accept');
    Route::delete('/admin/notifications/{notification}', [AdminNotificationController::class, 'destroy'])->name('admin.notifications.destroy');
    Route::put('/admin/club-sections/{section}', [ClubSectionController::class, 'update'])->name('admin.club-sections.update');
    Route::patch('/admin/club-sections/{section}/images/{image}', [ClubSectionController::class, 'updateImage'])->name('admin.club-sections.images.update');
    Route::delete('/admin/club-sections/{section}/images/{image}', [ClubSectionController::class, 'destroyImage'])->name('admin.club-sections.images.destroy');
    Route::post('/admin/academy/groups', [AcademyGroupController::class, 'store'])->name('admin.academy.groups.store');
    Route::patch('/admin/academy/groups/{group}', [AcademyGroupController::class, 'update'])->name('admin.academy.groups.update');
    Route::delete('/admin/academy/groups/{group}', [AcademyGroupController::class, 'destroy'])->name('admin.academy.groups.destroy');
    Route::post('/admin/academy/groups/{group}/trainers', [AcademyTrainerController::class, 'store'])->name('admin.academy.groups.trainers.store');
    Route::post('/admin/academy/groups/{group}/messages', [AcademyMessageController::class, 'store'])->name('admin.academy.groups.messages.store');
    Route::post('/admin/academy/trainings', [AcademyTrainingController::class, 'store'])->name('admin.academy.trainings.store');
    Route::patch('/admin/academy/trainings/{training}', [AcademyTrainingController::class, 'update'])->name('admin.academy.trainings.update');
    Route::patch('/admin/academy/trainings/{training}/cancel', [AcademyTrainingController::class, 'cancel'])->name('admin.academy.trainings.cancel');
    Route::patch('/admin/academy/trainings/{training}/restore', [AcademyTrainingController::class, 'restore'])->name('admin.academy.trainings.restore');
    Route::delete('/admin/academy/trainings/{training}', [AcademyTrainingController::class, 'destroy'])->name('admin.academy.trainings.destroy');
    Route::post('/admin/academy/calendar-notes', [AcademyCalendarNoteController::class, 'store'])->name('admin.academy.calendar-notes.store');
    Route::delete('/admin/academy/calendar-notes/{note}', [AcademyCalendarNoteController::class, 'destroy'])->name('admin.academy.calendar-notes.destroy');
    Route::get('/admin/academy/trainers/suggestions', [AcademyTrainerController::class, 'suggestions'])->name('admin.academy.trainers.suggestions');
    Route::patch('/admin/academy/trainers/{trainer}', [AcademyTrainerController::class, 'update'])->name('admin.academy.trainers.update');
    Route::delete('/admin/academy/trainers/{trainer}', [AcademyTrainerController::class, 'destroy'])->name('admin.academy.trainers.destroy');
    Route::patch('/admin/academy/messages/{message}', [AcademyMessageController::class, 'update'])->name('admin.academy.messages.update');
    Route::delete('/admin/academy/messages/{message}', [AcademyMessageController::class, 'destroy'])->name('admin.academy.messages.destroy');
    Route::resource('/admin/faq', FaqQuestionController::class)
        ->only(['store', 'update', 'destroy'])
        ->names('admin.faq')
        ->parameters(['faq' => 'question']);
    Route::post('/admin/league-table/sync', [LeagueTableController::class, 'sync'])->name('admin.league-table.sync');
    Route::patch('/admin/opponents/{opponent}', [LeagueTableController::class, 'updateOpponent'])->name('admin.opponents.update');
    Route::post('/admin/3x3/tournaments/{tournament}/groups', [ThreeXThreeTournamentGroupController::class, 'store'])->name('admin.3x3.tournaments.groups.store');
    Route::patch('/admin/3x3/tournaments/{tournament}/groups/{group}', [ThreeXThreeTournamentGroupController::class, 'update'])->name('admin.3x3.tournaments.groups.update');
    Route::delete('/admin/3x3/tournaments/{tournament}/groups/{group}', [ThreeXThreeTournamentGroupController::class, 'destroy'])->name('admin.3x3.tournaments.groups.destroy');
    Route::post('/admin/3x3/tournaments/{tournament}/matches', [ThreeXThreeTournamentMatchController::class, 'store'])->name('admin.3x3.tournaments.matches.store');
    Route::patch('/admin/3x3/tournaments/{tournament}/matches/{match}', [ThreeXThreeTournamentMatchController::class, 'update'])->name('admin.3x3.tournaments.matches.update');
    Route::delete('/admin/3x3/tournaments/{tournament}/matches/{match}', [ThreeXThreeTournamentMatchController::class, 'destroy'])->name('admin.3x3.tournaments.matches.destroy');
    Route::post('/admin/3x3/tournaments/{tournament}/draw', [ThreeXThreeTournamentDrawController::class, 'draw'])->name('admin.3x3.tournaments.draw');
    Route::post('/admin/3x3/tournaments/{tournament}/playoff/refresh', [ThreeXThreeTournamentDrawController::class, 'refreshPlayoff'])->name('admin.3x3.tournaments.playoff.refresh');
    Route::resource('/admin/staff', TeamStaffController::class)->only(['store', 'update', 'destroy'])->parameters(['staff' => 'staff']);
    Route::resource('/admin/3x3/members', ThreeXThreeMemberController::class)->only(['store', 'update', 'destroy'])->parameters(['members' => 'member']);
    Route::resource('/admin/3x3/tournaments', ThreeXThreeTournamentController::class)->only(['store', 'update', 'destroy'])->parameters(['tournaments' => 'tournament']);
    Route::resource('/admin/sponsors', SponsorController::class)->only(['store', 'update', 'destroy']);
    Route::resource('/admin/sponsor-categories', SponsorCategoryController::class)->only(['store', 'update', 'destroy']);

    Route::resource('/admin/products', ProductController::class)->names('admin.products');
    Route::post('/admin/products/{product}/variants', [ProductController::class, 'addVariant'])->name('admin.products.variants.store');
    Route::delete('/admin/products/{product}/variants/{variant}', [ProductController::class, 'removeVariant'])->name('admin.products.variants.destroy');
    Route::get('/admin/product-filters', [ProductFilterController::class, 'index'])->name('admin.product-filters.index');
    Route::post('/admin/product-filters/groups', [ProductFilterController::class, 'storeGroup'])->name('admin.product-filters.groups.store');
    Route::patch('/admin/product-filters/groups/{group}', [ProductFilterController::class, 'updateGroup'])->name('admin.product-filters.groups.update');
    Route::delete('/admin/product-filters/groups/{group}', [ProductFilterController::class, 'destroyGroup'])->name('admin.product-filters.groups.destroy');
    Route::post('/admin/product-filters/groups/{group}/options', [ProductFilterController::class, 'storeOption'])->name('admin.product-filters.options.store');
    Route::patch('/admin/product-filters/groups/{group}/options/{option}', [ProductFilterController::class, 'updateOption'])->name('admin.product-filters.options.update');
    Route::delete('/admin/product-filters/groups/{group}/options/{option}', [ProductFilterController::class, 'destroyOption'])->name('admin.product-filters.options.destroy');
    Route::resource('/admin/categories', CategoryController::class)->names('admin.categories');
    Route::resource('/admin/orders', OrderController::class)->names('admin.orders')->only(['index', 'show']);
    Route::patch('/admin/orders/{order}/transition', [OrderController::class, 'transition'])->name('admin.orders.transition');
    Route::get('/admin/orders/{order}/invoice', [OrderController::class, 'downloadInvoice'])->name('admin.orders.invoice');
    Route::post('/admin/orders/{order}/label', [OrderController::class, 'generateLabel'])->name('admin.orders.label');
    Route::get('/admin/export/jpk', [ExportController::class, 'jpk'])->name('admin.export.jpk');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::patch('/admin/site-logos/{logo}', [SiteLogoController::class, 'update'])->name('admin.site-logos.update');
    Route::delete('/admin/site-logos/{logo}', [SiteLogoController::class, 'destroy'])->name('admin.site-logos.destroy');
    Route::patch('/admin/users/{user}/role', [UserRoleController::class, 'update'])->name('admin.users.role.update');
    Route::get('/admin/users/search', UserSearchController::class)->name('admin.users.search');
    Route::get('/admin/users/emails/export', UserEmailExportController::class)->name('admin.users.emails.export');
});
