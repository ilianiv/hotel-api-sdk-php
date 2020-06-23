<?php
/**
 * Created by PhpStorm.
 * User: Tomeu
 * Date: 12/22/2015
 * Time: 1:25 AM
 */

namespace hotelbeds\hotel_api_sdk\messages;

use hotelbeds\hotel_api_sdk\types\ApiUri;

/**
 * Class BookingCancellationRQ
 * @package hotelbeds\hotel_api_sdk\messages
 */
class BookingCancellationRQ extends ApiRequest
{
    /**
     * BookingCancellationRQ constructor.
     * @param ApiUri $baseUri
     * @param string $bookingId
     */
    public function __construct(ApiUri $baseUri, $bookingId)
    {
        $baseUri = $baseUri->withPath($baseUri->getPath() . "/" . self::BOOKING . "/$bookingId");

        parent::__construct($baseUri, self::BOOKING);
    }

    protected function getMethod(): string
    {
        return 'DELETE';
    }
}
