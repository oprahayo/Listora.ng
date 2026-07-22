<x-layouts.public>
    @section('title', 'Agent setup | Listora.ng')
    @php($draft = $verification->draft_data ?: [])
    <section class="mx-auto max-w-[640px] px-4 py-6 sm:px-6 md:py-10">
        @if(session('status'))<p class="mb-4 rounded-lg bg-[#EAF2FF] p-3 text-sm text-[#0A2856]">{{ session('status') }}</p>@endif
        <div class="rounded-xl border border-[#D7E2F4] bg-white p-5 shadow-[0_3px_14px_rgba(10,40,86,.06)] md:p-7">
            <div class="flex items-start justify-between gap-4">
                <div><p class="text-xs font-medium text-[#145FCC]">{{ $step }} of 4</p><p class="mt-1 tracking-[.35em] text-[#145FCC]" aria-label="Step {{ $step }} of 4">@for($i=1;$i<=4;$i++){{ $i <= $step ? '●' : '○' }}@endfor</p></div>
                <a href="{{ route('agent.dashboard') }}" class="min-h-11 px-2 py-3 text-sm font-medium text-[#667085]">Save and exit</a>
            </div>

            <form action="{{ route('onboarding.agent.store') }}" method="POST" enctype="multipart/form-data" class="mt-5" data-offline-draft="agent-onboarding-{{ $step }}" data-safe-fields="operation_type,display_name,public_slug,operating_state,operating_city,short_bio,cac_registration_type,business_name,business_address,government_id_type" data-autosave-url="{{ route('onboarding.agent.autosave') }}">
                @csrf<input type="hidden" name="step" value="{{ $step }}">

                @if($step === 1)
                    <h1 class="text-[26px] font-semibold tracking-tight text-[#182230]">How do you manage properties?</h1>
                    <p class="mt-2 text-sm text-[#667085]">Choose the answer that feels closest. You can update it later.</p>
                    <div class="mt-5 grid gap-3 sm:grid-cols-2">
                        @foreach([
                            ['individual_agent','user','Individual Agent'], ['registered_business','building','Registered Business'],
                            ['property_company','office','Estate or Property Management Company'], ['caretaker','key','Caretaker Managing Properties']
                        ] as [$value,$icon,$label])
                            <label class="choice-card"><input type="radio" name="operation_type" value="{{ $value }}" class="peer sr-only" @checked(old('operation_type', data_get($draft, 'operation_type', $profile->operation_type)) === $value) required><span class="choice-card-content"><span class="choice-card-icon"><x-icon :name="$icon" /></span><span class="font-medium text-[#182230]">{{ $label }}</span><span class="ml-auto hidden text-[#145FCC] peer-checked:block"><x-icon name="check" /></span></span></label>
                        @endforeach
                    </div>
                    @error('operation_type')<p class="form-error">{{ $message }}</p>@enderror
                @elseif($step === 2)
                    <h1 class="text-[26px] font-semibold tracking-tight text-[#182230]">Your public profile</h1>
                    <p class="mt-2 text-sm text-[#667085]">We’ve filled in what we already know.</p>
                    <div class="mt-5 grid gap-3" x-data="{ displayName: @js(old('display_name', data_get($draft, 'display_name', $profile->display_name))), publicSlug: @js(old('public_slug', data_get($draft, 'public_slug', $profile->public_slug))), slugEdited: false, slugify(value) { return value.toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') } }">
                        <div class="grid gap-3 rounded-lg bg-[#F7F8FA] p-3 text-sm sm:grid-cols-3"><span><small class="block text-[#667085]">Name</small>{{ auth()->user()->name }}</span><span><small class="block text-[#667085]">Email</small>{{ auth()->user()->email }}</span><span><small class="block text-[#667085]">Phone</small>+{{ auth()->user()->phone }}</span></div>
                        <div><label class="form-label" for="display-name">Public display name <span class="rounded bg-[#EAF2FF] px-1.5 py-0.5 text-[11px] text-[#145FCC]">Suggested</span></label><input id="display-name" name="display_name" x-model="displayName" @input="if (!slugEdited) publicSlug = slugify(displayName)" class="form-input" required>@error('display_name')<p class="form-error">{{ $message }}</p>@enderror</div>
                        <div><label class="form-label" for="public-slug">Public page name <span class="rounded bg-[#EAF2FF] px-1.5 py-0.5 text-[11px] text-[#145FCC]">Suggested</span></label><div class="flex items-center rounded-lg border border-[#D0D5DD] bg-white focus-within:border-[#145FCC]"><span class="pl-3 text-xs text-[#667085]">listora.ng/agent/</span><input id="public-slug" name="public_slug" x-model="publicSlug" @input="slugEdited = true" class="min-w-0 flex-1 border-0 bg-transparent px-1 py-3 text-sm outline-none" required></div>@error('public_slug')<p class="form-error">{{ $message }}</p>@enderror</div>
                        <div class="grid gap-3 sm:grid-cols-2"><div><label class="form-label" for="operating-state">State</label><input id="operating-state" name="operating_state" value="{{ old('operating_state', data_get($draft, 'operating_state', $profile->operating_state)) }}" list="nigerian-states" class="form-input" required></div><div><label class="form-label" for="operating-city">City or area</label><input id="operating-city" name="operating_city" value="{{ old('operating_city', data_get($draft, 'operating_city', $profile->operating_city)) }}" class="form-input" required></div></div>
                        <datalist id="nigerian-states">@foreach(['Lagos','FCT','Rivers','Oyo','Ogun','Enugu','Anambra','Kano','Kaduna','Edo','Delta','Akwa Ibom'] as $state)<option value="{{ $state }}">@endforeach</datalist>
                        <div><label class="form-label" for="short-bio">Short description <span class="font-normal text-[#667085]">Optional</span></label><textarea id="short-bio" name="short_bio" rows="3" maxlength="300" class="form-input">{{ old('short_bio', data_get($draft, 'short_bio', $profile->short_bio)) }}</textarea></div>
                    </div>
                @elseif($step === 3)
                    <h1 class="text-[26px] font-semibold tracking-tight text-[#182230]">Verification</h1>
                    <p class="mt-2 text-sm text-[#667085]">Your documents are private and only authorised reviewers can open them.</p>
                    @if($profile->account_type === 'business')
                        <div class="mt-5 grid gap-3" x-data="{ cacType: @js(old('cac_registration_type', data_get($draft, 'cac_registration_type', $profile->organization?->cac_registration_type))), cacNumber: @js(old('cac_registration_number', $profile->organization?->cac_registration_number)), suggested: false, suggest() { if (this.cacType) return; const value = this.cacNumber.trim().toUpperCase(); if (value.startsWith('RC')) { this.cacType = 'limited_company'; this.suggested = true } else if (value.startsWith('BN')) { this.cacType = 'business_name'; this.suggested = true } } }">
                            <div><label class="form-label" for="cac-type">What type of CAC registration do you have?</label><select id="cac-type" name="cac_registration_type" x-model="cacType" class="form-input" required><option value="">Choose one</option>@foreach(['business_name'=>'Business Name','limited_company'=>'Limited Company','incorporated_trustee'=>'Incorporated Trustee','other'=>'Other'] as $value=>$label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select><p x-cloak x-show="suggested" class="mt-1 text-xs text-[#145FCC]">Suggested from the number prefix. Please confirm.</p></div>
                            <div><label class="form-label" for="business-name">Registered business name</label><input id="business-name" name="business_name" value="{{ old('business_name', data_get($draft, 'business_name', $profile->organization?->name)) }}" class="form-input" required></div>
                            <div><label class="form-label" for="cac-number">CAC registration number</label><input id="cac-number" name="cac_registration_number" x-model="cacNumber" @input="suggest()" class="form-input uppercase" placeholder="BN… or RC…" required><p class="mt-1 text-xs text-[#667085]">A BN or RC prefix can help suggest a registration type. Please confirm it yourself.</p></div>
                            <div><label class="form-label" for="business-address">Business address</label><textarea id="business-address" name="business_address" rows="2" class="form-input" required>{{ old('business_address', data_get($draft, 'business_address', $profile->organization?->address)) }}</textarea></div>
                            <x-onboarding.file-field name="cac_certificate" label="CAC certificate" :required="!$verification->documents->where('document_type','cac_certificate')->where('status','uploaded')->count()" />
                            <x-onboarding.file-field name="cac_status_report" label="CAC status report (optional)" />
                        </div>
                    @else
                        <div class="mt-5 grid gap-3">
                            <div><label class="form-label" for="government-id-type">Government ID type</label><select id="government-id-type" name="government_id_type" class="form-input" required><option value="">Choose one</option>@foreach(['nin'=>'National Identification Number','drivers_licence'=>'Driver’s Licence','international_passport'=>'International Passport','voters_card'=>'Voter’s Card','other'=>'Other accepted ID'] as $value=>$label)<option value="{{ $value }}" @selected(old('government_id_type', data_get($draft, 'government_id_type', data_get($verification->identity_data,'id_type'))) === $value)>{{ $label }}</option>@endforeach</select></div>
                            <div><label class="form-label" for="id-number">ID number</label><input id="id-number" name="id_number" value="{{ old('id_number', data_get($verification->identity_data,'id_number')) }}" class="form-input" autocomplete="off" required><p class="mt-1 text-xs text-[#667085]">This is encrypted and never saved on this device.</p></div>
                            <x-onboarding.file-field name="government_id" label="Government ID" :required="!$verification->documents->where('document_type','government_id')->where('status','uploaded')->count()" />
                            <x-onboarding.file-field name="proof_of_address" label="Proof of address (optional)" />
                            <x-onboarding.file-field name="passport_photo" label="Passport photo (optional)" accept=".jpg,.jpeg,.png" />
                        </div>
                    @endif
                    @error('documents')<p class="form-error">{{ $message }}</p>@enderror
                @else
                    <h1 class="text-[26px] font-semibold tracking-tight text-[#182230]">Review and submit</h1>
                    <p class="mt-2 text-sm text-[#667085]">Check the essentials before sending your verification.</p>
                    <dl class="mt-5 divide-y divide-[#E4E7EC] rounded-lg border border-[#E4E7EC] text-sm">
                        @foreach([
                            'Account type' => str($profile->account_type)->headline(), 'Display name' => $profile->display_name,
                            'Location' => collect([$profile->operating_city,$profile->operating_state])->filter()->join(', '),
                            'Registration number' => $profile->organization?->cac_registration_number,
                            'Uploaded documents' => $verification->documents->where('status','uploaded')->map(fn($doc)=>str($doc->document_type)->headline())->join(', '),
                        ] as $label=>$value) @if($value)<div class="grid grid-cols-[140px_1fr] gap-3 p-3"><dt class="text-[#667085]">{{ $label }}</dt><dd class="font-medium text-[#182230]">{{ $value }}</dd></div>@endif @endforeach
                    </dl>
                    @error('documents')<p class="form-error">{{ $message }}</p>@enderror
                @endif

                <div class="sticky bottom-[4.4rem] -mx-5 mt-6 flex gap-2 border-t border-[#E4E7EC] bg-white px-5 pb-1 pt-4 md:static md:mx-0 md:px-0 md:pb-0">
                    @if($step > 1)<button name="direction" value="back" class="btn-secondary">Back</button>@endif
                    <button name="direction" value="{{ $step === 4 ? 'submit' : 'next' }}" class="btn-primary flex-1">{{ $step === 4 ? 'Submit for verification' : 'Save and continue' }}</button>
                </div>
            </form>
        </div>
    </section>
</x-layouts.public>
