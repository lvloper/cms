<div class="flex items-center py-2">
    <input id="checkbox-{{ $model }}" wire:model="{{ $model }}" type="checkbox"
        class="h-5 w-5 border-primary text-primary focus:ring-primary checked:bg-primary checked:border-primary">
    <label for="checkbox-{{ $model }}" class="ml-2 text-gray-700 text-sm ">
        {{ $slot }}
    </label>
    @error($model)
    <span class="error absolute top-full left-0 text-secondary text-xs">{{ $message}}</span>@enderror

</div>