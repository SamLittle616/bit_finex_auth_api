<?php

namespace App\Http\Controllers\BitFinexAuthApi;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

/*
 * Sample class for authenticated endpoins of www.bitfinex.com
 * Guzzle is used instead of CURL 
 * http://docs.guzzlephp.org/en/stable/quickstart.html
 *
 * New order function was taken as the example
 * https://bitfinex.readme.io/v1/reference#rest-auth-new-order
 *
 * Add a route to web.php: route::get('/placeorder/{volume}/{direction}', 'BitFinexAuthApi\BitFinexAuthApi@placeorder');
 * The call the controller: http://www.yourdomain.com/public/placeorder/0.025/sell
 *
 * The provided code is well commented for further use and understanding
 * Got a question? Feel free to buzz me at djslinger77@gmail.com
 */
class BitFinexAuthApi extends Controller
{
    public function placeorder($volume, $direction)
    {

        $bit_fnx = new BitFnx(); // Created new instance of class

        /*
        * Where exactly the request is sent. REST AUTHENTICATED ENDPOINTS
        * It can be:
        * summary
        * account_fees
        * key_info
        * etc
        * https://docs.bitfinex.com/v1/reference#auth-key-permissions
        */
        $restAuthEndpoint = "order/new";

        // Create new instance of guzzle and pass $data array as the set of headers
        // 3 values are sent: X-BFX-APIKEY, X-BFX-PAYLOAD, X-BFX-SIGNATURE
        // Function requestPrepare() call and passing $restAuthEndpoint to it as a parameter
        $z = $bit_fnx->requestPrepare($restAuthEndpoint, $volume, $direction);

        //print_r ($z); // Trace output
        //echo "Payload: " . $bit_fnx->pay . "<br>";
        //echo "Signature: " . $bit_fnx->sig . "<br>";
        //echo "<br>Place order. Volume: " . $volume;

        $api_connection = new Client([
            'base_uri' => 'https://api.bitfinex.com/v1/',
            'timeout' => 5 // If make this value small - fatal error occurs
        ]);

        $response = $api_connection->request('POST', $restAuthEndpoint, [
            'headers' => [
                'X-BFX-APIKEY' => $bit_fnx->api_key,
                'X-BFX-PAYLOAD' => $bit_fnx->pay,
                'X-BFX-SIGNATURE' => $bit_fnx->sig
            ]
        ]);

        $body = $response->getBody(); // Get the body out of the request
        return $body;

    } // placeorder

}

class BitFnx
{
    public $pay;
    public $sig;
    const API_URL = 'https://api.bitfinex.com';

    public $api_key; // "YcGqIAMGDgTVESLBDTTGQ32Q9DTsL0u.....";
    private $api_secret; // "H4G2JdRGvsJ0JOKb1GcnDvoC27oVJvN5OU4hz.....";
    private $api_version = "v1";
    
    public function requestPrepare($summary, $volume, $direction) {

        // Assign key values
        $this->api_key = $_ENV['BIT_FINEX_PUBLIC_API_KEY']; // Api keys go here
        $this->api_secret = $_ENV['BIT_FINEX_PRIVATE_API_KEY']; // Add them to .env file or in "***"

        // This method call can take two parameters
        // No params taken. Only first value is sent
        $request = $this->endpoint($summary);

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

        return $this->send_auth_request($data);
    }

    /**
     * End point and api version
     * @param $method
     * @param null $params
     * @return string
     */
    private function endpoint($method, $params = NULL) {
        $parameters = '';

        if ($params !== NULL) {
            $parameters = '/';
            if (is_array($params)) {
                $parameters .= implode('/', $params);
            } else {
                $parameters .= $params;
            }
        }

        return "/{$this->api_version}/$method$parameters";
    }

    /**
     * Add data to header for authentication purpose
     * @param $data
     * @return array
     */
    private function prepare_header($data)
    {
        $data['nonce'] = (string) number_format(round(microtime(true) * 1000000), 0, '.', '');

        $payload = base64_encode(json_encode($data));
        $signature = hash_hmac('sha384', $payload, $this->api_secret);

        $this->pay = $payload;
        $this->sig = $signature;

        return array(
            'X-BFX-APIKEY: ' . $this->api_key,
            'X-BFX-PAYLOAD: ' . $payload,
            'X-BFX-SIGNATURE: ' . $signature
        );
    }

    /**
     * Send a signed HTTP request
     * @param $data
     * @return array
     */
    private function send_auth_request($data)
    {
        $headers = $this->prepare_header($data);
        return $headers;
    }

}

function num_to_string($num) {
    return number_format($num, 2, '.', '');
}