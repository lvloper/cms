<?php

if (!function_exists('app_config')) {
    /**
     * Get the configuration service instance
     *
     * @return \App\Services\ConfigurationService
     */
    function app_config(): \App\Services\ConfigurationService
    {
        return app('app.config');
    }
}

if (!function_exists('config_value')) {
    /**
     * Get a configuration value by key
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function config_value(string $key, $default = null)
    {
        return app_config()->get($key, $default);
    }
}

if (!function_exists('config_text')) {
    /**
     * Get a text configuration value
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    function config_text(string $key, string $default = ''): string
    {
        return app_config()->text($key, $default);
    }
}

if (!function_exists('config_rich_text')) {
    /**
     * Get a rich text configuration value
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    function config_rich_text(string $key, string $default = ''): string
    {
        return app_config()->richText($key, $default);
    }
}

if (!function_exists('config_url')) {
    /**
     * Get a URL configuration value
     *
     * @param string $key
     * @param array $default
     * @return array
     */
    function config_url(string $key, array $default = []): array
    {
        return app_config()->url($key, $default);
    }
}

if (!function_exists('config_url_string')) {
    /**
     * Get a URL configuration as string
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    function config_url_string(string $key, string $default = ''): string
    {
        return app_config()->urlString($key, $default);
    }
}

if (!function_exists('config_url_target')) {
    /**
     * Get the target attribute for a URL configuration
     *
     * @param string $key
     * @return string
     */
    function config_url_target(string $key): string
    {
        return app_config()->urlTarget($key);
    }
}

if (!function_exists('config_image')) {
    /**
     * Get an image configuration value
     *
     * @param string $key
     * @param string $default
     * @return string
     */
    function config_image(string $key, string $default = ''): string
    {
        return app_config()->image($key, $default);
    }
}

if (!function_exists('mb_split')) {
    /**
     * Compatibility polyfill for environments missing mb_split.
     *
     * @param string $pattern
     * @param string $string
     * @param int $limit
     * @return array|false
     */
    function mb_split(string $pattern, string $string, int $limit = -1)
    {
        $delimiter = '/';
        $escaped = str_replace($delimiter, '\\' . $delimiter, $pattern);
        $regex = $delimiter . $escaped . $delimiter . 'u';

        return preg_split($regex, $string, $limit);
    }
}
