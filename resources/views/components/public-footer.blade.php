<footer class="border-t border-[#E4E7EC] bg-white pb-24 pt-10 md:pb-10">
    <div class="mx-auto flex max-w-[1180px] flex-col gap-7 px-4 sm:px-6 md:flex-row md:items-end md:justify-between lg:px-0">
        <div>
            <a href="{{ route('home') }}" class="text-xl font-semibold tracking-tight text-[#0B2A5B]">Listora.ng</a>
            <p class="mt-2 max-w-sm text-sm leading-6 text-[#667085]">A simpler way to discover rental and commercial properties across Nigeria.</p>
        </div>
        <nav class="flex flex-wrap gap-x-5 gap-y-3 text-sm font-medium text-[#475467]" aria-label="Footer navigation">
            <a href="{{ route('properties.index') }}" class="hover:text-[#155EEF]">Browse</a>
            <a href="{{ route('saved') }}" class="hover:text-[#155EEF]">Saved</a>
            <a href="{{ route('home') }}#how-it-works" class="hover:text-[#155EEF]">How it works</a>
            <a href="{{ route('join') }}" class="hover:text-[#155EEF]">Join Listora</a>
        </nav>
        <p class="text-sm text-[#667085]">© {{ date('Y') }} Listora.ng</p>
    </div>
</footer>
