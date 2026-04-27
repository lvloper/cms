<x-block>
    @isset($preview)
        <div class="p-20 text-center bg-gray-100 rounded-md">
            <h1>Este bloque no se puede visualizar en la vista de preview</h1>
        </div>
    @else
        {!! $code !!}
    @endisset
</x-block>
