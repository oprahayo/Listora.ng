@props(['title', 'subtitle' => null])
<div {{ $attributes->merge(['class' => 'mb-5']) }}><h1 class="text-[23px] font-semibold tracking-tight text-[#182230] md:text-[28px]">{{ $title }}</h1>@if($subtitle)<p class="mt-1 text-sm text-[#667085]">{{ $subtitle }}</p>@endif</div>
