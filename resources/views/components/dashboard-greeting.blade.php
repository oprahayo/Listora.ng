@props(['name' => auth()->user()->name])
<div><p class="text-sm text-[#667085]">Welcome back</p><h1 {{ $attributes->merge(['class' => 'mt-0.5 text-[23px] font-semibold tracking-tight text-[#182230] md:text-[28px]']) }}>{{ Str::before($name, ' ') ?: $name }}</h1></div>
