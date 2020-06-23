<?php
/**
 * Created by PhpStorm.
 * User: Tomeu
 * Date: 11/17/2015
 * Time: 7:14 PM
 */

namespace hotelbeds\hotel_api_sdk\messages;

use hotelbeds\hotel_api_sdk\types\ApiUri;

/**
 * Class BookingDetailRQ
 * @package hotelbeds\hotel_api_sdk\messages
 */
class BookingDetailRQ extends ApiRequest
{
    /**
     * BookingDetailRQ constructor.
     *
     * @param ApiUri $baseUri Base URI of service
     * @param string $bookingReference
     */
    public function __construct(ApiUri $baseUri, $bookingReference)
    {
        parent::__construct($baseUri, self::BOOKING . '/' . $bookingReference);
    }

    protected function getMethod(): string
    {
        return 'GET';
    }
}
