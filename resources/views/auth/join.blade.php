<x-layouts.public>
    @section('title', 'Create an account | Listora.ng')
    <section id="agents" class="mx-auto max-w-2xl px-4 py-12 text-center sm:px-6 md:py-20">
        <span class="mx-auto flex size-12 items-center justify-center rounded-xl bg-[#EEF4FF] text-[#155EEF]"><x-icon name="users" class="size-6" /></span>
        <h1 class="mt-6 text-[26px] font-semibold tracking-tight text-[#172033] md:text-3xl">Account registration is currently unavailable</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-[#667085] md:text-base">We’re preparing a simple sign-up experience for agents, landlords and tenants. If you already have an account, you can sign in here.</p>
        <div class="mt-6 flex flex-col justify-center gap-3 sm:flex-row"><a href="{{ route('home') }}" class="btn-secondary">Back home</a><button type="button" @click="openLogin()" class="btn-primary">Sign in to an existing account</button></div>
    </section>
</x-layouts.public>
