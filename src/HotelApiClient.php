<?php
/**
 * #%L
 * hotel-api-sdk
 * %%
 * Copyright (C) 2015 HOTELBEDS, S.L.U.
 * %%
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 2.1 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Lesser Public License for more details.
 *
 * You should have received a copy of the GNU General Lesser Public
 * License along with this program.  If not, see
 * <http://www.gnu.org/licenses/lgpl-2.1.html>.
 * #L%
 */

namespace hotelbeds\hotel_api_sdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Utils;
use hotelbeds\hotel_api_sdk\helpers\Availability;
use hotelbeds\hotel_api_sdk\helpers\Booking;
use hotelbeds\hotel_api_sdk\helpers\BookingList;
use hotelbeds\hotel_api_sdk\helpers\CheckRate;
use hotelbeds\hotel_api_sdk\messages\ApiRequest;
use hotelbeds\hotel_api_sdk\messages\AvailabilityRS;
use hotelbeds\hotel_api_sdk\messages\BookingCancellationRS;
use hotelbeds\hotel_api_sdk\messages\BookingConfirmRS;
use hotelbeds\hotel_api_sdk\messages\BookingDetailRS;
use hotelbeds\hotel_api_sdk\messages\BookingListRS;
use hotelbeds\hotel_api_sdk\messages\CheckRateRS;
use hotelbeds\hotel_api_sdk\messages\StatusRS;
use hotelbeds\hotel_api_sdk\model\AuditData;
use hotelbeds\hotel_api_sdk\types\ApiUri;
use hotelbeds\hotel_api_sdk\types\ApiVersion;
use hotelbeds\hotel_api_sdk\types\HotelSDKException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HotelApiClient. This is the main class of the SDK that makes client-api hotel. Mainly this class is used to make all calls to the hotel-api webservice using ApiHelper classes
 * @package hotelbeds\hotel_api_sdk
 * @method StatusRS Status() Get status of hotel-api service
 * @method RequestInterface StatusRaw() Get status of hotel-api service
 * @method StatusRS StatusParse(ResponseInterface $data) Get status of hotel-api service
 *
 * @method AvailabilityRS Availability(Availability $availData) Do availability accommodation request
 * @method RequestInterface AvailabilityRaw(Availability $availData) Do availability accommodation request
 * @method AvailabilityRS AvailabilityParse(ResponseInterface $data) Do availability accommodation request
 *
 * @method CheckRateRS CheckRate(CheckRate $rateData) Check different room rates for booking
 * @method RequestInterface CheckRateRaw(CheckRate $rateData) Check different room rates for booking
 * @method CheckRateRS CheckRateParse(ResponseInterface $data) Check different room rates for booking
 *
 * @method BookingConfirmRS BookingConfirm(Booking $bookingData) Method allows confirmation of the rate keys selected.  There is an option of confirming more than one rate key for the same hotel/room/board.
 * @method RequestInterface BookingConfirmRaw(Booking $bookingData) Method allows confirmation of the rate keys selected.  There is an option of confirming more than one rate key for the same hotel/room/board.
 * @method BookingConfirmRS BookingConfirmParse(ResponseInterface $data) Method allows confirmation of the rate keys selected.  There is an option of confirming more than one rate key for the same hotel/room/board.
 *
 * @method BookingCancellationRS BookingCancellation($bookingId) Method can cancel confirmed booking
 * @method RequestInterface BookingCancellationRaw($bookingId) Method can cancel confirmed booking
 * @method BookingCancellationRS BookingCancellationParse(ResponseInterface $data) Method can cancel confirmed booking
 *
 * @method BookingListRS BookingList(BookingList $bookData) To get a list of bookings
 * @method RequestInterface BookingListRaw(BookingList $bookData) To get a list of bookings
 * @method BookingListRS BookingListParse(ResponseInterface $data) To get a list of bookings
 *
 * @method BookingDetailRS BookingDetail($bookingReference) To retrieve a booking with all its details
 * @method RequestInterface BookingDetailRaw($bookingReference) To retrieve a booking with all its details
 * @method BookingDetailRS BookingDetailParse(ResponseInterface $data) To retrieve a booking with all its details
 */
class HotelApiClient
{
    /**
     * @var ApiUri Well formatted URI of service
     */
    private $apiUri;

    /**
     * @var ApiUri Well formatted URI of service for payments
     */
    private $apiPaymentUri;

    /**
     * @var string Stores locally client api key
     */
    private $apiKey;

    /**
     * @var string Stores locally client shared secret
     */
    private $sharedSecret;

    /**
     * @var Client HTTPClient object
     */
    private $httpClient;

    /**
     * @var Request Last sent request
     */
    private $lastRequest;

    /**
     * @var Response Last sent request
     */
    private $lastResponse;

    /**
     * HotelApiClient Constructor they initialize SDK Client.
     * @param string $url Base URL of hotel-api service.
     * @param string $apiKey Client APIKey
     * @param string $sharedSecret Shared secret
     * @param ApiVersion $version Version of HotelAPI Interface
     * @param int $timeout HTTP Client timeout
     * @param string $adapter Customize adapter for http request
     * @param string $secureUrl Customize Base URL of hotel-api secure service.
     */
    public function __construct($url, $apiKey, $sharedSecret, ApiVersion $version, $timeout = 30, $adapter = null, $secureUrl = null)
    {
        $this->lastRequest  = null;
        $this->apiKey       = trim($apiKey);
        $this->sharedSecret = trim($sharedSecret);
        $this->httpClient   = new Client([
            "timeout" => $timeout
        ]);

        $this->apiUri        = (new ApiUri($url))->prepare($version);
        $this->apiPaymentUri = (new ApiUri($secureUrl ? $secureUrl : $url))->prepare($version);
    }

    /**
     * @param $sdkMethod string Method request name.
     * @param $args array only specify a ApiHelper class type for encapsulate request arguments
     * @return RequestInterface|ApiResponse Class of response. Each call type returns response class: For example AvailabilityRQ returns AvailabilityRS
     * @throws HotelSDKException Specific exception of call
     */

    public function __call($sdkMethod, array $args = null)
    {
        $raw_request = $parse_response = false;

        if (substr($sdkMethod, -3) == 'Raw') {
            $sdkMethod   = substr($sdkMethod, 0, -3);
            $raw_request = true;
        } elseif (substr($sdkMethod, -5) == 'Parse') {
            $sdkMethod      = substr($sdkMethod, 0, -5);
            $parse_response = true;
        }

        // Parse response mode
        if ($parse_response) {
            return $this->makeSdkClassRS($sdkMethod, $this->parseResponse($args[0]));
        }
        
        $req = $this->makeSdkClassRQ($sdkMethod, $args);

        // Raw request mode
        if ($raw_request) {
            return $this->buildRequest($req);
        }        

        // Default behaviour
        $data = $this->callApi($req);

        return $this->makeSdkClassRS($sdkMethod, $data);
    }

    protected function makeSdkClassRS($sdkMethod, array $data)
    {
        $sdkClassRS = "hotelbeds\\hotel_api_sdk\\messages\\" . $sdkMethod . "RS";

        if (!class_exists($sdkClassRS)) {
            throw new HotelSDKException("$sdkClassRS not implemented in SDK");
        }

        return new $sdkClassRS($data);
    }

    protected function makeSdkClassRQ($sdkMethod, array $args = null)
    {
        $sdkClassRQ = "hotelbeds\\hotel_api_sdk\\messages\\" . $sdkMethod . "RQ";

        if (!class_exists($sdkClassRQ)) {
            throw new HotelSDKException("$sdkClassRQ not implemented in SDK");
        }

        if ($sdkClassRQ == "hotelbeds\\hotel_api_sdk\\messages\\BookingConfirmRQ") {
            return new $sdkClassRQ($this->apiUri, $this->apiPaymentUri, $args[0]);
        } else {
            if ($args !== null && count($args) > 0) {
                return new $sdkClassRQ($this->apiUri, $args[0]);
            } else {
                return new $sdkClassRQ($this->apiUri);
            }
        }
    }

    public function buildRequest(ApiRequest $request)
    {
        $signature         = hash("sha256", $this->apiKey . $this->sharedSecret . time());
        $this->lastRequest = $request->prepare($this->apiKey, $signature);

        return $this->lastRequest;
    }

    public function parseResponse(ResponseInterface $response)
    {
        $this->lastResponse = $response;

        if ($response->getStatusCode() !== 200) {
            $auditData     = null;
            $message       = '';
            $errorResponse = null;
            $contents      = $response->getBody()->getContents();

            if ($contents !== '') {
                try {
                    $errorResponse = json_decode($contents, true);
                    $auditData     = new AuditData($errorResponse["auditData"]);
                    $message       = $errorResponse["error"]["code"] . ' ' . $errorResponse["error"]["message"];
                } catch (\Exception $e) {
                    throw new HotelSDKException($response->getReasonPhrase() . ': ' . $response->getBody(), null, $e->getCode());
                }
            }
            throw new HotelSDKException($response->getReasonPhrase() . ': ' . $message, $auditData, $response->getStatusCode());
        }

        return Utils::jsonDecode($response->getBody()->getContents(), true);
    }

    /**
     * @return Request getLastRequest Returns entire raw request
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * @return Response getLastResponse Returns entire raw response
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Generic API Call, this is a internal used method for sending all requests to webservice and parse
     * JSON response and transforms to PHP-Array object.
     * @param ApiRequest $request API Abstract request helper for construct request
     * @return array Response data into PHP Array structure
     * @throws HotelSDKException Calling exception, can capture remote server auditdata if exists.
     */
    private function callApi(ApiRequest $request)
    {
        try {
            $response = $this->httpClient->send($this->buildRequest($request));
        } catch (RequestException $e) {
            throw new HotelSDKException("Error accessing API: " . $e->getResponse()->getBody(), null, $e->getCode());
        } catch (\Exception $e) {
            throw new HotelSDKException("Error accessing API: " . $e->getMessage(), null, $e->getCode());
        }

        return $this->parseResponse($response);
    }
}
