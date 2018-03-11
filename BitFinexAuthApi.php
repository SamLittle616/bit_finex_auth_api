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
    public function Placeorder($volume, $direction)
    {

        $bitFnx = new BitFnx(); // Created new instance of class

        /*
        * Where exactly the request is sent. REST AUTHENTICATED EndpointS
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
        $z = $bitFnx->requestPrepare($restAuthEndpoint, $volume, $direction);

        dump($z); // Dump $z 
        //echo "Payload: " . $bitFnx->pay . "<br>";
        //echo "Signature: " . $bitFnx->sig . "<br>";
        //echo "<br>Place order. Volume: " . $volume;

        $apiConnection = new Client([
            'base_uri' => 'https://api.bitfinex.com/v1/',
            'timeout' => 5 // If make this value small - fatal error occurs
        ]);

        $response = $apiConnection->request('POST', $restAuthEndpoint, [
            'headers' => [
                'X-BFX-APIKEY' => $bitFnx->apiKey,
                'X-BFX-PAYLOAD' => $bitFnx->pay,
                'X-BFX-SIGNATURE' => $bitFnx->sig
            ]
        ]);

        $body = $response->getBody(); // Get the body out of the request
        return $body;

    } // placeorder

}

