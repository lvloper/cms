{{-- Device Selector Component --}}
<div class="fi-visual-editor__devices">
    <div class="fi-visual-editor__device-actions">
        <x-filament::icon-button 
            color="gray" 
            icon="heroicon-o-device-phone-mobile"
            x-on:click="cntWidth = getDeviceWidth('mobile'); currentDevice = 'mobile';" 
        />
        <x-filament::icon-button 
            color="gray" 
            icon="heroicon-o-device-tablet"
            x-on:click="cntWidth = getDeviceWidth('tablet'); currentDevice = 'tablet';" 
        />
        <x-filament::icon-button 
            color="gray" 
            icon="heroicon-o-computer-desktop"
            x-on:click="cntWidth = getDeviceWidth('desktop'); currentDevice = 'desktop';" 
        />
    </div>

    {{-- Reload All Previews Button --}}
    {{-- <button 
        type="button"
        x-on:click="window.blockPreviewManager?.reloadAll()"
        class="flex items-center gap-x-2 px-3 py-1.5 text-sm font-medium rounded-lg bg-white dark:bg-gray-800 shadow-sm hover:shadow-md transition-all border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 text-gray-700 dark:text-gray-300"
        title="Recargar todos los previews"
    >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        <span>Recargar</span>
    </button> --}}
</div>
