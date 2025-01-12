<?php

namespace App\Services\Gosend;

use Illuminate\Support\Facades\Http;

/**
 * This will suppress all the PMD warnings in
 * this class.
 *
 */
class GosendService
{
    protected $clientId;
    protected $passKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->clientId = config('gosend.client_id');
        $this->passKey = config('gosend.pass_key');
        $this->baseUrl = config('gosend.base_url');
    }

    public function getResponse($response)
    {
        $responseCode = $response->getStatusCode();

        if ($response && in_array($responseCode, [200, 204, 201])) {
            return [
                'code' => $responseCode,
                'data' => json_decode($response->getBody(), true),
                'message' => 'success'
            ];
        }

        return $this->responseError($responseCode, $response);
    }

    private function responseError($responseCode, $response)
    {
        $responseBody = json_decode($response->getBody());

        if (in_array($responseCode, [400, 422]) && $responseBody && $responseBody->errors) {
            $errors = $responseBody->errors;
            $message = is_array($errors) ? implode('-', $errors) : $errors;
            return [
                'code' => $responseCode,
                'data' => null,
                'message' => $message
            ];
        }

        $headers = $response->getHeaders();

        if (in_array($responseCode, [406, 422]) && array_key_exists("error-message", $headers)) {
            $errorMessages = $headers['error-message'];
            $message = is_array($errorMessages) ? implode('-', $errorMessages) : $errorMessages;
            return [
                'code' => $responseCode,
                'data' => null,
                'message' => $message
            ];
        }

        if ($responseCode == 401) {
            return [
                'code' => $responseCode,
                'data' => null,
                'message' =>  '401 Unauthorized Gosend',
            ];
        }

        return false;
    }

    public function get($endpoint, $data = [])
    {
        $url = $this->buildEndpoint($endpoint);
        $response = $this->sendRequest($url, 'GET', $data);

        return $this->getResponse($response);
    }

    public function post($endpoint, $data = [])
    {
        $url = $this->buildEndpoint($endpoint);
        $response = $this->sendRequest($url, 'POST', $data);
        return $this->getResponse($response);
    }

    public function put($endpoint, $data = [])
    {
        $url = $this->buildEndpoint($endpoint);

        $response = $this->sendRequest($url, 'PUT', $data);
        return $this->getResponse($response);
    }

    protected function getHeaders()
    {
        return [
            'Client-ID' => $this->clientId,
            'Pass-Key' => $this->passKey,
            'Accept' => 'application/json'
        ];
    }

    protected function buildEndpoint($endpoint)
    {
        return $this->baseUrl . $endpoint;
    }

    protected function sendRequest($url, $method = 'GET', $data = [])
    {
        $headers = $this->getHeaders();
        $response = Http::withHeaders($headers)->{$method}($url, $method != 'GET' ? $data : null);
        return $response;
    }

    public function getShippingCost($origin, $destination)
    {
        return $this->get(
            '/calculate/price?origin=' . $origin . '&destination=' . $destination . '&paymentType=0'
        );
    }

    public function getTrackingHistory($orderNo)
    {
        return $this->get(
            '/booking/orderno/' . $orderNo
        );
    }

    public function cancel($orderNo)
    {
        return $this->put(
            '/booking/cancel',
            [
                "orderNo" => $orderNo
            ]
        );
    }

    public function booking($params)
    {
        return $this->post(
            '/booking',
            $params

        );
    }
}
