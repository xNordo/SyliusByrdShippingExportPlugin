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

use BitBag\SyliusByrdShippingExportPlugin\Api\Exception\AuthorizationIssueException;
use BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest\CreateShipmentByrdRequest;
use BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest\GenerateTokenByrdRequest;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Response;

final class ByrdHttpClient implements ByrdHttpClientInterface
{
    /** @var GenerateTokenByrdRequest */
    private $generateTokenRequest;

    /** @var CreateShipmentByrdRequest */
    private $createShipmentRequest;

    public function __construct(
        GenerateTokenByrdRequest $generateTokenRequest,
        CreateShipmentByrdRequest $createShipmentRequest
    ) {
        $this->generateTokenRequest = $generateTokenRequest;
        $this->createShipmentRequest = $createShipmentRequest;
    }

    public function createShipment(
        OrderInterface $order,
        ShippingGatewayInterface $shippingGateway
    ): void {
        $token = $this->receiveAuthorizationToken($shippingGateway);

        $this->createShipmentRequest->setOrder($order);
        $this->createShipmentRequest->setShippingGateway($shippingGateway);
        $response = $this->createShipmentRequest->sendAuthorized($token);

        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            throw new \InvalidArgumentException("Something went wrong: ".$response->getContent());
        }
    }

    private function receiveAuthorizationToken(ShippingGatewayInterface $shippingGateway): string
    {
        $gatewayConfig = $shippingGateway->getConfig();
        $this->generateTokenRequest->setCredentials(
            $gatewayConfig['api_key'] ?? "",
            $gatewayConfig['api_secret'] ?? ""
        );

        $response = $this->generateTokenRequest->send();

        if ($response->getStatusCode() != Response::HTTP_CREATED) {
            throw new AuthorizationIssueException('Authorization issue');
        }

        $content = json_decode($response->getContent());
        if (!$content->token) {
            throw new \Exception('No token received from server');
        }

        return $content->token;
    }
}
