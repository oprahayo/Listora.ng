@props(['name', 'label', 'required' => false, 'accept' => '.pdf,.jpg,.jpeg,.png'])
<div class="rounded-lg border border-dashed border-[#B8C8E1] bg-[#F8FAFF] p-3">
    <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    <input id="{{ $name }}" name="{{ $name }}" type="file" accept="{{ $accept }}" class="block w-full text-sm text-[#475467] file:mr-3 file:rounded-md file:border-0 file:bg-[#EAF2FF] file:px-3 file:py-2 file:text-sm file:font-medium file:text-[#145FCC]" @required($required)>
    <p class="mt-2 text-xs text-[#667085]">PDF up to 10MB. JPG or PNG up to 5MB.</p>
    @error($name)<p class="form-error">{{ $message }}</p>@enderror
</div>
