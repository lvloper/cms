<?php

use App\Services\ConfigurationService;

if (! function_exists('app_config')) {
    /**
     * Get the configuration service instance
     */
    function app_config(): ConfigurationService
    {
        return app('app.config');
    }
}

if (! function_exists('config_value')) {
    /**
     * Get a configuration value by key
     *
     * @param  mixed  $default
     * @return mixed
     */
    function config_value(string $key, $default = null)
    {
        return app_config()->get($key, $default);
    }
}

if (! function_exists('config_text')) {
    /**
     * Get a text configuration value
     */
    function config_text(string $key, string $default = ''): string
    {
        return app_config()->text($key, $default);
    }
}

if (! function_exists('config_rich_text')) {
    /**
     * Get a rich text configuration value
     */
    function config_rich_text(string $key, string $default = ''): string
    {
        return app_config()->richText($key, $default);
    }
}

if (! function_exists('config_url')) {
    /**
     * Get a URL configuration value
     */
    function config_url(string $key, array $default = []): array
    {
        return app_config()->url($key, $default);
    }
}

if (! function_exists('config_url_string')) {
    /**
     * Get a URL configuration as string
     */
    function config_url_string(string $key, string $default = ''): string
    {
        return app_config()->urlString($key, $default);
    }
}

if (! function_exists('config_url_target')) {
    /**
     * Get the target attribute for a URL configuration
     */
    function config_url_target(string $key): string
    {
        return app_config()->urlTarget($key);
    }
}

if (! function_exists('config_image')) {
    /**
     * Get an image configuration value
     */
    function config_image(string $key, string $default = ''): string
    {
        return app_config()->image($key, $default);
    }
}

if (! function_exists('mb_split')) {
    /**
     * Compatibility polyfill for environments missing mb_split.
     *
     * @return array|false
     */
    function mb_split(string $pattern, string $string, int $limit = -1)
    {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\'.$delimiter, $pattern);
        $regex = $delimiter.$escaped.$delimiter.'u';

        return preg_split($regex, $string, $limit);
    }
}
