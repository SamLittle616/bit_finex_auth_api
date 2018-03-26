<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 3/11/2018
 * Time: 12:29 PM
 */

namespace App\Http\Controllers\BitFinexAuthApi;

/**
 * Bitfinex exchange New Order PHP sample API request
 *
 * @package     App\Http\Controllers\BitFinexAuthApi
 * @author      Boris Borisov (djslinger77@gmail.com)
 * @license     https://github.com/dacoders77/bit_finex_auth_api/blob/master/LICENSE
 * @version     0.1.1
 * @link        https://github.com/mariodian/bitfinex-api-php
 */
class BitFnx
{
    public $pay;
    public $sig;
    const API_URL = 'https://api.bitfinex.com';

    public $apiKey; // "YcGqIAMGDgTVESLBDTTGQ32Q9DTsL0u.....";
    private $apiSecret; // "H4G2JdRGvsJ0JOKb1GcnDvoC27oVJvN5OU4hz.....";
    private $apiVersion = "v1";

    /**
     * Request Prepare
     *
     * @param string $restAuthEndpoint   Authentication end point. https://bitfinex.readme.io/v2/docs/rest-auth
     * @param decimal $volume            Order volume
     * @param string $direction          Order direction
     * @return array                     Json server response associative array
     */

    public function requestPrepare(string $restAuthEndpoint, float $volume, string $direction) {

        // Assign key values
        $this->apiKey = $_ENV['BIT_FINEX_PUBLIC_API_KEY']; // Api keys go here
        $this->apiSecret = $_ENV['BIT_FINEX_PRIVATE_API_KEY']; // Add them to the .env file or in "***"

        // This call method can take two parameters
        // No params are taken. Only the first value is sent
        $request = $this->endPoint($restAuthEndpoint);

        $data = array(
            'request' => $request, // Request params MUST go here. New order: https://bitfinex.readme.io/v1/reference#rest-auth-new-order
            'symbol'=> 'ETHUSD', // ETHUSD ETCUSD
            'amount' => number_format($volume,2), //$volume, // 0.02
            'price' => '1000',
            'exchange' => 'bitfinex',
            'side' => $direction,
            // Exchange market - exchanger order. Market - margin order. If you need to open a short position, use 'market'. It is impossible to go short using 'exchange order'
            // The funds must be located in Margin wallet if you go short and long.
            'type' => 'market'
        );

        return $this->sendAuthRequest($data);
    }

    /**
     * The end point, api version, and parameters
     * @param string $method        The get or post method can be used in this case. Only the post method is used for authentication and points. Get is used for public ones
     * @param string $params          In this example, no parameters are provided for input
     * @return string               Return the string in api version v1/м2 with method parameters
     */
    private function endPoint(string $method, string $params = NULL) {
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
     * Add data to the header for authentication purposes
     * @param array $data       Set key/value pairs according to the correspondent api and point
     * @return array
     */
    private function prepareHeader(array $data)
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
     * Send signed HTTP request
     * @param $data
     * @return array
     */
    private function sendAuthRequest(array $data)
    {
        $headers = $this->prepareHeader($data);
        return $headers;
    }

}
