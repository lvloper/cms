    <div class="container mx-auto px-4 py-8">
        <div class="max-w-5xl mx-auto">

            <!-- Header with Instructions -->
            <div class="test-instructions">
                <h1 class="text-3xl font-bold mb-3">Test Rich Editor - Limpieza de Pegado</h1>
                <p class="text-lg mb-4">
                    Esta página te permite probar la limpieza automática de contenido pegado desde Google Docs, Word, etc.
                </p>
                <div class="bg-white/10 rounded p-4">
                    <h3 class="font-semibold mb-2">Lo que DEBE suceder al pegar:</h3>
                    <ul class="list-disc list-inside space-y-1 text-sm">
                        <li>Se mantienen las <strong>negritas</strong> y <em>cursivas</em></li>
                        <li>Se eliminan los atributos de estilo (font-family, color, margin, etc.)</li>
                        <li>Se eliminan los párrafos vacíos (incluso los que tienen &nbsp; o <br>)</li>
                        <li>Se consolida el espacio en blanco excesivo</li>
                    </ul>
                </div>
            </div>

            <!-- Test Example -->
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-2">Ejemplo para Probar:</h3>
                <p class="text-sm text-yellow-700 mb-2">Copia y pega este contenido desde Google Docs en el editor:</p>
                <div class="bg-white p-3 rounded text-sm">
                    <p><strong>Título en Negrita</strong></p>
                    <p><br></p>
                    <p>Texto normal con un párrafo vacío arriba.</p>
                    <p><br></p>
                    <p><br></p>
                    <p><em>Cursiva</em> con múltiples párrafos vacíos arriba.</p>
                </div>
            </div>

            <!-- Editor Form -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Editor</h2>
                <form wire:submit.prevent="">
                    {{ $this->form }}
                </form>
            </div>

            <!-- Output Display -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    Contenido Capturado
                </h2>
                <div class="test-result bg-gray-900 text-green-400 p-4 rounded overflow-x-auto max-h-96 overflow-y-auto">
                    <pre>{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>

                @if(!empty($data['content']))
                <div class="mt-4">
                    <h3 class="font-semibold mb-2 text-gray-700">Vista Previa del Contenido:</h3>
                    <div class="border rounded p-4 bg-gray-50 prose max-w-none">
                        {!! $data['content'] ?? '' !!}
                    </div>
                </div>
                @endif
            </div>

            <!-- Debugging Tips -->
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
                <h3 class="font-semibold text-blue-800 mb-2">Tips de Debugging:</h3>
                <ul class="text-sm text-blue-700 space-y-1">
                    <li>Abre la consola del navegador (F12) para ver mensajes de debug</li>
                    <li>• Revisa el JSON para verificar que no hay párrafos vacíos (&lt;p&gt;&lt;/p&gt; o &lt;p&gt;&lt;br&gt;&lt;/p&gt;)</li>
                    <li>• Busca en el HTML resultante si hay atributos style="" que no deberían estar</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Test Editor page loaded');
            console.log('Ready to test paste functionality');
        });
    </script>
