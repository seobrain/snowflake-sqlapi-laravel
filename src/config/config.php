<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tenant ID
    |--------------------------------------------------------------------------
    |
    | This value is equal to the 'Directory (tenant) ID' as found in the Azure
    | portal
    |
    */
    'tenant_id' => env('AZURE_TENANT_ID'),

    /*
    |--------------------------------------------------------------------------
    | Client Info
    |--------------------------------------------------------------------------
    |
    | These values are equal to 'Application (client) ID' and the secret you
    | made in 'Client secrets' as found in the Azure portal
    |
    */
    'client' => [
        'id' => env('AZURE_CLIENT_ID'),
        'secret' => env('AZURE_CLIENT_SECRET'),
    ],

    'scope' => env('AZURE_CLIENT_SCOPE'),

    'account' => env('SF_ACCOUNT'),
    'warehouse' => env('SF_WAREHOUSE'),
    'role' => env('SF_ROLE'),

    'dateformat' => env('SF_DATE_OUTPUT_FORMAT', "YYYY-MM-DD")
];
