<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Plugins Directory
    |--------------------------------------------------------------------------
    |
    | The absolute path for loading plugins.
    | Defaults to `base_path()."/plugins"`.
    |
    */
    'directory' => env('PLUGINS_DIR'),

    /*
    |--------------------------------------------------------------------------
    | Plugins Assets URL
    |--------------------------------------------------------------------------
    |
    | The URL to access plugin's assets (CSS, JavaScript etc.).
    | Defaults to `http://site_url/plugins`.
    |
    */
    'url' => env('PLUGINS_URL'),

    /*
    |--------------------------------------------------------------------------
    | Plugins Market Source
    |--------------------------------------------------------------------------
    |
    | Specify where to get plugins' metadata for plugin market.
    |
    */
    'registry' => env(
        'PLUGINS_REGISTRY',
        'https://d2jw1l0ullrzt6.cloudfront.net/registry_{lang}.json'
    ),

    /*
    |--------------------------------------------------------------------------
    | Plugins Market Localization
    |--------------------------------------------------------------------------
    |
    | Supported languages of plugins market registry will be listed here.
    |
    */
    'locales' => ['en', 'zh_CN'],
];
