<x-layouts.public>
    @section('title', 'Choose workspace | Listora.ng')
    <section class="mx-auto max-w-xl px-4 py-8 sm:px-6 md:py-12" aria-labelledby="workspace-title">
        <div class="text-center">
            <span class="mx-auto flex size-11 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="users" class="size-5" /></span>
            <h1 id="workspace-title" class="mt-4 text-[27px] font-semibold tracking-tight text-[#182230] md:text-[32px]">Choose a workspace</h1>
            <p class="mt-2 text-sm text-[#667085]">Select how you want to use Listora right now.</p>
        </div>

        <div class="mt-6 grid gap-3">
            @forelse($roles as $role)
                @php($icon = match($role->name) { 'agent' => 'building', 'landlord' => 'key', 'tenant' => 'user', 'admin' => 'shield-check', default => 'users' })
                <form action="{{ route('workspace.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role->name }}">
                    <button class="group flex w-full items-center gap-4 rounded-xl border border-[#E4E7EC] bg-white p-4 text-left shadow-[0_1px_3px_rgba(10,40,86,.06)] transition hover:border-[#B8C8E1] hover:bg-[#F8FAFF] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#145FCC]">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon :name="$icon" class="size-5" /></span>
                        <span class="min-w-0 flex-1"><span class="block text-[16px] font-semibold text-[#182230]">{{ $role->display_name }}</span><span class="mt-1 block text-sm text-[#667085]">Open this workspace</span></span>
                        <x-icon name="arrow-right" class="size-5 shrink-0 text-[#98A2B3] transition group-hover:translate-x-0.5 group-hover:text-[#145FCC]" />
                    </button>
                </form>
            @empty
                <div class="rounded-xl border border-[#E4E7EC] bg-white p-5 text-center text-sm text-[#667085]">No workspace is assigned to this account. Contact Listora support.</div>
            @endforelse
        </div>
    </section>
</x-layouts.public>
