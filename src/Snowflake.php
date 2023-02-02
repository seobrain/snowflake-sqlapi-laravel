<?php

namespace Seobrain\SnowflakeSqlapiLaravel;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Snowflake {

    public array $response = [];

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
     * @param $statement
     * @param int $timeout
     * @param bool $async
     * @return false|Snowflake
     * @throws RequestException
     */
    public function postStatement($statement, int $timeout = 60, bool $async = false): false|static
    {
        if (!$statement) return false;

        $sf_account = config('snowflakeapi.account');
        $sf_warehouse = config('snowflakeapi.warehouse');
        $sf_role = config('snowflakeapi.role');
        $sf_timeout = 1000;

        $token = $this->getsAccessToken();
        $headers = ['X-Snowflake-Authorization-Token-Type' => 'OAUTH'];
        $url = "https://$sf_account.snowflakecomputing.com/api/v2/statements";
        $url .= $async ? '?async=true' : '';

        $body = [
            'statement' => $statement,
            'timeout' => $sf_timeout,
            'warehouse' => $sf_warehouse,
            'role' => $sf_role,
            "parameters" => [
                "DATE_OUTPUT_FORMAT" => config('snowflakeapi.dateformat')
            ]
        ];

        $this->response = Http::acceptJson()
            ->timeout($timeout)
            ->withToken($token)
            ->withHeaders($headers)
            ->post($url, $body)
            ->json();

        return $this;
    }

    /**
     * @param string $statement
     * @return false|Snowflake
     * @throws RequestException
     */
    public function getStatement(string $statement): false|static
    {
        if (!$statement) return false;

        $sf_account = config('snowflakeapi.account');

        $token = $this->getsAccessToken();
        $headers = ['X-Snowflake-Authorization-Token-Type' => 'OAUTH'];
        $url = "https://$sf_account.snowflakecomputing.com/api/v2/statements/$statement";

        $this->response = Http::acceptJson()
            ->withToken($token)
            ->withHeaders($headers)
            ->get($url)
            ->json();

        return $this;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        if (!$this->response['resultSetMetaData']) return [];
        $headers = array_map(fn($h) => $h['name'], $this->response['resultSetMetaData']['rowType']);
        $data = [];
        foreach ($this->response['data'] as $row) {
            $data[] = array_combine($headers, $row);
        }

        return $data;
    }

    /**
     * @return Collection
     */
    public function toCollection(): Collection
    {
        return collect($this->toArray());
    }
}
