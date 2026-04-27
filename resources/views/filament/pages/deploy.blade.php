<x-filament::page>
    <x-filament::card>
        <div class="flex flex-col gap-4">
            <h2 class="text-lg font-medium">Despliegue del Sistema</h2>
            <p class="text-gray-500">
                Esto ejecutará el proceso de despliegue. Por favor, asegúrese de que desea proceder antes de hacer clic
                en el botón.
            </p>
            <div class="grid grid-cols-2 gap-4">
                <x-filament::button wire:click="deploy" wire:loading.attr="disabled" class="w-full">
                    <span wire:loading.remove>Iniciar Despliegue</span>
                    <span wire:loading>Desplegando...</span>
                </x-filament::button>

            </div>

            @if($output)
            <div class="mt-4">
                <h3 class="text-md font-medium mb-2">Salida del Despliegue:</h3>
                <pre class="p-4 rounded-lg overflow-x-auto whitespace-pre-wrap">{{ $output }}</pre>
            </div>
            @endif
        </div>
    </x-filament::card>
</x-filament::page>