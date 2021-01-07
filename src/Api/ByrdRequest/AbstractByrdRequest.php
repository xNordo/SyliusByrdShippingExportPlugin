<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

abstract class AbstractByrdRequest
{
    /** @var string */
    protected $requestMethod;

    /** @var string */
    protected $requestUrl;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $apiUrl;

    /** @var string|null */
    protected $token;

    public function __construct(
        HttpClientInterface $httpClient,
        string $apiUrl
    ) {
        $this->apiUrl = $apiUrl;
        $this->httpClient = $httpClient;

        $this->requestUrl = $this->createUrl($this->requestUrl);
    }

    abstract public function buildRequest(): array;

    public function send(): ResponseInterface
    {
        $request = $this->buildRequest();

        return $this->httpClient->request(
            $this->requestMethod,
            $this->requestUrl,
            $request
        );
    }

    public function sendAuthorized(string $token): ResponseInterface
    {
        $this->token = $token;

        $request = $this->buildRequest();

        return $this->httpClient->request(
            $this->requestMethod,
            $this->requestUrl,
            $this->addAuthorizationToken($request, $token)
        );
    }

    protected function addAuthorizationToken(
        array $request = null,
        ?string $token = null
    ): array {
        if ($token !== null) {
            $request['headers']['Authorization'] = 'Bearer '.$token;
        }

        return $request;
    }

    protected function buildRequestFromParams(
        ?array $parameters = null
    ): array {
        $request = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ];

        if (!empty($parameters)) {
            $request['body'] = json_encode($parameters);
        }

        return $request;
    }

    protected function createUrl(string $suffix): string
    {
        return $this->apiUrl.$suffix;
    }
}
