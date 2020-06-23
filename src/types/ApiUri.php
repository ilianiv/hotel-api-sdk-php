<?php
/**
 * Created by PhpStorm.
 * User: Tomeu
 * Date: 10/27/2015
 * Time: 3:15 AM
 */

namespace hotelbeds\hotel_api_sdk\types;

use GuzzleHttp\Psr7\Uri;

/**
 * Class ApiUri
 * @package hotelbeds\hotel_api_sdk\types
 */
class ApiUri extends Uri
{
    const BASE_PATH='/hotel-api';
    const API_URI_FORMAT = '{basepath}/{version}';

    /**
     * @param ApiVersion $version
     * @return ApiUri
     */
    public function prepare(ApiVersion $version)
    {
        return $this->withPath(
            str_replace(['{basepath}', '{version}'], [static::BASE_PATH, $version->getVersion()], static::API_URI_FORMAT)
        );
    }
}
