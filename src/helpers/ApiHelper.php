<?php
/**
 * Created by PhpStorm.
 * User: Tomeu
 * Date: 11/4/2015
 * Time: 7:27 PM
 */

namespace hotelbeds\hotel_api_sdk\helpers;

use GuzzleHttp\Utils;
use hotelbeds\hotel_api_sdk\generic\DataContainer;

abstract class ApiHelper extends DataContainer
{
    public function __toString()
    {
        return Utils::jsonEncode($this->toArray());
    }
}
