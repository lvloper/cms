<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Página no encontrada</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .title-animate {
            transform: translateY(150px);
        }
        .description-animate {
            opacity: 0;
            transform: translateY(30px);
        }
        .error-number {
            font-size: clamp(80px, 5vw, 200px);
            font-weight: 900;
            line-height: 1;
            margin: 2rem 0;
            color:white;
        }
    </style>
</head>
<body>
    <x-layout>
        <div class="pt-0 bg-gray-3 min-h-screen">
            <!-- Hero Section -->
            <div class="bg-primary">
                <div class="relative" style="z-index: 1;">
                    <div class="flex flex-col items-center justify-center container text-center">
                        <div class="error-number my-8">
                            404
                        </div>
                        <h1 class="title-animate uppercase text-white max-w-[900px] font-bold text-2xl lg:text-4xl leading-tight mb-6">
                            La página que buscás no existe
                        </h1>
                        <p class="description-animate text-white text-base lg:text-lg max-w-[700px] mb-8 futura-light">
                            Es posible que la URL haya cambiado o que el contenido ya no esté disponible. 
                            Usá el buscador para encontrar lo que necesitás.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div style="z-index: 1;" class="relative md:pt-[4rem] 2xl:pt-[4rem] font-sans leading-normal bg-white">
                <div class="container">
                    <div class="py-6 lg:py-10 bg-white">
                        @php
                            // Extraer el término de búsqueda de la URL
                            $path = request()->path();
                            $searchTerm = '';
                            
                            // Intentar extraer el último segmento de la URL como término de búsqueda
                            $segments = array_filter(explode('/', $path));
                            $lastSegment = end($segments);
                            
                            // Limpiar y formatear el término de búsqueda
                            if ($lastSegment && $lastSegment !== '/' && strlen($lastSegment) > 2) {
                                // Reemplazar guiones por espacios
                                $searchTerm = str_replace(['-', '_'], ' ', $lastSegment);
                            }
                        @endphp
                        
                        @if($searchTerm)
                            <div class="mb-6 text-center">
                                <p class="text-lg text-gray-600 futura-light">
                                    Mostrando resultados para: <strong class="text-primary">"{{ ucwords($searchTerm) }}"</strong>
                                </p>
                            </div>
                        @endif

                        <livewire:search-component 
                            :isFullPage="true" 
                            :autoSearch="!empty($searchTerm)"
                            :initialSearch="$searchTerm"
                        />
                    </div>

                    <!-- Quick Links -->
                    <div class="py-10 border-t border-gray-200">
                        <h2 class="text-2xl font-bold text-center mb-8 text-gray-800">
                            O visitá alguna de estas secciones:
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <a href="/" class="group p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-primary transition-all duration-300">
                                <div class="text-primary text-3xl mb-3">🏠</div>
                                <h3 class="font-bold text-lg mb-2 group-hover:text-primary transition-colors">Inicio</h3>
                                <p class="text-sm text-gray-600">Ir a la página principal</p>
                            </a>
                            
                            <a href="/informacion" class="group p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-primary transition-all duration-300">
                                <div class="text-primary text-3xl mb-3">ℹ️</div>
                                <h3 class="font-bold text-lg mb-2 group-hover:text-primary transition-colors">Información</h3>
                                <p class="text-sm text-gray-600">Encontrá información accesible y basada en evidencia en nuestro sitio. Podés usar el buscador para introducir tu pregunta o navegar el índice.</p>
                            </a>
                            
                            <a href="/novedades" class="group p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-primary transition-all duration-300">
                                <div class="text-primary text-3xl mb-3">📰</div>
                                <h3 class="font-bold text-lg mb-2 group-hover:text-primary transition-colors">Novedades</h3>
                                <p class="text-sm text-gray-600">Últimas noticias y actualizaciones</p>
                            </a>
                            
                            <a href="/servicios" class="group p-6 bg-white border-2 border-gray-200 rounded-lg hover:border-primary transition-all duration-300">
                                <div class="text-primary text-3xl mb-3">🏥</div>
                                <h3 class="font-bold text-lg mb-2 group-hover:text-primary transition-colors">Servicios</h3>
                                <p class="text-sm text-gray-600">Encontrá los servicios disponibles en el sitio y la información relacionada en cada sección.</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Animations
                const title = document.querySelector('.title-animate');
                const descriptions = document.querySelectorAll('.description-animate');

                if (typeof gsap !== 'undefined') {
                    gsap.to(title, {
                        scrollTrigger: {
                            trigger: title,
                            start: "top 80%",
                            end: "bottom 20%",
                            toggleActions: 'play none none none'
                        },
                        y: 0,
                        duration: 1,
                        ease: "power3.out"
                    });

                    descriptions.forEach((desc, index) => {
                        gsap.to(desc, {
                            scrollTrigger: {
                                trigger: desc,
                                start: "top 80%",
                                toggleActions: 'play none none none',
                            },
                            y: 0,
                            opacity: 1,
                            duration: 1,
                            delay: 0.3,
                            ease: "power2.out"
                        });
                    });
                }
            });
        </script>
        @endpush
    </x-layout>
</body>
</html>
