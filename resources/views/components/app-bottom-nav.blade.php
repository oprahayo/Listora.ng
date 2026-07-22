@props(['items'])
<nav class="private-bottom-nav md:hidden" aria-label="Workspace navigation">
    @foreach($items as $item)
        @php($active = request()->routeIs($item['active']))
        <a href="{{ route($item['route']) }}" class="private-bottom-nav-item {{ $active ? 'is-active' : '' }}" @if($active) aria-current="page" @endif>
            <x-icon :name="$item['icon']" class="size-[22px]" />
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</nav>
