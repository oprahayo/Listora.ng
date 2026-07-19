@guest
<div x-cloak x-show="loginOpen" @keydown.escape.window="closeLogin()" class="fixed inset-0 z-[70]" aria-live="polite">
    <div x-show="loginOpen" x-transition.opacity.duration.150ms class="absolute inset-0 bg-[#06152D]/75" @click="if (!loginLoading) closeLogin()"></div>
    <section x-show="loginOpen" x-transition:enter="transition duration-150" x-transition:enter-start="translate-y-full opacity-0 md:translate-y-3" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition duration-150" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0 md:translate-y-3" @keydown.tab="trapFocus($event, $el)" class="absolute inset-x-0 bottom-0 max-h-[92dvh] overflow-y-auto rounded-t-2xl bg-white px-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] pt-3 shadow-2xl md:inset-auto md:left-1/2 md:top-1/2 md:w-[420px] md:max-w-[calc(100%-2rem)] md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-xl md:p-6" role="dialog" aria-modal="true" aria-labelledby="login-title">
        <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-[#D0D5DD] md:hidden"></div>
        <div class="flex items-start justify-between gap-4">
            <div><h2 id="login-title" class="text-xl font-semibold tracking-tight text-[#172033] md:text-2xl">Sign in</h2><p class="mt-1 text-sm text-[#667085]">Continue without leaving this page.</p></div>
            <button type="button" @click="closeLogin()" class="touch-icon -mr-2 -mt-1" aria-label="Close sign in"><x-icon name="x" /></button>
        </div>

        <div class="mt-5 grid grid-cols-3 gap-2 rounded-lg bg-[#F2F4F7] p-1" role="tablist" aria-label="Choose account role">
            @foreach(['agent' => 'Agent', 'landlord' => 'Landlord', 'tenant' => 'Tenant'] as $value => $label)
                <button type="button" @click="loginRole = '{{ $value }}'; loginErrors = {}" :class="loginRole === '{{ $value }}' ? 'bg-white text-[#0B2A5B] shadow-sm' : 'text-[#667085]'" class="min-h-11 rounded-md px-2 text-sm font-semibold transition" role="tab" :aria-selected="loginRole === '{{ $value }}'">{{ $label }}</button>
            @endforeach
        </div>

        <button type="button" disabled class="mt-4 flex min-h-12 w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg border border-[#D0D5DD] bg-white px-4 text-sm font-medium text-[#667085]" title="Google sign-in is currently unavailable">
            <span class="text-base font-semibold text-[#0B2A5B]">G</span> Continue with Google <span class="rounded bg-[#F2F4F7] px-1.5 py-0.5 text-[10px] font-medium uppercase tracking-wide text-[#667085]">Unavailable</span>
        </button>

        <div class="my-4 flex items-center gap-3 text-xs font-medium text-[#98A2B3]"><span class="h-px flex-1 bg-[#E4E7EC]"></span>or<span class="h-px flex-1 bg-[#E4E7EC]"></span></div>

        <div class="mb-4 grid grid-cols-2 border-b border-[#E4E7EC]" role="tablist" aria-label="Sign-in method">
            <button type="button" @click="loginMode = 'password'; loginErrors = {}; loginMessage = ''" :class="loginMode === 'password' ? 'border-[#155EEF] text-[#155EEF]' : 'border-transparent text-[#667085]'" class="min-h-11 border-b-2 px-2 text-sm font-semibold">Email or phone</button>
            <button type="button" @click="loginMode = 'otp'; loginErrors = {}; loginMessage = ''" :class="loginMode === 'otp' ? 'border-[#155EEF] text-[#155EEF]' : 'border-transparent text-[#667085]'" class="min-h-11 border-b-2 px-2 text-sm font-semibold">Sign in with OTP</button>
        </div>

        <form x-show="loginMode === 'password'" action="{{ route('login.store') }}" method="POST" @submit.prevent="submitLogin($el)" novalidate>
            @csrf
            <input type="hidden" name="role" :value="loginRole">
            <input type="hidden" name="return_to" :value="window.location.pathname + window.location.search">
            <div>
                <label for="login-identifier" class="form-label">Email or phone</label>
                <input x-ref="loginIdentifier" id="login-identifier" name="identifier" autocomplete="username" inputmode="email" class="form-input" :class="loginErrors.identifier ? 'border-[#D92D20]' : ''" placeholder="you@example.com or 080…" required>
                <p x-show="loginErrors.identifier" x-text="loginErrors.identifier" class="form-error"></p>
            </div>
            <div class="mt-4">
                <label for="login-password" class="form-label">Password</label>
                <input id="login-password" name="password" type="password" autocomplete="current-password" class="form-input" :class="loginErrors.password ? 'border-[#D92D20]' : ''" placeholder="At least 8 characters" required>
                <p x-show="loginErrors.password" x-text="loginErrors.password" class="form-error"></p>
            </div>
            <div class="mt-4 flex items-center justify-between gap-3">
                <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm text-[#475467]"><input name="remember" value="1" type="checkbox" class="size-5 rounded border-[#98A2B3] text-[#155EEF] focus:ring-[#155EEF]">Remember me</label>
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-[#155EEF] hover:text-[#0E4CC9]">Forgot password?</a>
            </div>
            <p x-show="!online" class="mt-3 flex items-start gap-2 rounded-lg bg-[#FFF4E8] p-3 text-sm text-[#8A4B00]"><x-icon name="wifi-off" class="mt-0.5 size-4 shrink-0" />Reconnect to sign in.</p>
            <button type="submit" :disabled="loginLoading || !online" class="btn-primary mt-4 w-full disabled:cursor-not-allowed disabled:opacity-60"><span x-show="!loginLoading">Sign In</span><span x-show="loginLoading">Signing in…</span></button>
        </form>

        <form x-show="loginMode === 'otp'" action="{{ route('otp.request') }}" method="POST" novalidate>
            @csrf
            <input type="hidden" name="role" :value="loginRole">
            <label for="otp-identifier" class="form-label">Email or phone</label>
            <input id="otp-identifier" name="identifier" autocomplete="username" class="form-input" placeholder="Email address or phone number" disabled>
            <p class="mt-3 rounded-lg bg-[#EEF4FF] p-3 text-sm leading-5 text-[#0B2A5B]">OTP sign-in is currently unavailable. Please use your password instead.</p>
            <button type="button" disabled class="btn-primary mt-4 w-full cursor-not-allowed opacity-60">OTP unavailable</button>
        </form>

        <p class="mt-5 text-center text-sm text-[#667085]">New to Listora? <a href="{{ route('join') }}" class="font-semibold text-[#155EEF] hover:text-[#0E4CC9]">Create account</a></p>
    </section>
</div>
@endguest
