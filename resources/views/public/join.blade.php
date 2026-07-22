<x-layouts.public>
    @section('title', 'Join Listora | Listora.ng')
    <section id="agents" class="mx-auto max-w-2xl px-4 py-8 sm:px-6 md:py-12" x-data="{ registrationOpen: false, registrationTrigger: null, openRegistration(event) { this.registrationTrigger = event.currentTarget; this.registrationOpen = true; this.$nextTick(() => this.$refs.registrationClose?.focus()) }, closeRegistration() { this.registrationOpen = false; this.$nextTick(() => this.registrationTrigger?.focus()) } }">
        <div class="text-center">
            <h1 class="text-[27px] font-semibold tracking-tight text-[#182230] md:text-[32px]">Join Listora</h1>
            <p class="mt-2 text-sm text-[#667085] md:text-[15px]">Choose how you want to use Listora.</p>
        </div>

        <div class="mt-6 grid gap-3">
            <x-role-card icon="building" title="Agent or Property Manager" description="List and manage properties." @click="openRegistration($event)" />
            <x-role-card icon="key" title="Landlord" description="View your properties and reports." @click="openRegistration($event)" />
            <x-role-card icon="user" title="Tenant" description="Manage rent, receipts and requests." @click="openRegistration($event)" />
        </div>

        <p class="mt-6 text-center text-sm text-[#667085]">Already have an account? <button type="button" @click="openLogin()" class="font-medium text-[#145FCC] hover:text-[#0E4DA9]">Sign in</button></p>

        <div x-cloak x-show="registrationOpen" @keydown.escape.window="closeRegistration()" class="fixed inset-0 z-[75] flex items-end justify-center md:items-center md:p-4" role="dialog" aria-modal="true" aria-labelledby="registration-title">
            <button type="button" @click="closeRegistration()" class="absolute inset-0 bg-[#06152D]/75" aria-label="Close registration notice"></button>
            <section x-show="registrationOpen" x-transition @keydown.tab="trapFocus($event, $el)" class="relative w-full rounded-t-2xl bg-white p-5 md:max-w-[410px] md:rounded-xl md:p-6">
                <button x-ref="registrationClose" type="button" @click="closeRegistration()" class="touch-icon absolute right-3 top-3" aria-label="Close"><x-icon name="x" /></button>
                <span class="flex size-10 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="info" class="size-5" /></span>
                <h2 id="registration-title" class="mt-4 text-xl font-semibold text-[#182230]">Registration is currently unavailable.</h2>
                <button type="button" @click="closeRegistration()" class="btn-primary mt-5 w-full">Got it</button>
            </section>
        </div>
    </section>
</x-layouts.public>
