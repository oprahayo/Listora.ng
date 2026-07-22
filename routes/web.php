<?php

use App\Http\Controllers\Admin\VerificationController as AdminVerificationController;
use App\Http\Controllers\Auth\DashboardController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\Auth\PhoneVerificationController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\WorkspaceController;
use App\Http\Controllers\Dashboard\RoleDashboardController;
use App\Http\Controllers\Dashboard\WorkspacePageController;
use App\Http\Controllers\Invitations\AgentInvitationController;
use App\Http\Controllers\Invitations\InvitationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\Onboarding\AgentOnboardingController;
use App\Http\Controllers\Onboarding\LandlordOnboardingController;
use App\Http\Controllers\Onboarding\OnboardingController;
use App\Http\Controllers\Onboarding\TenantOnboardingController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\PublicAgentController;
use App\Http\Controllers\PublicHomeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\Verification\VerificationDocumentController;
use App\Http\Middleware\PrivateCachePolicy;
use App\Http\Middleware\PublicCachePolicy;
use Illuminate\Support\Facades\Route;

Route::middleware(PublicCachePolicy::class)->group(function (): void {
    Route::get('/', PublicHomeController::class)->name('home');
    Route::get('/properties', [PropertyController::class, 'index'])->name('properties.index');
    Route::get('/properties/{property:slug}', [PropertyController::class, 'show'])->name('properties.show');
    Route::get('/saved', [PropertyController::class, 'saved'])->name('saved');
});

Route::get('/saved/property-summaries', [PropertyController::class, 'savedSummaries'])->name('saved.summaries');

Route::post('/auth/login', [SessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
Route::post('/auth/otp/request', [OtpController::class, 'store'])->middleware('throttle:otp')->name('otp.request');
Route::post('/auth/otp/confirm', [OtpController::class, 'confirm'])->middleware('throttle:otp')->name('otp.confirm');
Route::post('/auth/logout', [SessionController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/auth/csrf-token', fn () => response()->json(['token' => csrf_token()]))->name('csrf.refresh');

Route::get('/join', [RegistrationController::class, 'create'])->middleware('guest')->name('join');
Route::post('/register', [RegistrationController::class, 'store'])->middleware(['guest', 'throttle:6,1'])->name('register');
Route::get('/invitations/{token}', [InvitationController::class, 'show'])->middleware(PrivateCachePolicy::class)->name('invitations.show');
Route::post('/invitations/{token}/accept', [InvitationController::class, 'accept'])->middleware([PrivateCachePolicy::class, 'throttle:6,1'])->name('invitations.accept');
Route::view('/forgot-password', 'auth.forgot-password')->name('password.request');
Route::view('/offline', 'public.offline')->name('offline');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/manifest.webmanifest', fn () => response()->file(public_path('manifest.webmanifest'), [
    'Content-Type' => 'application/manifest+json',
]))->name('manifest');

Route::get('/service-worker.js', fn () => response()->file(public_path('service-worker.js'), [
    'Content-Type' => 'application/javascript',
    'Service-Worker-Allowed' => '/',
]))->name('service-worker');

Route::middleware(['auth', PrivateCachePolicy::class])->group(function (): void {
    Route::get('/verify-phone', [PhoneVerificationController::class, 'show'])->name('phone.verify');
    Route::post('/verify-phone/request', [PhoneVerificationController::class, 'request'])->middleware('throttle:otp')->name('phone.request');
    Route::post('/verify-phone/confirm', [PhoneVerificationController::class, 'confirm'])->middleware('throttle:otp')->name('phone.confirm');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])->middleware('throttle:6,1')->name('verification.send');

    Route::get('/onboarding', OnboardingController::class)->name('onboarding.index');
    Route::get('/onboarding/agent', [AgentOnboardingController::class, 'show'])->middleware('role:agent')->name('onboarding.agent');
    Route::post('/onboarding/agent', [AgentOnboardingController::class, 'store'])->middleware('role:agent')->name('onboarding.agent.store');
    Route::post('/onboarding/agent/autosave', [AgentOnboardingController::class, 'autosave'])->middleware(['role:agent', 'throttle:30,1'])->name('onboarding.agent.autosave');
    Route::get('/onboarding/landlord', [LandlordOnboardingController::class, 'show'])->middleware('role:landlord')->name('onboarding.landlord');
    Route::post('/onboarding/landlord', [LandlordOnboardingController::class, 'store'])->middleware('role:landlord')->name('onboarding.landlord.store');
    Route::get('/onboarding/tenant', [TenantOnboardingController::class, 'show'])->middleware('role:tenant')->name('onboarding.tenant');
    Route::post('/onboarding/tenant', [TenantOnboardingController::class, 'store'])->middleware('role:tenant')->name('onboarding.tenant.store');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/workspace', [WorkspaceController::class, 'index'])->name('workspace.index');
    Route::post('/workspace/switch', [WorkspaceController::class, 'switch'])->name('workspace.switch');

    Route::middleware('role:agent')->group(function (): void {
        Route::get('/agent/dashboard', [RoleDashboardController::class, 'agent'])->name('agent.dashboard');
        Route::get('/agent/properties', WorkspacePageController::class)->defaults('workspaceRole', 'agent')->defaults('workspacePage', 'properties')->name('agent.properties.index');
        Route::get('/agent/landlords', WorkspacePageController::class)->defaults('workspaceRole', 'agent')->defaults('workspacePage', 'landlords')->name('agent.landlords');
        Route::get('/agent/tenants', WorkspacePageController::class)->defaults('workspaceRole', 'agent')->defaults('workspacePage', 'tenants')->name('agent.tenants');
        Route::get('/agent/more', WorkspacePageController::class)->defaults('workspaceRole', 'agent')->defaults('workspacePage', 'more')->name('agent.more');
        Route::get('/agent/invitations', [AgentInvitationController::class, 'index'])->name('agent.invitations.index');
        Route::post('/agent/invitations', [AgentInvitationController::class, 'store'])->name('agent.invitations.store');
        Route::post('/agent/invitations/{invitation}/resend', [AgentInvitationController::class, 'resend'])->name('agent.invitations.resend');
        Route::delete('/agent/invitations/{invitation}', [AgentInvitationController::class, 'destroy'])->name('agent.invitations.destroy');
    });
    Route::middleware('role:landlord')->group(function (): void {
        Route::get('/landlord/dashboard', [RoleDashboardController::class, 'landlord'])->name('landlord.dashboard');
        Route::get('/landlord/properties', WorkspacePageController::class)->defaults('workspaceRole', 'landlord')->defaults('workspacePage', 'properties')->name('landlord.properties');
        Route::get('/landlord/reports', WorkspacePageController::class)->defaults('workspaceRole', 'landlord')->defaults('workspacePage', 'reports')->name('landlord.reports');
        Route::get('/landlord/agents', WorkspacePageController::class)->defaults('workspaceRole', 'landlord')->defaults('workspacePage', 'agents')->name('landlord.agents');
        Route::get('/landlord/approvals', WorkspacePageController::class)->defaults('workspaceRole', 'landlord')->defaults('workspacePage', 'approvals')->name('landlord.approvals');
        Route::get('/landlord/statements', WorkspacePageController::class)->defaults('workspaceRole', 'landlord')->defaults('workspacePage', 'statements')->name('landlord.statements');
        Route::get('/landlord/more', WorkspacePageController::class)->defaults('workspaceRole', 'landlord')->defaults('workspacePage', 'more')->name('landlord.more');
    });
    Route::middleware('role:tenant')->group(function (): void {
        Route::get('/tenant/dashboard', [RoleDashboardController::class, 'tenant'])->name('tenant.dashboard');
        Route::get('/tenant/bills', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'bills')->name('tenant.bills');
        Route::get('/tenant/receipts', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'receipts')->name('tenant.receipts');
        Route::get('/tenant/report-issue', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'issues')->name('tenant.issues');
        Route::get('/tenant/chat', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'chat')->name('tenant.chat');
        Route::get('/tenant/documents', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'documents')->name('tenant.documents');
        Route::get('/tenant/notices', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'notices')->name('tenant.notices');
        Route::get('/tenant/credit', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'credit')->name('tenant.credit');
        Route::get('/tenant/refunds', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'refunds')->name('tenant.refunds');
        Route::get('/tenant/support', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'support')->name('tenant.support');
        Route::get('/tenant/more', WorkspacePageController::class)->defaults('workspaceRole', 'tenant')->defaults('workspacePage', 'more')->name('tenant.more');
    });

    Route::middleware('role:admin')->group(function (): void {
        Route::get('/admin/dashboard', [RoleDashboardController::class, 'admin'])->name('admin.dashboard');
        Route::get('/admin/users', WorkspacePageController::class)->defaults('workspaceRole', 'admin')->defaults('workspacePage', 'users')->name('admin.users');
        Route::get('/admin/more', WorkspacePageController::class)->defaults('workspaceRole', 'admin')->defaults('workspacePage', 'more')->name('admin.more');
        Route::get('/admin/verifications', [AdminVerificationController::class, 'index'])->name('admin.verifications.index');
        Route::get('/admin/verifications/{verificationRequest}', [AdminVerificationController::class, 'show'])->name('admin.verifications.show');
        Route::post('/admin/verifications/{verificationRequest}/approve', [AdminVerificationController::class, 'approve'])->name('admin.verifications.approve');
        Route::post('/admin/verifications/{verificationRequest}/request-correction', [AdminVerificationController::class, 'correction'])->name('admin.verifications.correction');
        Route::post('/admin/verifications/{verificationRequest}/reject', [AdminVerificationController::class, 'reject'])->name('admin.verifications.reject');
    });

    Route::get('/verification-documents/{document}', [VerificationDocumentController::class, 'show'])->name('verification-documents.show');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
});

Route::get('/agent/{agentProfile:public_slug}', PublicAgentController::class)->name('agents.show');
