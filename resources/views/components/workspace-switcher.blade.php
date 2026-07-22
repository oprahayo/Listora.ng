@props(['roles'])
<div class="grid gap-3">
    @foreach($roles as $role)
        @php($icon = match($role->name) { 'agent' => 'building', 'landlord' => 'key', 'tenant' => 'user', 'admin' => 'shield-check', default => 'users' })
        <form action="{{ route('workspace.switch') }}" method="POST">@csrf<input type="hidden" name="role" value="{{ $role->name }}"><button class="group flex min-h-16 w-full items-center gap-3 rounded-xl border border-[#E4E7EC] bg-white p-3 text-left transition hover:border-[#B8C8E1] hover:bg-[#F8FAFF]"><span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon :name="$icon" /></span><span class="min-w-0 flex-1"><span class="block text-[15px] font-semibold">{{ $role->display_name }}</span><span class="block text-xs text-[#667085]">Open this workspace</span></span><x-icon name="chevron-right" class="size-5 text-[#98A2B3]" /></button></form>
    @endforeach
</div>
