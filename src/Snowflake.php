<?php

namespace Seobrain\SnowflakeSqlapiLaravel;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Snowflake {
    /**
     * @throws RequestException
     */
    public function getsAccessToken(): string
    {
        if (Cache::has('sf_token')) return Cache::get('sf_token');

        $tenant = config('snowflakepi.tenant_id');
        $client_id = config('snowflakepi.client.id');
        $client_secret = config('snowflakepi.client.secret');
        $scope = config('snowflakepi.scope');

        $url = "https://login.microsoftonline.com/$tenant/oauth2/v2.0/token";
        $data = [
            'client_id' => $client_id,
            'client_secret' => $client_secret,
            'scope' => $scope,
            'grant_type' => 'client_credentials'
        ];

        $response = Http::acceptJson()->asForm()->post($url, $data)->throw()->json();

        $access_token = $response['access_token'];
        $expires_in = $response['expires_in'];

        Cache::put('sf_token', $access_token, $expires_in);

        return $access_token;
    }
}
