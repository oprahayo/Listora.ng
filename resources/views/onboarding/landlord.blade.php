<x-layouts.app>
    @section('title', 'Landlord setup | Listora.ng')
    @section('app_title', 'Profile setup')
    @section('app_back', route('landlord.dashboard'))
    <section class="onboarding-shell"><div class="onboarding-panel">
        <p class="text-xs font-medium text-[#145FCC]">{{ $step }} of 2</p><p class="mt-1 tracking-[.35em] text-[#145FCC]">{{ $step === 1 ? '● ○' : '● ●' }}</p>
        <h1 class="mt-4 text-[26px] font-semibold text-[#182230]">{{ $step === 1 ? 'Confirm your details' : 'How should we contact you?' }}</h1>
        <form action="{{ route('onboarding.landlord.store') }}" method="POST" class="mt-5 grid gap-3" data-offline-draft="landlord-onboarding-{{ $step }}" data-safe-fields="name,email,phone,preferred_contact_method,invited">@csrf<input type="hidden" name="step" value="{{ $step }}">
            @if($step === 1)
                <div><label class="form-label">Name</label><input name="name" value="{{ old('name', auth()->user()->name) }}" class="form-input" required></div>
                <div><label class="form-label">Phone</label><input name="phone" value="{{ old('phone', auth()->user()->phone) }}" class="form-input" required></div>
                <div><label class="form-label">Email</label><input name="email" value="{{ old('email', auth()->user()->email) }}" type="email" class="form-input" required></div>
            @else
                <div class="grid grid-cols-2 gap-2">@foreach(['app'=>'In-app','whatsapp'=>'WhatsApp','sms'=>'SMS','email'=>'Email'] as $value=>$label)<label class="choice-card"><input type="radio" name="preferred_contact_method" value="{{ $value }}" class="peer sr-only" @checked(old('preferred_contact_method',$profile->preferred_contact_method)===$value) required><span class="choice-card-content justify-center">{{ $label }}</span></label>@endforeach</div>
                <fieldset><legend class="form-label mt-2">Were you invited by an agent?</legend><div class="grid gap-2"><label class="choice-card"><input type="radio" name="invited" value="1" class="peer sr-only" @checked((bool)$invitation) required><span class="choice-card-content">Yes, I have an invitation @if($invitation)<small class="ml-auto text-[#145FCC]">Detected</small>@endif</span></label><label class="choice-card"><input type="radio" name="invited" value="0" class="peer sr-only" @checked(!$invitation)><span class="choice-card-content">No, I’m joining independently</span></label></div></fieldset>
                @unless($invitation)<p class="rounded-lg bg-[#F7F8FA] p-3 text-sm text-[#667085]">You can connect your property to an agent later.</p>@endunless
            @endif
            <div class="sticky bottom-[4.4rem] mt-3 flex gap-2 border-t border-[#E4E7EC] bg-white pt-4 md:static">@if($step>1)<button name="direction" value="back" class="btn-secondary">Back</button>@endif<button class="btn-primary flex-1">{{ $step===1?'Continue':'Finish setup' }}</button></div>
        </form>
    </div></section>
</x-layouts.app>
