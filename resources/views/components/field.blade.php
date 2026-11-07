@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'hint' => null,
    'required' => false,
    'inputmode' => null,
    'placeholder' => null,
    'autocomplete' => 'off',
])

@php
    $hasError = $errors->has($name);
    $describedBy = trim(($hint ? $name.'-hint ' : '').($hasError ? $name.'-error' : ''));
@endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-stone-700">
        {{ $label }}
        @unless ($required)
            <span class="font-normal text-stone-400">(opcional)</span>
        @endunless
    </label>

    <input
        id="{{ $name }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($required) required @endif
        @if ($inputmode) inputmode="{{ $inputmode }}" @endif
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        autocomplete="{{ $autocomplete }}"
        @if ($hasError) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        class="mt-1.5 w-full rounded-lg border bg-white px-3.5 py-2.5 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200 {{ $hasError ? 'border-red-400' : 'border-stone-200' }}"
    />

    @if ($hint)
        <p id="{{ $name }}-hint" class="mt-1.5 text-xs text-stone-500">{{ $hint }}</p>
    @endif

    @error($name)
        <p id="{{ $name }}-error" class="mt-1.5 text-xs font-medium text-red-700">{{ $message }}</p>
    @enderror
</div>
