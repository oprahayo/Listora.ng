<x-layouts.app>
    @section('title', $title.' | Listora.ng')
    @section('app_title', $title)
    @section('app_back', route($back))
    <div class="mx-auto max-w-[760px]">
        <x-mobile-page-header :title="$title" />
        @if($page === 'more')
            @php
                $links = match($role) {
                    'agent' => [
                        ['shield-check', 'Verification', route('onboarding.agent')],
                        ['eye', 'Public profile', auth()->user()->agent ? route('agents.show', auth()->user()->agent) : route('onboarding.agent')],
                        ['bell', 'Notifications', route('notifications.index')],
                    ],
                    'landlord' => [
                        ['users', 'Agents', route('landlord.agents')],
                        ['document', 'Statements', route('landlord.statements')],
                        ['settings', 'Communication preferences', route('onboarding.landlord', ['step' => 2])],
                    ],
                    'tenant' => [
                        ['document', 'Receipts', route('tenant.receipts')],
                        ['document', 'Documents', route('tenant.documents')],
                        ['bell', 'Notices', route('tenant.notices')],
                        ['wallet', 'Credit balance', route('tenant.credit')],
                        ['wallet', 'Refunds', route('tenant.refunds')],
                        ['settings', 'Communication preferences', route('onboarding.tenant', ['step' => 2])],
                        ['user', 'Personal details', route('onboarding.tenant')],
                    ],
                    default => [
                        ['bell', 'Notifications', route('notifications.index')],
                        ['users', 'Users', route('admin.users')],
                    ],
                };
            @endphp
            <div class="overflow-hidden rounded-xl border border-[#E4E7EC] bg-white">@foreach($links as [$linkIcon, $linkLabel, $linkUrl])<a href="{{ $linkUrl }}" class="flex min-h-14 items-center gap-3 border-b border-[#E4E7EC] px-4 last:border-0"><span class="text-[#145FCC]"><x-icon :name="$linkIcon" /></span><span class="flex-1 text-sm font-medium">{{ $linkLabel }}</span><x-icon name="chevron-right" class="size-4 text-[#98A2B3]" /></a>@endforeach</div>
            @if(in_array($role, ['landlord', 'tenant'], true) && $profile)<p class="mt-4 rounded-lg bg-[#F2F4F7] p-3 text-sm text-[#667085]">Preferred contact: <span class="font-medium text-[#344054]">{{ str($profile->preferred_contact_method)->headline() }}</span></p>@endif
            @if(auth()->user()->roles()->count() > 1)<a href="{{ route('workspace.index') }}" class="btn-secondary mt-4 w-full"><x-icon name="users" class="size-4" />Switch workspace</a>@endif
            <form action="{{ route('logout') }}" method="POST" class="mt-3">@csrf<button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-lg border border-[#E4E7EC] bg-white text-sm font-medium text-[#C92A2A]"><x-icon name="logout" class="size-4" />Sign out</button></form>
        @else
            <x-empty-state :icon="$icon" :title="$message" message="This area will update automatically when records are connected to your account." />
        @endif
    </div>
</x-layouts.app>
