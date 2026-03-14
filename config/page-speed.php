<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Google PageSpeed API Key
    |--------------------------------------------------------------------------
    |
    | Your Google PageSpeed Insights API key. You can obtain one from
    | the Google Cloud Console: https://console.cloud.google.com/
    |
    */

    'api_key' => env('GOOGLE_PAGESPEED_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Route Path
    |--------------------------------------------------------------------------
    |
    | The URI path where the PageSpeed panel will be accessible.
    |
    */

    'path' => env('PAGESPEED_PATH', 'page-speed'),

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    |
    | These middleware will be assigned to the PageSpeed routes. You may
    | add your own middleware to this list as needed.
    |
    */

    'middleware' => [
        'web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Domain
    |--------------------------------------------------------------------------
    |
    | This is the subdomain where PageSpeed will be accessible from. If the
    | setting is null, PageSpeed will reside under the same domain as the
    | application. Otherwise, this value will serve as the subdomain.
    |
    */

    'domain' => env('PAGESPEED_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Default Strategy
    |--------------------------------------------------------------------------
    |
    | The default strategy for PageSpeed tests. Can be "mobile" or "desktop".
    |
    */

    'default_strategy' => 'mobile',

    /*
    |--------------------------------------------------------------------------
    | Default URL
    |--------------------------------------------------------------------------
    |
    | The default URL to test when the panel opens. Set to null to use
    | the application URL with '/'.
    |
    */

    'default_url' => null,

    /*
    |--------------------------------------------------------------------------
    | History Limit
    |--------------------------------------------------------------------------
    |
    | The maximum number of test results to display in the history table.
    |
    */

    'history_limit' => 50,

    /*
    |--------------------------------------------------------------------------
    | API Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout in seconds for Google PageSpeed API requests.
    |
    */

    'timeout' => 60,

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | This option may be used to disable the PageSpeed panel entirely.
    |
    */

    'enabled' => env('PAGESPEED_ENABLED', true),

];
