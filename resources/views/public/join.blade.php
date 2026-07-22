<x-layouts.public>
    @section('title', 'Create your Listora account | Listora.ng')
    <section id="agents" class="mx-auto max-w-2xl px-4 py-8 sm:px-6 md:py-12"
        x-data="{ registrationOpen: {{ $errors->any() ? 'true' : 'false' }}, selectedRole: @js(old('role')), step: {{ old('password') ? 2 : 1 }}, trigger: null,
            openRegistration(role, event) { this.selectedRole = role; this.step = 1; this.trigger = event.currentTarget; this.registrationOpen = true; this.$nextTick(() => this.$refs.fullName?.focus()) },
            closeRegistration() { this.registrationOpen = false; this.$nextTick(() => this.trigger?.focus()) } }">
        <div class="text-center">
            <h1 class="text-[27px] font-semibold tracking-tight text-[#182230] md:text-[32px]">Create one Listora account</h1>
            <p class="mt-2 text-sm text-[#667085] md:text-[15px]">Choose where you want to start. You can add another workspace later.</p>
        </div>

        <div class="mt-6 grid gap-3">
            <x-role-card icon="building" title="Agent or Property Manager" description="List and manage properties." @click="openRegistration('agent', $event)" />
            <x-role-card icon="key" title="Landlord" description="View properties, income and reports." @click="openRegistration('landlord', $event)" />
            <x-role-card icon="user" title="Tenant" description="Manage rent, receipts and requests." @click="openRegistration('tenant', $event)" />
        </div>

        <p class="mt-6 text-center text-sm text-[#667085]">Already have an account? <button type="button" @click="openLogin()" class="font-medium text-[#145FCC] hover:text-[#0E4DA9]">Sign in</button></p>

        <div x-cloak x-show="registrationOpen" @keydown.escape.window="closeRegistration()" class="fixed inset-0 z-[75] flex items-end justify-center md:items-center md:p-4" role="dialog" aria-modal="true" aria-labelledby="registration-title">
            <button type="button" @click="closeRegistration()" class="absolute inset-0 bg-[#06152D]/75" aria-label="Close registration"></button>
            <section x-show="registrationOpen" x-transition @keydown.tab="trapFocus($event, $el)" class="relative max-h-[92dvh] w-full overflow-y-auto rounded-t-2xl bg-white px-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] pt-4 md:max-w-[460px] md:rounded-xl md:p-6">
                <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-[#D0D5DD] md:hidden"></div>
                <button type="button" @click="closeRegistration()" class="touch-icon absolute right-3 top-3" aria-label="Close"><x-icon name="x" /></button>
                <p class="text-xs font-medium text-[#145FCC]" x-text="step === 1 ? '1 of 2  ● ○' : '2 of 2  ● ●'"></p>
                <h2 id="registration-title" class="mt-2 text-xl font-semibold text-[#182230]" x-text="selectedRole === 'agent' ? 'Agent account' : selectedRole === 'landlord' ? 'Landlord account' : 'Tenant account'"></h2>

                <form action="{{ route('register') }}" method="POST" class="mt-5" @submit="if (window.LISTORA_STATIC_PREVIEW) { $event.preventDefault(); showToast('Preview only. No information was submitted.') }">
                    @csrf
                    <input type="hidden" name="role" :value="selectedRole">
                    <div x-show="step === 1" class="grid gap-3">
                        <div><label for="register-name" class="form-label">Full name</label><input x-ref="fullName" id="register-name" name="name" value="{{ old('name') }}" autocomplete="name" class="form-input" required>@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
                        <div><label for="register-phone" class="form-label">Phone number</label><input id="register-phone" name="phone" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" class="form-input" placeholder="0803 123 4567" required>@error('phone')<p class="form-error">{{ $message }}</p>@enderror</div>
                        <div><label for="register-email" class="form-label">Email address</label><input id="register-email" name="email" value="{{ old('email') }}" autocomplete="email" inputmode="email" type="email" class="form-input" required>@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
                        @if($errors->has('phone') || $errors->has('email'))
                            <div class="rounded-lg border border-[#B8C8E1] bg-[#F8FAFF] p-3 text-sm text-[#344054]">
                                <p class="font-medium text-[#0A2856]">Already registered?</p>
                                <div class="mt-2 flex flex-wrap gap-3"><button type="button" @click="closeRegistration(); openLogin()" class="font-medium text-[#145FCC]">Sign in</button><a href="{{ route('password.request') }}" class="font-medium text-[#145FCC]">Reset password</a><span>Add this role after signing in</span></div>
                            </div>
                        @endif
                        <button type="button" @click="step = 2; $nextTick(() => document.getElementById('register-password')?.focus())" class="btn-primary mt-1 w-full">Continue</button>
                    </div>

                    <div x-cloak x-show="step === 2" class="grid gap-3">
                        <div><label for="register-password" class="form-label">Create password</label><input id="register-password" name="password" type="password" autocomplete="new-password" class="form-input" minlength="8" required>@error('password')<p class="form-error">{{ $message }}</p>@enderror</div>
                        <div><label for="register-password-confirmation" class="form-label">Confirm password</label><input id="register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="form-input" minlength="8" required></div>
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg bg-[#F7F8FA] p-3 text-sm text-[#475467]"><input name="terms" value="1" type="checkbox" class="mt-0.5 size-5 rounded border-[#98A2B3] text-[#145FCC]" required><span>I accept the <a href="#" class="font-medium text-[#145FCC]">Terms</a> and <a href="#" class="font-medium text-[#145FCC]">Privacy Policy</a>.</span></label>
                        @error('terms')<p class="form-error">{{ $message }}</p>@enderror
                        <div class="grid grid-cols-[auto_1fr] gap-2"><button type="button" @click="step = 1" class="btn-secondary">Back</button><button type="submit" class="btn-primary">Create account</button></div>
                    </div>
                </form>
            </section>
        </div>
    </section>
</x-layouts.public>
