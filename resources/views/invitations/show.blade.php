<x-layouts.public>
    @section('title', 'Your Listora invitation')
    <section class="mx-auto max-w-[560px] px-4 py-8 sm:px-6 md:py-12"><div class="rounded-xl border border-[#D7E2F4] bg-white p-5 md:p-7">
        <span class="flex size-11 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="users" /></span>
        <h1 class="mt-4 text-[27px] font-semibold text-[#182230]">You’re invited to Listora</h1>
        <p class="mt-2 text-sm text-[#667085]">{{ $invitation->inviter?->name ?: 'A Listora user' }} invited you as a {{ str($invitation->intended_role)->headline() }}@if($invitation->organization), with {{ $invitation->organization->name }}@endif.</p>
        @if($invitation->status !== 'pending')
            <p class="mt-5 rounded-lg bg-[#F7F8FA] p-4 text-sm text-[#475467]">This invitation is {{ str($invitation->status)->replace('_',' ') }}.</p>
        @elseif(auth()->check())
            <form action="{{ route('invitations.accept',$token) }}" method="POST" class="mt-5">@csrf<button class="btn-primary w-full">Accept invitation</button></form>
        @elseif($existingUser)
            <div class="mt-5 rounded-lg bg-[#EAF2FF] p-4 text-sm text-[#0A2856]"><p class="font-medium">An account already exists with these details.</p><p class="mt-1">Sign in with the invited email or phone, then accept this invitation.</p></div>
            <button type="button" @click="openLogin()" class="btn-primary mt-4 w-full">Sign in</button><a href="{{ route('password.request') }}" class="btn-secondary mt-2 w-full">Reset password</a>
        @else
            <form action="{{ route('invitations.accept',$token) }}" method="POST" class="mt-5 grid gap-3">@csrf
                <div><label class="form-label">Full name</label><input name="name" value="{{ old('name',$invitation->name) }}" class="form-input" required></div>
                <div><label class="form-label">Phone</label><input name="phone" value="{{ old('phone',$invitation->phone) }}" inputmode="tel" class="form-input" required></div>
                <div><label class="form-label">Email</label><input name="email" value="{{ old('email',$invitation->email) }}" type="email" class="form-input" required></div>
                <div><label class="form-label">Create password</label><input name="password" type="password" class="form-input" minlength="8" required></div>
                <div><label class="form-label">Confirm password</label><input name="password_confirmation" type="password" class="form-input" minlength="8" required></div>
                <label class="flex items-start gap-3 text-sm text-[#475467]"><input name="terms" value="1" type="checkbox" class="mt-0.5 size-5" required><span>I accept the Terms and Privacy Policy.</span></label>
                @if($errors->any())<p class="form-error">{{ $errors->first() }}</p>@endif
                <button class="btn-primary w-full">Create account and continue</button>
            </form>
        @endif
    </div></section>
</x-layouts.public>
