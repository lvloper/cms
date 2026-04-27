@props(['type' => 'text', 'placeholder' => '', 'value' => '', 'name' => '', 'id' => null, 'model' => ''])

@php
$inputClasses = 'border-[3px] border-primary py-2 px-1 md:px-3 focus:outline-none focus:ring-2 text-base
focus:ring-blue-500 placeholder-primary';
@endphp

@if ($type === 'phone')
<div class="flex gap-4">
    <div class="w-[160px]">
        <input type="text" id="{{ $id ? $id . '_area_code' : Str::random(8) . '_area_code' }}"
            name="{{ $name ? $name . '_area_code' : '' }}" placeholder="C de area *"
            value="{{ old($name ? $name . '_area_code' : '', '') }}" wire:model="{{ $model }}_area_code"
            x-mask="+(99) 999" inputmode="numeric" class="w-full {{ $inputClasses }}">
        @error($model.'_area_code')
        <span class="error absolute top-full left-0 text-red-500 text-sm">{{ $message }}</span>@enderror

    </div>
    <div class="w-full">
        <input type="text" id="{{ $id ? $id . '_number' : Str::random(8) . '_number' }}"
            name="{{ $name ? $name . '_number' : '' }}" wire:model="{{ $model }}_number" placeholder="Teléfono *"
            value="{{ old($name ? $name . '_number' : '', '') }}" x-mask="9999-9999" inputmode="numeric"
            class="w-full {{ $inputClasses }}">
        @error($model.'_number')
        <span class="error absolute top-full left-0 text-red-500 text-sm">{{ $message}}</span>@enderror
    </div>

</div>
@else
<div class="grid gap-4">
    <input type="{{ $type }}" id="{{ $id ?? Str::random(8) }}" name="{{ $name }}" placeholder="{{ $placeholder }}"
        value="{{ old($name, $value) }}" wire:model="{{ $model }}" class="{{ $inputClasses }}">
</div>
@error($model)
<span class="error absolute top-full left-0 text-secondary text-xs z-50">{{ $message}}</span>@enderror
@endif