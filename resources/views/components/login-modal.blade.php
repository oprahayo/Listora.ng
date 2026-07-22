@guest
<div x-cloak x-show="loginOpen" @keydown.escape.window="closeLogin()" class="fixed inset-0 z-[70]" aria-live="polite">
    <div x-show="loginOpen" x-transition.opacity.duration.150ms class="absolute inset-0 bg-[#06152D]/75" @click="if (!loginLoading) closeLogin()"></div>
    <section x-show="loginOpen" x-transition:enter="transition duration-150" x-transition:enter-start="translate-y-full opacity-0 md:translate-y-3" x-transition:enter-end="translate-y-0 opacity-100" x-transition:leave="transition duration-150" x-transition:leave-start="translate-y-0 opacity-100" x-transition:leave-end="translate-y-full opacity-0 md:translate-y-3" @keydown.tab="trapFocus($event, $el)" class="absolute inset-x-0 bottom-0 max-h-[90dvh] overflow-y-auto rounded-t-2xl bg-white px-4 pb-[max(1.5rem,env(safe-area-inset-bottom))] pt-3 shadow-2xl md:inset-auto md:left-1/2 md:top-1/2 md:w-[410px] md:max-w-[calc(100%-2rem)] md:max-h-[85vh] md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-xl md:p-6" role="dialog" aria-modal="true" aria-labelledby="login-title">
        <div class="mx-auto mb-3 h-1 w-10 rounded-full bg-[#D0D5DD] md:hidden"></div>
        <button type="button" @click="closeLogin()" class="touch-icon absolute right-3 top-3" aria-label="Close sign in"><x-icon name="x" /></button>

        <span class="flex size-10 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="lock" class="size-5" /></span>
        <h2 id="login-title" class="mt-3 text-xl font-semibold tracking-tight text-[#182230]">Sign in</h2>

        <div class="mt-4 grid grid-cols-3 gap-1 rounded-lg bg-[#F2F4F7] p-1" role="tablist" aria-label="Choose account role">
            @foreach(['agent' => 'Agent', 'landlord' => 'Landlord', 'tenant' => 'Tenant'] as $value => $label)
                <button type="button" @click="loginRole = '{{ $value }}'; loginErrors = {}" :class="loginRole === '{{ $value }}' ? 'bg-white text-[#0A2856] shadow-sm' : 'text-[#667085]'" class="min-h-10 rounded-md px-2 text-sm font-medium transition" role="tab" :aria-selected="loginRole === '{{ $value }}'">{{ $label }}</button>
            @endforeach
        </div>

        <form action="{{ route('login.store') }}" method="POST" @submit.prevent="submitLogin($el)" novalidate class="mt-4">
            @csrf
            <input type="hidden" name="role" :value="loginRole">
            <input type="hidden" name="return_to" :value="window.location.pathname + window.location.search">
            <div>
                <label for="login-identifier" class="form-label">Email or phone</label>
                <input x-ref="loginIdentifier" id="login-identifier" name="identifier" autocomplete="username" inputmode="email" class="form-input" :class="loginErrors.identifier ? 'border-[#C92A2A]' : ''" placeholder="you@example.com or 080…" required>
                <p x-show="loginErrors.identifier" x-text="loginErrors.identifier" class="form-error"></p>
            </div>
            <div class="mt-3">
                <label for="login-password" class="form-label">Password</label>
                <input id="login-password" name="password" type="password" autocomplete="current-password" class="form-input" :class="loginErrors.password ? 'border-[#C92A2A]' : ''" placeholder="Enter your password" required>
                <p x-show="loginErrors.password" x-text="loginErrors.password" class="form-error"></p>
            </div>
            <div class="mt-3 flex items-center justify-between gap-3">
                <label class="flex min-h-11 cursor-pointer items-center gap-2 text-sm text-[#475467]"><input name="remember" value="1" type="checkbox" class="size-5 rounded border-[#98A2B3] text-[#145FCC] focus:ring-[#145FCC]">Remember me</label>
                <a href="{{ route('password.request') }}" class="text-sm font-medium text-[#145FCC] hover:text-[#0E4DA9]">Forgot password?</a>
            </div>
            <p x-show="!online" class="mt-3 flex items-start gap-2 rounded-lg bg-[#FFF4E8] p-3 text-sm text-[#B75D00]"><x-icon name="wifi-off" class="mt-0.5 size-4 shrink-0" />Reconnect to sign in.</p>
            <button type="submit" :disabled="loginLoading || !online" class="btn-primary mt-3 w-full disabled:cursor-not-allowed disabled:opacity-60"><span x-show="!loginLoading">Sign In</span><span x-show="loginLoading">Signing in…</span></button>
        </form>

        <button type="button" disabled class="mt-3 flex min-h-11 w-full cursor-not-allowed items-center justify-center gap-2 rounded-lg border border-[#D0D5DD] bg-white px-4 text-sm font-medium text-[#667085]" title="OTP sign-in is currently unavailable">Sign in with OTP <span class="sr-only">— currently unavailable</span></button>
        <p class="mt-4 text-center text-sm text-[#667085]">New to Listora? <a href="{{ route('join') }}" class="font-medium text-[#145FCC] hover:text-[#0E4DA9]">Create account</a></p>
    </section>
</div>
@endguest
