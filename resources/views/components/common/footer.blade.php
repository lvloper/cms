<footer class="bg-primary bg-footer z-50 py-8 md:py-12">
    <div class="grid-cols-1 md:grid md:grid-cols-2 max-w-[980px] mx-auto">
        <div type="button" x-data @click="$dispatch('open-subs-modal')" aria-label="recibir informacion"
            class="text-center -mt-8 mb-6 md:hidden block bg-secondary text-white font-bold py-4 text-base uppercase md:w-[15%] md:rounded-3xl md:text-lg">
            recibí informacion
        </div>

        <div class=" ">
            <div class="mx-auto md:mx-0 w-[70%] md:w-[100%] text-white  text-base">
                <span class="font-bold lg:font-heavy">0-800-222 HUESPED (4837)</span><br>
                Av. Forest 345 (C1427CEA)<br>
                Ciudad Autónoma de<br>
                Buenos Aires- Argentina<br>
                Tel: <span class="font-heavy">(5411) 2120-9999</span><br>
                <span class="font-heavy">info@huesped.org.ar</span>
            </div>
        </div>
        {{-- <div class="py-6 mx-10  text-white md:hidden border-y-white border-y-2 cursor-pointer" x-data
            @click="$dispatch('toggle-mobile-menu')">
            <div class=" w-full text-center">Más</div>
        </div> --}}
        <div class="flex flex-col items-end justify-center mx-auto">
            <!--googleoff: index-->

            <!--googleon: index-->
            <div class="flex justify-around w-[80%] mx-auto text-white pt-6">
                @foreach (config('social-media.networks') as $network => $data)
                <a href="{{ $data['url'] }}" target="_blank" aria-label="Visitar {{ $network }}">
                    <x-dynamic-component :component="$data['icon']" class="w-6 h-6 hover:scale-110 animation" />
                </a>
                @endforeach
            </div>

        </div>
        <div class="col-span-2 text-center mt-8">
            <div class="flex flex-wrap justify-center gap-x-4 gap-y-2 mb-2">
                <a href="{{ url('politicas-de-privacidad') }}" class="text-xs font-bold text-white uppercase hover:underline">Política de privacidad</a>
                <span class="text-white hidden sm:inline">|</span>
                <a href="https://asociate.huesped.org.ar/actualizacion-de-datos/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-white uppercase hover:underline">Soy donante</a>
                <span class="text-white hidden sm:inline">|</span>
                <a href="https://asociate.huesped.org.ar/solicitud-de-baja-de-donacion/" target="_blank" rel="noopener noreferrer" class="text-xs font-bold text-white uppercase hover:underline">Baja de donación</a>
            </div>
            <span class="text-sm text-white opacity-30 ">© Copyright {{ now()->year }} Fundación Huésped</span>
        </div>
    </div>
</footer>

{{-- Abrir modal de newsletter si la URL tiene #newsletter --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#newsletter') {
            window.dispatchEvent(new CustomEvent('open-subs-modal'));
        }
    });
    window.addEventListener('hashchange', function() {
        if (window.location.hash === '#newsletter') {
            window.dispatchEvent(new CustomEvent('open-subs-modal'));
        }
    });
</script>

{{--<style>
    .bg-footer {
        /* Otros estilos que se aplicarán en todos los tamaños de pantalla */
    }

    @media (min-width: 768px) {
        .bg-footer {
            background-image: url(./img/layout/footer-bg.svg);
            background-repeat: no-repeat;
            background-position: right calc(100% + 3rem);
            background-size: 40% 100%;
        }
    }

    @media (min-width: 1024px) {
        .bg-footer {
            background-image: url(./img/layout/footer-bg.svg);
            background-repeat: no-repeat;
            background-position: right calc(100% + 3rem);
            background-size: 30% 100%;
        }
    }

    @media (min-width: 1368px) {
        .bg-footer {
            background-image: url(./img/layout/footer-bg.svg);
            background-repeat: no-repeat;
            background-position: right calc(100% + 3rem);
            background-size: 20% 100%;
        }
    }
</style>
--}}
