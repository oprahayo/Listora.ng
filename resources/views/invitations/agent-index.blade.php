<x-layouts.public>
    @section('title', 'Invitations | Listora.ng')
    <section class="dashboard-shell">
        @if(session('status'))<p class="mb-4 rounded-lg bg-[#EAF2FF] p-3 text-sm text-[#0A2856]">{{ session('status') }}</p>@endif
        <div class="flex items-start justify-between gap-4"><div><p class="text-sm text-[#667085]">Agent workspace</p><h1 class="text-[27px] font-semibold text-[#182230]">Invite a person</h1><p class="mt-1 text-sm text-[#667085]">Connect a landlord or tenant to their Listora account.</p></div><a href="{{ route('agent.dashboard') }}" class="btn-secondary">Back</a></div>
        <div class="mt-5 grid gap-5 lg:grid-cols-[360px_1fr]">
            <form action="{{ route('agent.invitations.store') }}" method="POST" class="rounded-xl border border-[#D7E2F4] bg-white p-4">@csrf
                <fieldset><legend class="form-label">Invite</legend><div class="grid grid-cols-2 gap-2">@foreach(['landlord'=>'Landlord','tenant'=>'Tenant'] as $value=>$label)<label class="choice-card"><input class="peer sr-only" type="radio" name="intended_role" value="{{ $value }}" @checked(old('intended_role','landlord')===$value)><span class="choice-card-content justify-center">{{ $label }}</span></label>@endforeach</div></fieldset>
                <div class="mt-3"><label class="form-label">Name <span class="font-normal text-[#667085]">Optional</span></label><input name="name" value="{{ old('name') }}" class="form-input"></div>
                <div class="mt-3"><label class="form-label">Phone</label><input name="phone" value="{{ old('phone') }}" inputmode="tel" class="form-input" placeholder="0803 123 4567"></div>
                <p class="my-2 text-center text-xs text-[#667085]">or</p>
                <div><label class="form-label">Email</label><input name="email" value="{{ old('email') }}" type="email" class="form-input"></div>
                @error('identifier')<p class="form-error">{{ $message }}</p>@enderror
                <button class="btn-primary mt-4 w-full">Send invitation</button>
            </form>
            <div><h2 class="text-xl font-semibold text-[#182230]">Recent invitations</h2><div class="mt-3 grid gap-3">
                @forelse($invitations as $invitation)<article class="rounded-xl border border-[#E4E7EC] bg-white p-4"><div class="flex items-start justify-between gap-3"><div><h3 class="font-semibold text-[#182230]">{{ $invitation->name ?: $invitation->email ?: '+'.$invitation->phone }}</h3><p class="mt-1 text-sm text-[#667085]">{{ str($invitation->intended_role)->headline() }} · expires {{ $invitation->expires_at->format('j M Y') }}</p></div><x-status-badge :type="$invitation->status==='accepted'?'verified':'neutral'">{{ str($invitation->status)->headline() }}</x-status-badge></div>@if($invitation->status==='pending')<div class="mt-3 flex gap-3 border-t border-[#E4E7EC] pt-3"><form action="{{ route('agent.invitations.resend',$invitation) }}" method="POST">@csrf<button class="min-h-11 text-sm font-medium text-[#145FCC]">Resend</button></form><form action="{{ route('agent.invitations.destroy',$invitation) }}" method="POST">@csrf @method('DELETE')<button class="min-h-11 text-sm font-medium text-[#C92A2A]">Cancel</button></form></div>@endif</article>
                @empty<x-empty-state icon="users" title="No invitations yet" message="Send a simple invitation to connect someone." />@endforelse
            </div>{{ $invitations->links() }}</div>
        </div>
    </section>
</x-layouts.public>
