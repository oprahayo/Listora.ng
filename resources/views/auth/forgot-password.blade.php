<x-layouts.public>
    @section('title', 'Password recovery | Listora.ng')
    <section class="mx-auto max-w-xl px-4 py-12 text-center sm:px-6 md:py-20">
        <span class="mx-auto flex size-12 items-center justify-center rounded-xl bg-[#EEF4FF] text-[#155EEF]"><x-icon name="lock" class="size-6" /></span>
        <h1 class="mt-6 text-[26px] font-semibold tracking-tight text-[#172033] md:text-3xl">Password recovery is currently unavailable</h1>
        <p class="mt-3 text-sm leading-6 text-[#667085] md:text-base">Please try again later or return home to continue browsing properties.</p>
        <a href="{{ route('home') }}" class="btn-primary mt-6">Back to Listora</a>
    </section>
</x-layouts.public>
