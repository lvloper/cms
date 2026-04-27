@php
$modalId = Str::slug($ref ?? 'modal-'.uniqid());
$theme = $theme ?? 'default';
$backUrl = $backUrl ?? null;

$styles = match ($theme) {
'transparent' => 'shadow-2xl',
default => 'py-16 shadow-2xl rounded-t-2xl md:rounded-3xl bg-white md:py-8',
}
@endphp

@isset ($button)
<button type="button" x-data @click="$dispatch('open-{{ $modalId }}')"
    data-modal-id="{{ $modalId }}"
    class="{{ isset($buttonClass) ? $buttonClass : '' }}" {{ $attributes }}>

    {{ $button }}
</button>
@endisset

@pushOnce('modals', $modalId)

<div x-ref="{{ $modalId }}" x-data="{
        modalIsOpen: false,
        delay: {{ isset($delay) ? $delay * 1000 : 0 }},
        backUrl: {{ json_encode($backUrl) }},
        init() {
            if ({{ isset($open) ? $open : 'false' }}) {
                if (this.delay > 0) {
                    setTimeout(() => this.toggleModal(true), this.delay);
                } else {
                    this.toggleModal(true);
                }
            }
        },
        toggleModal(value = null) {
            this.modalIsOpen = value ?? !this.modalIsOpen;
            if (this.modalIsOpen) {
                document.body.style.overflow = 'hidden';
                document.documentElement.style.overflow = 'hidden';
                // Event: modal opened
                try { window.dispatchEvent(new CustomEvent('modal-opened', { detail: { modalId: '{{ $modalId }}' } })); } catch(e) {}
            } else {
                document.body.style.overflow = '';
                document.documentElement.style.overflow = '';
                if (this.backUrl) {
                    try {
                        window.history.replaceState(window.history.state, '', this.backUrl);
                    } catch (e) {
                        // Fallback: ignore if history is not available
                    }
                }
                // Event: modal closed
                try { window.dispatchEvent(new CustomEvent('modal-closed', { detail: { modalId: '{{ $modalId }}' } })); } catch(e) {}
            }
        }
    }">

    {{-- <button @click="toggleModal(true)" class="pt-10">{{ $modalId }}</button> --}}

    <div x-cloak @open-{{ $modalId }}.window="toggleModal(true)" @close-{{ $modalId }}.window="toggleModal(false)" x-show="modalIsOpen"
        x-transition.opacity.duration.200ms @keydown.esc.window="toggleModal(false)" @click.self="toggleModal(false)"
        class="flex fixed inset-0 z-[100] justify-center items-start
             pt-16 pb-0 backdrop-blur-md bg-[rgba(0,0,0,0.7)] sm:items-start 
             lg:p-16 overflow-y-auto " role="dialog" aria-modal="true" aria-labelledby="defaultModalTitle">
        <!-- Modal Dialog -->
        <div x-show="modalIsOpen"
            x-transition:enter="transition ease-out duration-200 delay-100 motion-reduce:transition-opacity"
            x-transition:enter-start="opacity-0 translate-y-[100vh]" x-transition:enter-end="opacity-100 translate-y-0"
            class="mx-auto {{ $styles }}
                 container-modal{{ isset($size) ? '-'.$size : '' }}
             modal-content relative">

            <button @click="toggleModal(false)" aria-label="close modal " style="left:calc( 100% + 50px )"
                class="sticky top-0 right-2 md:top-4 md:right-4 text-{{ isset($closeColor) ? $closeColor : 'primary' }}">
                <svg xmlns="http://www.w3.org/2000/svg"
                    class="absolute w-8 md:w-12 right-0 -top-[38px] md:right-[-70px] md:-top-[18px] -mb-[52px]"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-circle-x">
                    <circle cx="12" cy="12" r="10" />
                    <path d="m15 9-6 6" />
                    <path d="m9 9 6 6" />
                </svg>
            </button>

            {{ $content ?? $slot ?? '' }}
        </div>
    </div>
</div>
@endpushOnce