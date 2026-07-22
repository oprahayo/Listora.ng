<x-layouts.app>
    @section('title', 'Choose workspace | Listora.ng')
    @section('app_title', 'Choose workspace')
    @section('hide_app_navigation', '1')
    <section class="mx-auto max-w-xl" aria-labelledby="workspace-title">
        <div class="text-center"><span class="mx-auto flex size-11 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="users" /></span><h1 id="workspace-title" class="mt-4 text-[23px] font-semibold tracking-tight md:text-[28px]">Choose a workspace</h1><p class="mt-2 text-sm text-[#667085]">Select how you want to use Listora right now.</p></div>
        <div class="mt-6"><x-workspace-switcher :roles="$roles" /></div>
    </section>
</x-layouts.app>
