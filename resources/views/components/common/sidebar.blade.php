@php
$id = 'sidebar';
@endphp
<div class="z-20 bg-gray-2 sidebar sticky top-[80px] " x-data="{
    anchors: [],
    sections: [],
}">
    <div class="overflow-y-auto h-full">
        <!-- Swiper para pantallas más pequeñas (2xl:hidden) -->
        <div class="py-2 2xl:hidden">
            <div class="relative px-6 opacity-0 fade-in lg:mt-7 overflow-hidden ">
                <button
                    class="{{ $id }}-swiper-button-prev absolute left-0 top-1/2 transform -translate-y-1/2 z-10 swiper-custom-buttons text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>

                <swiper-container slides-per-view="auto" space-between="20" id="slidebar-swiper"
                    x-data="sidebarScrollSpy(true)" navigation-next-el=".{{ $id }}-swiper-button-next"
                    navigation-prev-el=".{{ $id }}-swiper-button-prev">
                    @foreach ($index as $item)
                    <swiper-slide class="!w-auto sidebar-swiper-slide" data-anchor="{{ $item['id'] }}">
                        <li class="text-base">
                            <a href="#{{ $item['id'] }}" :class="{
                                'text-primary border-b-primary': activeSection === '{{ $item['id'] }}',
                                'border-b-gray md:hover:text-primary md:hover:border-b-primary': activeSection !== '{{ $item['id'] }}'
                            }" class="sidebar-anchor-link block flex justify-between w-full">
                                <span class="block ml-4 w-full font-medium">{{ $item['title'] }}</span>
                            </a>
                        </li>
                    </swiper-slide>
                    @endforeach
                </swiper-container>

                <button
                    class="{{ $id }}-swiper-button-next absolute -right-1 top-1/2 transform -translate-y-1/2 z-10 swiper-custom-buttons text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18l6-6-6-6" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Lista normal para pantallas 2xl y más grandes -->
        <div x-data="sidebarScrollSpy()" x-init="init()"
            class="hidden relative flex-col gap-y-6 justify-between content-around h-full 2xl:flex">
            <ul class="flex flex-col gap-3 sm:pt-10 sm:pb-0 sm:mx-8">
                @foreach ($index as $item)
                <li class="pb-2 transition-all text-sm xl:text-base futura-medium duration-400 group {{ $loop->last ? 'border-b-0' : 'border-b-2' }}"
                    :class="{
                        'text-primary border-b-primary': activeSection === '{{ $item['id'] }}',
                        'border-b-gray md:hover:text-primary md:hover:border-b-primary': activeSection !== '{{ $item['id'] }}'
                    }">
                    <a href="#{{ $item['id'] }}" @click.prevent="scrollToSection('{{ $item['id'] }}')"
                        class="flex justify-between w-full sidebar-anchor-link">
                        <span class="block ml-2 font-medium">{{ $item['title'] }}</span>
                        <div class="flex items-center">
                            <svg class="mr-2 transition-all scale-75 duration-400" :class="{
                                    'text-primary -rotate-45': activeSection === '{{ $item['id'] }}',
                                    'text-secondary md:group-hover:text-primary group-hover:rotate-45': activeSection !== '{{ $item['id'] }}'
                                }" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M19 13V19H13" />
                                <path d="M5 5L19 19" />
                            </svg>
                        </div>
                    </a>
                </li>
                @endforeach
            </ul>

            <div class="mt-4" :style="{ transform: scrolled ? 'translateY(0px)' : 'translateY(-60px)' }"></div>
        </div>
    </div>
</div>

<script>
    function sidebarScrollSpy(isSwiper = false) {
        return {
            activeSection: '',
            sections: [],
            scrolled: window.scrollY > 16,
            init() {
                const indexData = @json($index ?? []);
                this.anchors = Array.isArray(indexData) ? indexData.map(item => item.id) : [];
                this.sections = Array.isArray(indexData) ? indexData.map(item => item.id) : [];

                gsap.utils.toArray('.block').forEach((block) => {
                    gsap.to(block, {
                        scrollTrigger: {
                            trigger: block,
                            start: 'top center',
                            onEnter: () => {
                                const id = block.getAttribute('id');
                                if (this.anchors.includes(id)) {
                                    this.activeSection = id;


                                    if (!isSwiper) {
                                        let slide = document.querySelector('.sidebar-swiper-slide[data-anchor="'+id+'"]');
                                        let swiper = document.querySelector('#slidebar-swiper').swiper;

                                        if (swiper && slide) {
                                            const slideIndex = Array.from(slide.parentNode.children).indexOf(slide);
                                            swiper.slideTo(slideIndex, 200, false, 'center');
                                        }
                                    }
                                }
                            },
                            onEnterBack: () => {
                                const id = block.getAttribute('id');
                                if (this.anchors.includes(id)) {
                                    this.activeSection = id;
                                }
                                if (!isSwiper) {
                                        let slide = document.querySelector('.sidebar-swiper-slide[data-anchor="'+id+'"]');
                                        let swiper = document.querySelector('#slidebar-swiper').swiper;

                                        if (swiper && slide) {
                                            const slideIndex = Array.from(slide.parentNode.children).indexOf(slide);
                                            swiper.slideTo(slideIndex, 200, false, 'center');
                                        }
                                    }
                            },
                        }
                    });
                });

                const hash = window.location.hash.substring(1);
                if (hash && this.sections.includes(hash)) {
                    this.activeSection = hash;
                    this.$nextTick(() => {
                        const targetElement = document.getElementById(hash);
                        if (targetElement) {
                            const headerOffset = 80;
                            const elementPosition = targetElement.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                            window.scrollTo({ top: offsetPosition, behavior: 'smooth' });
                        }
                    });
                } else if (this.sections.length > 0) {
                    this.activeSection = this.sections[0];
                     this.$nextTick(() => {
                        const firstSectionElement = document.getElementById(this.sections[0]);
                        if(window.scrollY > 0 && firstSectionElement) {
                            const headerOffset = 80;
                            const elementPosition = firstSectionElement.getBoundingClientRect().top;
                            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                            window.scrollTo({ top: offsetPosition, behavior: 'smooth'});
                        }
                    });
                }

                window.addEventListener('scroll', () => {
                    this.scrolled = window.scrollY > 16;
                    let current = '';
                    const scrollY = window.scrollY;
                    const offset = 80 + 20;

                    this.sections.forEach((sectionId, index) => {
                        const sectionElement = document.getElementById(sectionId);
                        if (sectionElement) {
                            const sectionTop = sectionElement.offsetTop;
                            const sectionHeight = sectionElement.offsetHeight;

                            if (scrollY >= sectionTop - offset && scrollY < sectionTop + sectionHeight - offset) {
                                current = sectionId;
                            }
                        }
                    });

                    if (current && current !== this.activeSection) {
                        this.activeSection = current;


                    } else if (scrollY < offset && this.sections.length > 0 && !current) {
                        if (this.activeSection !== this.sections[0]) {
                           this.activeSection = this.sections[0];
                        }
                    }


                }, { passive: true });

                this.$nextTick(() => {
                     window.dispatchEvent(new Event('scroll'));
                });
            },
            scrollToSection(sectionId) {
                this.activeSection = sectionId;
                const targetElement = document.getElementById(sectionId);
                if (targetElement) {
                    const headerOffset = 80;
                    const elementPosition = targetElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                    history.replaceState(null, null, '#' + sectionId);
                }
            }
        }
    }
</script>
