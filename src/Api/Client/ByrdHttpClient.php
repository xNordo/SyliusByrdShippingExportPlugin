<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Api\Client;

use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ByrdHttpClient implements ByrdHttpClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var string */
    private $apiUrl;

    public function __construct(
        HttpClientInterface $httpClient,
        string $apiUrl
    ) {
        $this->httpClient = $httpClient;
        $this->apiUrl = $apiUrl;
    }

    public function createShipment(
        OrderInterface $order,
        ShippingGatewayInterface $shippingGateway
    ): void {
        $token = $this->receiveAuthorizationToken($shippingGateway);

        $this->createProduct($token);
    }

    private function receiveAuthorizationToken(ShippingGatewayInterface $shippingGateway): string
    {
        $gatewayConfig = $shippingGateway->getConfig();

        $url = $this->createUrl('/login');
        $body = $this->buildRequest([
            'username' => $gatewayConfig['api_key'],
            'password' => $gatewayConfig['api_secret'],
        ]);

        $response = $this->httpClient->request(Request::METHOD_POST, $url, $body);
        $content = json_decode($response->getContent());

        if (!$content->token) {
            throw new \Exception('No token received from server');
        }

        return $content->token;
    }

    private function createUrl(string $suffix): string
    {
        return $this->apiUrl.$suffix;
    }

    private function buildRequest(array $parameters): array
    {
        return [
            'body' => json_encode($parameters),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ];
    }
}
