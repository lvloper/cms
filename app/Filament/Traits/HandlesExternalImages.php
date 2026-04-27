<?php

namespace App\Filament\Traits;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Log;

trait HandlesExternalImages
{
    /**
     * Resolve image field names (single and multiple) for the current model context.
     * Models can optionally define a static $imageFields = ['single' => [...], 'multiple' => [...]]
     * or a static getImageFields(): array method returning the same structure.
     */
    protected function resolveImageFields(): array
    {
        $single = ['image', 'image_mobile', 'photo', 'picture', 'avatar'];
        $multiple = ['images', 'gallery'];

        // Try to get model class from the Filament page/resource context
        $modelClass = null;
        if (method_exists($this, 'getModel')) {
            $modelClass = $this->getModel(); // usually a class string
        } elseif (method_exists($this, 'getRecord') && $this->getRecord()) {
            $modelClass = get_class($this->getRecord());
        }

        if (is_string($modelClass)) {
            // Static property override using reflection-friendly method
            try {
                $vars = \get_class_vars($modelClass);
                if (isset($vars['imageFields']) && is_array($vars['imageFields'])) {
                    $fields = $vars['imageFields'];
                    if (isset($fields['single']) && is_array($fields['single'])) {
                        $single = array_values(array_unique(array_merge($single, $fields['single'])));
                    }
                    if (isset($fields['multiple']) && is_array($fields['multiple'])) {
                        $multiple = array_values(array_unique(array_merge($multiple, $fields['multiple'])));
                    }
                }
            } catch (\Throwable $e) {
                // ignore and use defaults
            }

            // Static method override
            if (method_exists($modelClass, 'getImageFields')) {
                try {
                    $fields = $modelClass::getImageFields();
                    if (isset($fields['single']) && is_array($fields['single'])) {
                        $single = array_values(array_unique(array_merge($single, $fields['single'])));
                    }
                    if (isset($fields['multiple']) && is_array($fields['multiple'])) {
                        $multiple = array_values(array_unique(array_merge($multiple, $fields['multiple'])));
                    }
                } catch (\Throwable $e) {
                    // ignore and use defaults
                }
            }
        }

        return [
            'single' => $single,
            'multiple' => $multiple,
        ];
    }

    /**
     * Public accessor to image field names for reuse by Pages.
     */
    public function getImageFieldNames(): array
    {
        return $this->resolveImageFields();
    }

    /**
     * Resolve non-image file field config (e.g., PDFs) for the current model.
     * Returns an array with keys:
     * - single: string[] field names
     * - multiple: string[] field names
     * - directories: array<string, string> field => directory mapping
     * - extensions: array<string, string[]> field => allowed extensions
     * - contentTypes: array<string, string|string[]> field => allowed content-type(s) starts-with or contains
     */
    protected function resolveFileFields(): array
    {
        $single = ['pdf_file'];
        $multiple = [];
        $directories = [
            'pdf_file' => 'materials',
        ];
        $extensions = [
            'pdf_file' => ['pdf'],
        ];
        $contentTypes = [
            'pdf_file' => ['application/pdf', 'application/x-pdf'],
        ];

        $modelClass = null;
        if (method_exists($this, 'getModel')) {
            $modelClass = $this->getModel();
        } elseif (method_exists($this, 'getRecord') && $this->getRecord()) {
            $modelClass = get_class($this->getRecord());
        }

        if (is_string($modelClass)) {
            try {
                $vars = \get_class_vars($modelClass);
                if (isset($vars['fileFields']) && is_array($vars['fileFields'])) {
                    $cfg = $vars['fileFields'];
                    if (isset($cfg['single'])) $single = array_values(array_unique(array_merge($single, (array)$cfg['single'])));
                    if (isset($cfg['multiple'])) $multiple = array_values(array_unique(array_merge($multiple, (array)$cfg['multiple'])));
                    if (isset($cfg['directories']) && is_array($cfg['directories'])) $directories = array_merge($directories, $cfg['directories']);
                    if (isset($cfg['extensions']) && is_array($cfg['extensions'])) $extensions = array_merge($extensions, $cfg['extensions']);
                    if (isset($cfg['contentTypes']) && is_array($cfg['contentTypes'])) $contentTypes = array_merge($contentTypes, $cfg['contentTypes']);
                }
            } catch (\Throwable $e) {
                // ignore
            }

            if (method_exists($modelClass, 'getFileFields')) {
                try {
                    $cfg = $modelClass::getFileFields();
                    if (isset($cfg['single'])) $single = array_values(array_unique(array_merge($single, (array)$cfg['single'])));
                    if (isset($cfg['multiple'])) $multiple = array_values(array_unique(array_merge($multiple, (array)$cfg['multiple'])));
                    if (isset($cfg['directories']) && is_array($cfg['directories'])) $directories = array_merge($directories, $cfg['directories']);
                    if (isset($cfg['extensions']) && is_array($cfg['extensions'])) $extensions = array_merge($extensions, $cfg['extensions']);
                    if (isset($cfg['contentTypes']) && is_array($cfg['contentTypes'])) $contentTypes = array_merge($contentTypes, $cfg['contentTypes']);
                } catch (\Throwable $e) {
                    // ignore
                }
            }
        }

        return [
            'single' => $single,
            'multiple' => $multiple,
            'directories' => $directories,
            'extensions' => $extensions,
            'contentTypes' => $contentTypes,
        ];
    }

    public function getFileFieldConfig(): array
    {
        return $this->resolveFileFields();
    }

    /**
     * Download an image from external URL and store it locally
     * 
     * @param string $url External image URL
     * @param string $directory Directory to store the image (default: 'images')
     * @param string $disk Storage disk (default: 'public')
     * @return string|null Local file path or null if failed
     */
    public static function downloadExternalImage(string $url, string $directory = 'images', string $disk = 'public'): ?string
    {
        try {
            // Validate URL
            if (!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'http')) {
                return null;
            }

            // Get file extension from URL or default to jpg
            $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (empty($extension) || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $extension = 'jpg';
            }

            // Generate unique filename
            $filename = Str::random(40) . '.' . $extension;
            $filePath = $directory . '/' . $filename;

            // Download image with timeout and size limit
            $response = Http::timeout(30)
                ->withOptions([
                    'max_redirects' => 5,
                    'verify' => false, // For development, consider enabling in production
                ])
                ->get($url);

            if (!$response->successful()) {
                Log::warning("Failed to download image from URL: {$url}", [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return null;
            }

            // Check if response is actually an image
            $contentType = $response->header('Content-Type');
            if (!str_starts_with($contentType, 'image/')) {
                Log::warning("URL does not return an image: {$url}", [
                    'content_type' => $contentType
                ]);
                return null;
            }

            // Check file size (limit to 10MB)
            $imageData = $response->body();
            if (strlen($imageData) > 10 * 1024 * 1024) {
                Log::warning("Image too large: {$url}", [
                    'size' => strlen($imageData)
                ]);
                return null;
            }

            // Store the image
            if (Storage::disk($disk)->put($filePath, $imageData)) {
                Log::info("Successfully downloaded image from {$url} to {$filePath}");
                return $filePath;
            }

            return null;

        } catch (RequestException $e) {
            Log::error("HTTP error downloading image from {$url}: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("Error downloading image from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Download a non-image file (e.g., PDF) from external URL and store it locally
     */
    public static function downloadExternalFile(string $url, string $directory = 'files', string $disk = 'public', array $allowedExtensions = [], array $allowedContentTypes = []): ?string
    {
        try {
            if (!filter_var($url, FILTER_VALIDATE_URL) || !str_starts_with($url, 'http')) {
                return null;
            }

            $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if ($extension === '') {
                $extension = 'bin';
            }
            if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                // allow but normalize to the first allowed extension if missing/invalid
                $extension = $allowedExtensions[0];
            }

            $filename = \Illuminate\Support\Str::random(40) . '.' . $extension;
            $filePath = rtrim($directory, '/') . '/' . $filename;

            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->withOptions([
                    'max_redirects' => 5,
                    'verify' => false,
                ])->get($url);

            if (!$response->successful()) {
                \Illuminate\Support\Facades\Log::warning("Failed to download file from URL: {$url}", [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $contentType = $response->header('Content-Type') ?? '';
            if (!empty($allowedContentTypes)) {
                $ok = false;
                foreach ($allowedContentTypes as $ct) {
                    if (str_contains($contentType, $ct)) { $ok = true; break; }
                }
                if (!$ok) {
                    \Illuminate\Support\Facades\Log::warning("URL does not return allowed content-type: {$url}", [
                        'content_type' => $contentType,
                        'allowed' => $allowedContentTypes,
                    ]);
                    return null;
                }
            }

            $data = $response->body();
            if (strlen($data) > 50 * 1024 * 1024) { // 50MB limit
                \Illuminate\Support\Facades\Log::warning("File too large: {$url}", ['size' => strlen($data)]);
                return null;
            }

            if (\Illuminate\Support\Facades\Storage::disk($disk)->put($filePath, $data)) {
                \Illuminate\Support\Facades\Log::info("Successfully downloaded file from {$url} to {$filePath}");
                return $filePath;
            }

            return null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error downloading file from {$url}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Process image field - if it's an external URL, download it locally
     * 
     * @param mixed $value The image field value
     * @param string $directory Directory to store downloaded images
     * @param string $disk Storage disk
     * @return mixed Processed value (local path or original value)
     */
    public static function processImageField($value, string $directory = 'images', string $disk = 'public')
    {
        if (empty($value)) {
            return $value;
        }

        // Handle string values (single image)
        if (is_string($value)) {
            return static::processImageUrl($value, $directory, $disk);
        }

        // Handle array values (multiple images)
        if (is_array($value)) {
            return array_map(function ($url) use ($directory, $disk) {
                return static::processImageUrl($url, $directory, $disk);
            }, $value);
        }

        return $value;
    }

    /**
     * Process a single image URL
     * 
     * @param string $url Image URL
     * @param string $directory Directory to store downloaded images
     * @param string $disk Storage disk
     * @return string Original URL or local path if downloaded
     */
    private static function processImageUrl(string $url, string $directory, string $disk): string
    {
        // If it's already a local path, return as is
        if (!str_starts_with($url, 'http')) {
            return $url;
        }

        // Try to download the external image
        $localPath = static::downloadExternalImage($url, $directory, $disk);
        
        // Return local path if successful, otherwise return original URL
        return $localPath ?? $url;
    }

    /**
     * Check if a URL is an external image URL
     * 
     * @param string $url URL to check
     * @return bool True if it's an external image URL
     */
    public static function isExternalImageUrl(string $url): bool
    {
        if (!str_starts_with($url, 'http')) {
            return false;
        }

        // Check if URL has image extension
        $extension = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        
        return in_array(strtolower($extension), $imageExtensions);
    }

    /**
     * Process external images in form data array
     * 
     * @param array $data Form data array
     * @param string $directory Directory to store downloaded images
     * @param string $disk Storage disk
     * @return array Processed data array
     */
    protected function processExternalImagesInData(array $data, string $directory = 'images', string $disk = 'public'): array
    {
    // Determine image fields dynamically (with sensible defaults)
    $fields = $this->resolveImageFields();
    $singleImageFields = $fields['single'];
    $multipleImageFields = $fields['multiple'];
        
        // Process single image fields
        foreach ($singleImageFields as $field) {
            if (isset($data[$field])) {
                $processed = static::processImageField($data[$field], $directory, $disk);
                // Ensure single image fields always return a string
                if (is_array($processed)) {
                    $data[$field] = $processed[0] ?? null;
                } else {
                    $data[$field] = $processed;
                }
            }
        }
        
        // Process multiple image fields
        foreach ($multipleImageFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = static::processImageField($data[$field], $directory, $disk);
            }
        }

        // Also process route image if exists and model has route relationship
        if (isset($data['route']['image']) && method_exists($this->getModel(), 'route')) {
            $processed = static::processImageField($data['route']['image'], 'images/routes', $disk);
            // Route image should also be a single string
            if (is_array($processed)) {
                $data['route']['image'] = $processed[0] ?? null;
            } else {
                $data['route']['image'] = $processed;
            }
        }

        // Process additional non-image file fields (e.g., PDFs)
        $fileCfg = $this->resolveFileFields();
        foreach ($fileCfg['single'] as $field) {
            if (!isset($data[$field]) || empty($data[$field])) continue;
            $val = $data[$field];
            if (is_string($val) && str_starts_with($val, 'http')) {
                $dir = $fileCfg['directories'][$field] ?? 'files';
                $exts = $fileCfg['extensions'][$field] ?? [];
                $cts = $fileCfg['contentTypes'][$field] ?? [];
                $local = static::downloadExternalFile($val, $dir, $disk, $exts, (array)$cts);
                if ($local) {
                    $data[$field] = $local;
                }
            }
        }
        foreach ($fileCfg['multiple'] as $field) {
            if (!isset($data[$field]) || !is_array($data[$field])) continue;
            $dir = $fileCfg['directories'][$field] ?? 'files';
            $exts = $fileCfg['extensions'][$field] ?? [];
            $cts = $fileCfg['contentTypes'][$field] ?? [];
            $data[$field] = array_map(function($val) use ($dir, $disk, $exts, $cts) {
                if (is_string($val) && str_starts_with($val, 'http')) {
                    return static::downloadExternalFile($val, $dir, $disk, $exts, (array)$cts) ?? $val;
                }
                return $val;
            }, $data[$field]);
        }

        return $data;
    }
}