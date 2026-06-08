@php
$url = '#';
$is_modal = false;

if (isset($attrs) && $attrs) {
    extract($attrs);
}

$btnLabel = isset($btn_label) && $btn_label ? $btn_label : $slot;

$allowWireNavitage = isset($allowWireNavitage) && $allowWireNavitage;
$disableWireNavitage = isset($disableWireNavitage) && $disableWireNavitage;

if ($disableWireNavitage) {
    $allowWireNavitage = false;
}

$anchor = isset($anchor) && $anchor ? '#' . $anchor : '';

// Keep original route_id value to support special options (0 external, -1 file)
$route_id = isset($route_id) ? $route_id : null;
$routeClass = (isset($route_id) && is_numeric($route_id) && (int)$route_id >= 1)
    ? \App\Models\Route::find((int)$route_id)
    : null;

if ($routeClass) {
    $url = url($routeClass->url);
    $layout = $routeClass->layout;
    $allowWireNavitage = true;

    if ($disableWireNavitage) {
        $allowWireNavitage = false;
    }
}

if (isset($external_url) && $external_url) {
    $url = $external_url;
    $allowWireNavitage = false;
} else {
    $external_url = null;
}


// Handle file download option when route_id indicates file (-1)
$isFile = isset($route_id) && ((string)$route_id === '-1' || (is_int($route_id) && $route_id === -1));
$filePath = null;
if ($isFile) {
    if (isset($file) && $file) {
        if (is_string($file)) {
            $filePath = $file;
        } elseif (is_array($file)) {
            // Common shapes: ['path' => '...'] or a list [ 'path', ... ] or first item array
            if (isset($file['path']) && is_string($file['path'])) {
                $filePath = $file['path'];
            } elseif (isset($file['url']) && is_string($file['url'])) {
                $url = $file['url'];
            } elseif (function_exists('array_is_list') && array_is_list($file) && isset($file[0])) {
                $first = $file[0];
                if (is_string($first)) {
                    $filePath = $first;
                } elseif (is_array($first)) {
                    if (isset($first['path']) && is_string($first['path'])) {
                        $filePath = $first['path'];
                    } elseif (isset($first['url']) && is_string($first['url'])) {
                        $url = $first['url'];
                    }
                }
            }
        }
        // If no direct URL was set from structure, use Storage URL from path
        if (($url === '#') && $filePath) {
            $url = \Illuminate\Support\Facades\Storage::url($filePath);
        }
    }
    // Disable SPA navigation for file downloads
    $allowWireNavitage = false;
}

// Prepare download attribute if file
$downloadAttr = '';
if ($isFile) {
    $downloadName = isset($download_name) && $download_name ? $download_name : null;
    if (!$downloadName) {
        if (isset($file) && is_array($file)) {
            $downloadName = $file['name'] ?? (isset($file[0]) && is_array($file[0]) ? ($file[0]['name'] ?? null) : (isset($file[0]) && is_string($file[0]) ? basename($file[0]) : null));
        } elseif (isset($file) && is_string($file)) {
            $downloadName = basename($file);
        } elseif ($filePath) {
            $downloadName = basename($filePath);
        }
    }
    $downloadAttr = $downloadName ? 'download="' . e($downloadName) . '"' : 'download';
}

if (isset($layout) && $layout == 'modal' && isset($new_window) && !$new_window && $routeClass) {
    $is_modal = true;
    $content = view('pages/blocksList', ['blocks' => $routeClass->routable->blocks, 'notLayout' => true])->render();
}

// Determine if there's something to link to (route, external url, or file with resolved url/path)
$fileResolved = $isFile && (($filePath !== null) || ($url !== '#'));
$isHasRoute = ($routeClass !== null) || ($external_url !== null) || $fileResolved;

// // Deshabilitar wire:navigate si hay ancla, porque puede causar problemas con el scroll
// if ($anchor && $allowWireNavitage) {
//     $allowWireNavitage = false;
// }

// $allowWireNavitage = false;
@endphp


@if( $isHasRoute )

@if( $is_modal )
<x-modal ref="modal-page-{{ $routeClass->id }}" buttonClass="{{ isset($class) ? $class : '' }}">
    <x-slot name="button">
        {{ $btnLabel }}
    </x-slot>
    <x-slot name="content">
        {!! $content !!}
    </x-slot>
</x-modal>

@else
<a href="{{ $url }}{{ $anchor }}" {{ $allowWireNavitage ? 'wire:navigate.hover' : '' }} {!! $isFile ? $downloadAttr : '' !!}
    class="{{ isset($class) ? $class : '' }} " {{ isset($new_window) && $new_window ? 'target="_blank"' : '' }}
    alt="{{ $route->title ?? '' }}">
    {{ $btnLabel }}

</a>
@endif

@elseif( !isset($hideIfNull) || !$hideIfNull )
<div class="{{ isset($class) ? $class : '' }}">
    {{ $btnLabel }}
</div>
@endif