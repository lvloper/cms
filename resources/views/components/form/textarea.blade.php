@props(['placeholder' => '', 'value' => '', 'name' => '', 'id' => null, 'model' => ''])

@php
$inputClasses = 'border-[3px] border-primary py-2 px-1 md:px-3 focus:outline-none focus:ring-2 text-base focus:ring-blue-500 placeholder-primary w-full min-h-[120px]';
@endphp

<div class="grid gap-4">
    <textarea id="{{ $id ?? Str::random(8) }}" name="{{ $name }}" placeholder="{{ $placeholder }}"
        wire:model="{{ $model }}" class="{{ $inputClasses }}">{{ old($name, $value) }}</textarea>
</div>
@error($model)
<span class="error absolute top-full left-0 text-secondary text-xs">{{ $message}}</span>@enderror
