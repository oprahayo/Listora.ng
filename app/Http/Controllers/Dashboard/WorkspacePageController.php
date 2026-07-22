<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspacePageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $role = (string) $request->route('workspaceRole');
        $page = (string) $request->route('workspacePage');
        $pages = $this->pages();
        abort_unless(isset($pages[$role][$page]), 404);

        return view('workspace.page', [
            ...$pages[$role][$page],
            'role' => $role,
            'page' => $page,
            'profile' => match ($role) {
                'landlord' => $request->user()->landlordProfile,
                'tenant' => $request->user()->tenantProfile,
                default => null,
            },
        ]);
    }

    /** @return array<string, array<string, array{title: string, message: string, icon: string, back: string}>> */
    private function pages(): array
    {
        return [
            'agent' => [
                'properties' => ['title' => 'Properties', 'message' => 'No properties added yet.', 'icon' => 'building', 'back' => 'agent.dashboard'],
                'landlords' => ['title' => 'Landlords', 'message' => 'Your landlords will appear here.', 'icon' => 'key', 'back' => 'agent.dashboard'],
                'tenants' => ['title' => 'Tenants', 'message' => 'Your tenants will appear here.', 'icon' => 'users', 'back' => 'agent.dashboard'],
                'more' => ['title' => 'More', 'message' => 'Manage your profile and account.', 'icon' => 'grid', 'back' => 'agent.dashboard'],
            ],
            'landlord' => [
                'properties' => ['title' => 'Properties', 'message' => 'No properties are connected yet.', 'icon' => 'building', 'back' => 'landlord.dashboard'],
                'reports' => ['title' => 'Reports', 'message' => 'No reports are available yet.', 'icon' => 'chart', 'back' => 'landlord.dashboard'],
                'agents' => ['title' => 'Agents', 'message' => 'Connected agents will appear here.', 'icon' => 'users', 'back' => 'landlord.dashboard'],
                'approvals' => ['title' => 'Approvals', 'message' => 'No approvals need your attention.', 'icon' => 'check', 'back' => 'landlord.dashboard'],
                'statements' => ['title' => 'Statements', 'message' => 'No statements are available yet.', 'icon' => 'document', 'back' => 'landlord.dashboard'],
                'more' => ['title' => 'More', 'message' => 'Manage communication preferences and your account.', 'icon' => 'grid', 'back' => 'landlord.dashboard'],
            ],
            'tenant' => [
                'bills' => ['title' => 'Bills', 'message' => 'No bills yet.', 'icon' => 'receipt', 'back' => 'tenant.dashboard'],
                'receipts' => ['title' => 'Receipts', 'message' => 'No receipts yet.', 'icon' => 'document', 'back' => 'tenant.dashboard'],
                'issues' => ['title' => 'Report Issue', 'message' => 'You can report an issue after a tenancy is connected.', 'icon' => 'alert-circle', 'back' => 'tenant.dashboard'],
                'chat' => ['title' => 'Chat', 'message' => 'No conversations yet.', 'icon' => 'chat', 'back' => 'tenant.dashboard'],
                'documents' => ['title' => 'Documents', 'message' => 'No documents yet.', 'icon' => 'document', 'back' => 'tenant.dashboard'],
                'notices' => ['title' => 'Notices', 'message' => 'No notices yet.', 'icon' => 'bell', 'back' => 'tenant.dashboard'],
                'credit' => ['title' => 'Credit balance', 'message' => 'No credit balance yet.', 'icon' => 'wallet', 'back' => 'tenant.more'],
                'refunds' => ['title' => 'Refunds', 'message' => 'No refunds yet.', 'icon' => 'wallet', 'back' => 'tenant.more'],
                'support' => ['title' => 'Support', 'message' => 'No active requests.', 'icon' => 'chat', 'back' => 'tenant.dashboard'],
                'more' => ['title' => 'More', 'message' => 'Manage your tenancy information and account.', 'icon' => 'grid', 'back' => 'tenant.dashboard'],
            ],
            'admin' => [
                'users' => ['title' => 'Users', 'message' => 'User administration is not needed for this account review.', 'icon' => 'users', 'back' => 'admin.dashboard'],
                'more' => ['title' => 'More', 'message' => 'Manage your administrator account.', 'icon' => 'grid', 'back' => 'admin.dashboard'],
            ],
        ];
    }
}
