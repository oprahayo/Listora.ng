<x-layouts.public>
    @section('title', 'Verify your phone | Listora.ng')
    <section class="mx-auto max-w-[520px] px-4 py-8 sm:px-6 md:py-12">
        <div class="rounded-xl border border-[#D7E2F4] bg-white p-5 shadow-[0_3px_14px_rgba(10,40,86,.06)] md:p-7">
            <span class="flex size-11 items-center justify-center rounded-lg bg-[#EAF2FF] text-[#145FCC]"><x-icon name="lock" /></span>
            <p class="mt-4 text-xs font-medium text-[#145FCC]">Phone verification</p>
            <h1 class="mt-1 text-[26px] font-semibold tracking-tight text-[#182230]">Enter your six-digit code</h1>
            <p class="mt-2 text-sm text-[#667085]">Sent to +{{ substr(auth()->user()->phone, 0, 3) }} {{ substr(auth()->user()->phone, 3, 3) }} ••• •{{ substr(auth()->user()->phone, -3) }}</p>
            @if(session('status'))<p class="mt-4 rounded-lg bg-[#EAF2FF] p-3 text-sm text-[#0A2856]">{{ session('status') }}</p>@endif
            <form action="{{ route('phone.confirm') }}" method="POST" class="mt-5">
                @csrf
                <label for="phone-code" class="form-label">Verification code</label>
                <input id="phone-code" name="code" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" class="form-input h-14 text-center text-2xl tracking-[.55em]" autofocus required>
                @error('code')<p class="form-error">{{ $message }}</p>@enderror
                <button class="btn-primary mt-4 w-full">Verify phone</button>
            </form>
            <div class="mt-4 flex items-center justify-between gap-3 text-sm">
                <form action="{{ route('phone.request') }}" method="POST">@csrf<button class="min-h-11 font-medium text-[#145FCC]">Send another code</button></form>
                <a href="{{ route('join') }}" class="min-h-11 py-3 font-medium text-[#667085]">Change number</a>
            </div>
            <p class="mt-2 text-center text-xs text-[#667085]">A new code can be requested after one minute.</p>
        </div>
    </section>
</x-layouts.public>
