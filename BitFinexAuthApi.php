<?php

namespace App\Http\Controllers\BitFinexAuthApi;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;

/*
 * Sample class for www.bitfinex.com authenticated endpoints
 * Guzzle is used instead of CURL 
 * http://docs.guzzlephp.org/en/stable/quickstart.html
 *
 * A new order function has been taken as the example
 * https://bitfinex.readme.io/v1/reference#rest-auth-new-order
 *
 * Add a route to web.php: route::get('/placeOrder/{volume}/{direction}', 'BitFinexAuthApi\BitFinexAuthApi@placeOrder');
 * Controller call: http://www.yourdomain.com/public/placeOrder/0.025/sell
 *
 * The provided code is commented on in depth for further use and comprehension
 * Any questions? Feel free to contact me at djslinger77@gmail.com
 */
class BitFinexAuthApi extends Controller
{
    public function placeOrder($volume, $direction)
    {

        $bitFnx = new BitFnx(); // Created new instance of class

        /*
        * The exact location where the request is sent, REST AUTHENTICATED EndpointS, can be:
        * summary
        * account_fees
        * key_info
        * etc
        * https://docs.bitfinex.com/v1/reference#auth-key-permissions
        */
        $restAuthEndpoint = "order/new";

        // Create a new guzzle and pass $data array instance as the headers set
        // 3 values are sent: X-BFX-APIKEY, X-BFX-PAYLOAD, X-BFX-SIGNATURE
        // The requestPrepare() call fuction and $restAuthEndpoint passing to to it as a parameter
        $z = $bitFnx->requestPrepare($restAuthEndpoint, $volume, $direction);

        dump($z); // Dump $z 
        //echo "Payload: " . $bitFnx->pay . "<br>";
        //echo "Signature: " . $bitFnx->sig . "<br>";
        //echo "<br>Place order. Volume: " . $volume;

        $apiConnection = new Client([
            'base_uri' => 'https://api.bitfinex.com/v1/',
            'timeout' => 5 // If this value is made small, a fatal error occurs
        ]);

        $response = $apiConnection->request('POST', $restAuthEndpoint, [
            'headers' => [
                'X-BFX-APIKEY' => $bitFnx->apiKey,
                'X-BFX-PAYLOAD' => $bitFnx->pay,
                'X-BFX-SIGNATURE' => $bitFnx->sig
            ]
        ]);

        return $response->getBody(); // Remove the request body

    } // placeOrder

}

