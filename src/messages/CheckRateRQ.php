<?php
/**
 * Created by PhpStorm.
 * User: Tomeu
 * Date: 11/24/2015
 * Time: 1:39 AM
 */

namespace hotelbeds\hotel_api_sdk\messages;

use hotelbeds\hotel_api_sdk\helpers\CheckRate;
use hotelbeds\hotel_api_sdk\types\ApiUri;

class CheckRateRQ extends ApiRequest
{
    public function __construct(ApiUri $baseUri, CheckRate $checkDataRQ)
    {
        parent::__construct($baseUri, self::CHECK_AVAIL);

        $this->setDataRequest($checkDataRQ);
    }

    protected function getMethod(): string
    {
        return 'POST';
    }
}
