<div class="col-span-{{ $colSpan ?? 12 }}">
    <button type="submit" class="w-full md:w-auto md:px-12 bg-primary text-white py-1 text-md"
        wire:loading.attr="disabled">{{ $slot }}</button>
</div>