<?php

namespace Seobrain\SnowflakeSqlapiLaravel;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Snowflake {
    /**
     * @throws RequestException
     */
    private function getsAccessToken(): string
    {
        if (Cache::has('sf_token')) return Cache::get('sf_token');

        $tenant = config('snowflakeapi.tenant_id');
        $url = "https://login.microsoftonline.com/$tenant/oauth2/v2.0/token";
        $data = [
            'client_id' => config('snowflakeapi.client.id'),
            'client_secret' => config('snowflakeapi.client.secret'),
            'scope' => config('snowflakeapi.scope'),
            'grant_type' => 'client_credentials'
        ];

        $response = Http::acceptJson()->asForm()->post($url, $data)->throw()->json();

        Cache::put('sf_token', $response['access_token'], $response['expires_in']);

        return $response['access_token'];
    }

    /**
     * @throws RequestException
     */
    public function postStatement($statement, $params = [])
    {
        if (!$statement) return false;

        $sf_account = config('snowflakeapi.account');
        $sf_server = "$sf_account.snowflakecomputing.com";
        $sf_warehouse = config('snowflakeapi.warehouse');
        $sf_role = config('snowflakeapi.role');
        $sf_timeout = 1000;

        $token = $this->getsAccessToken();
        $headers = ['X-Snowflake-Authorization-Token-Type' => 'OAUTH'];
        $url = "https://$sf_server/api/v2/statements";
        $params = http_build_query($params);

        $body = [
            'statement' => $statement,
            'timeout' => $sf_timeout,
            'warehouse' => $sf_warehouse,
            'role' => $sf_role
        ];

        $response = Http::acceptJson()
            ->withToken($token)
            ->withHeaders($headers)
            ->post("$url?$params", $body)
            ->json();

        return $response;
    }
}
