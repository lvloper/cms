<footer class="bg-primary z-50 py-8 md:py-12">
    <div class="container mx-auto max-w-[980px] px-6 text-center text-white">
        <div class="mb-4 text-sm uppercase tracking-wide opacity-80">
            {{ config_text('site-name', 'CMS Base') }}
        </div>
        <div class="text-sm opacity-70">
            © {{ now()->year }} {{ config_text('site-name', 'CMS Base') }}
        </div>
    </div>
</footer>
