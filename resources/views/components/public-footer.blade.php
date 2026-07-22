<footer class="border-t border-[#E4E7EC] bg-white pb-20 pt-6 md:pb-8 md:pt-8">
    <div class="mx-auto flex max-w-[1180px] flex-col gap-4 px-4 sm:px-6 md:flex-row md:items-center md:justify-between lg:px-0">
        <a href="{{ route('home') }}" class="text-lg font-semibold tracking-tight text-[#0A2856]">Listora.ng</a>
        <nav class="flex flex-wrap gap-x-4 gap-y-2 text-[13px] font-medium text-[#475467] md:text-sm" aria-label="Footer navigation">
            <a href="{{ route('properties.index') }}" class="hover:text-[#145FCC]">Browse</a>
            <a href="{{ route('home') }}#how-it-works" class="hover:text-[#145FCC]">How It Works</a>
            <a href="{{ route('join') }}#agents" class="hover:text-[#145FCC]">For Agents</a>
            <span>Privacy</span>
            <span>Terms</span>
        </nav>
        <p class="text-[13px] text-[#667085]">© {{ date('Y') }} Listora.ng</p>
    </div>
</footer>
