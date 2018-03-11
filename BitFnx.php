<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 3/11/2018
 * Time: 12:29 PM
 */

namespace App\Http\Controllers\BitFinexAuthApi;


class BitFnx
{
    public $pay;
    public $sig;
    const API_URL = 'https://api.bitfinex.com';

    public $apiKey; // "YcGqIAMGDgTVESLBDTTGQ32Q9DTsL0u.....";
    private $apiSecret; // "H4G2JdRGvsJ0JOKb1GcnDvoC27oVJvN5OU4hz.....";
    private $apiVersion = "v1";

    public function requestPrepare($summary, $volume, $direction) {

        // Assign key values
        $this->apiKey = $_ENV['BIT_FINEX_PUBLIC_API_KEY']; // Api keys go here
        $this->apiSecret = $_ENV['BIT_FINEX_PRIVATE_API_KEY']; // Add them to .env file or in "***"

        // This method call can take two parameters
        // No params taken. Only first value is sent
        $request = $this->Endpoint($summary);

        $data = array(
            'request' => $request, // Request params MUST go here. New order: https://bitfinex.readme.io/v1/reference#rest-auth-new-order
            'symbol'=> 'ETHUSD', // ETHUSD ETCUSD
            'amount' => $volume, // 0.02
            'price' => '1000',
            'exchange' => 'bitfinex',
            'side' => $direction,
            // exchange market - exchanger order. market - margin order. If you need to open a short position - use 'market'. It is impossible to go short with 'exchange order'
            // Funds must be located at Margin wallet if you go short and long.
            'type' => 'market'
        );

        return $this->SendAuthRequest($data);
    }

    /**
     * End point and api version
     * @param $method
     * @param null $params
     * @return string
     */
    private function Endpoint($method, $params = NULL) {
        $parameters = '';

        if ($params !== NULL) {
            $parameters = '/';
            if (is_array($params)) {
                $parameters .= implode('/', $params);
            } else {
                $parameters .= $params;
            }
        }

        return "/{$this->apiVersion}/$method$parameters";
    }

    /**
     * Add data to header for authentication purpose
     * @param $data
     * @return array
     */
    private function PrepareHeader($data)
    {
        $data['nonce'] = (string) number_format(round(microtime(true) * 1000000), 0, '.', '');

        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac('sha384', $payload, $this->apiSecret);

        $this->pay = $payload;
        $this->sig = $signature;

        return array(
            'X-BFX-APIKEY: ' . $this->apiKey,
            'X-BFX-PAYLOAD: ' . $payload,
            'X-BFX-SIGNATURE: ' . $signature
        );
    }

    /**
     * Send a signed HTTP request
     * @param $data
     * @return array
     */
    private function SendAuthRequest($data)
    {
        $headers = $this->PrepareHeader($data);
        return $headers;
    }

}
