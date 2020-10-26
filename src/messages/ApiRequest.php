<?php
/**
 * Created by PhpStorm.
 * User: Tomeu
 * Date: 10/27/2015
 * Time: 3:13 AM
 */

namespace hotelbeds\hotel_api_sdk\messages;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use hotelbeds\hotel_api_sdk\helpers\ApiHelper;
use hotelbeds\hotel_api_sdk\types\ApiUri;

/**
 * Class ApiRequest This is abstract request class define how prepare final HTTP Request
 * @package hotelbeds\hotel_api_sdk\messages
 */
abstract class ApiRequest implements ApiCallTypes
{
    const DEF_HEADERS = [
        'Accept'          => 'application/json',
        'Accept-Charset'  => 'utf-8',
        'Accept-Encoding' => 'gzip, deflate',
        'User-Agent'      => 'hotel-api-sdk-php'
    ];
    /**
     * @var Request Contains a http request
     */
    protected $request;

    /**
     * @var ApiUri Contains final URL with endpoint and extra parameters if is needed
     */
    protected $baseUri;

    /**
     * @var ApiHelper
     */
    private $dataRQ;

    /**
     * ApiRequest constructor.
     * @param ApiUri $baseUri Base URI of service
     * @param string $operation Endpoint name of operation
     */
    public function __construct(ApiUri $baseUri, $operation)
    {
        $this->baseUri = $baseUri->withPath($baseUri->getPath() . "/" . $operation);
        $this->request = new Request($this->getMethod(), $this->baseUri, static::DEF_HEADERS);
    }

    /**
     * @param ApiHelper $dataRQ Set data request to request
     */
    protected function setDataRequest(ApiHelper $dataRQ)
    {
        $this->dataRQ = $dataRQ;
    }

    /**
     * @param string $apiKey API Key of client
     * @param string $signature Computed signature for made this call
     * @return Request Return well constructed HTTP Request
     */
    public function prepare($apiKey, $signature)
    {
        if (empty($apiKey) || empty($signature))
            throw new \InvalidArgumentException("HotelApiClient cannot be created without specifying an API key and signature");

        $this->request = $this->request
            ->withHeader('Api-Key', $apiKey)
            ->withHeader('X-Signature', $signature);

        if (!empty($this->dataRQ)) {
            switch ($this->request->getMethod()) {
                case 'GET':
                    $this->request = $this->request->withUri(
                        $this->request->getUri()->withQuery(
                            http_build_query($this->dataRQ->toArray(), null, '&', PHP_QUERY_RFC3986)
                        )
                    );
                    break;
                case 'POST':
                    $this->request = $this->request
                        ->withHeader('Content-Type', 'application/json')
                        ->withBody(Utils::streamFor($this->dataRQ->__toString()));
            }
        }

        return $this->request;
    }

    abstract protected function getMethod(): string;
}
