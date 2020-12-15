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
use Symfony\Component\HttpFoundation\Response;
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

        $requestBody = $this->constructNewShipmentRequest($shippingGateway, $order);
        $request = $this->buildRequest($requestBody, $token);

        $response = $this->httpClient->request(
            Request::METHOD_POST, $this->createUrl('/shipments'), $request
        );

        if ($response->getStatusCode() !== Response::HTTP_CREATED) {
            throw new \InvalidArgumentException("Something went wrong: ".$response->getContent());
        }
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

    private function buildRequest(
        array $parameters,
        ?string $token = null
    ): array {
        $request = [
            'body' => json_encode($parameters),
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ];

        if ($token !== null) {
            $request['headers']['Authorization'] = 'Bearer '.$token;
        }

        return $request;
    }

    private function constructNewShipmentRequest(
        ShippingGatewayInterface $shippingGateway,
        OrderInterface $order
    ): array {
        $request = $this->constructNewShippingRequestBase($shippingGateway, $order);

        $request['shipmentItems'] = $this->createShipmentItemsRequest($order);
        $request['destinationAddress'] = $this->createDestinationAddressRequest($order);

        return $request;
    }

    private function constructNewShippingRequestBase(
        ShippingGatewayInterface $shippingGateway,
        OrderInterface $order
    ): array {
        $customer = $order->getCustomer();
        $gatewayConfig = $shippingGateway->getConfig();

        return [
            "destinationName" => $customer->getFullName(),
            "destinationPhone" => $customer->getPhoneNumber(),
            "destinationEmail" => $customer->getEmailCanonical(),
            "destinationCompany" => $order->getBillingAddress()->getCompany(),
            "description" => $order->getNotes(),
            "fragile" => false,
            "option" => $gatewayConfig['send_option'],
            "status" => "new",
        ];
    }

    private function createShipmentItemsRequest(
        OrderInterface $order
    ): array {
        $shipmentItems = [];

        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            if (!$product->hasMatchedByrdProduct()) {
                continue;
            }

            $shipmentItem = [
                "lotNumber" => null,
                "amount" => $item->getQuantity(),
                "byrdProductID" => $byrdProduct->getByrdProductId(),
                "description" => $byrdProduct->getDescription(),
                "productName" => $byrdProduct->getName(),
                "sku" => $byrdProduct->getSku(),
                "price" => $item->getUnitPrice(),
            ];

            $shipmentItems[] = $shipmentItem;
        }

        return $shipmentItems;
    }

    private function createDestinationAddressRequest(OrderInterface $order): array
    {
        $shippingAddress = $order->getShippingAddress();

        return [
            "countryCode" => $shippingAddress->getCountryCode(),
            "locality" => $shippingAddress->getCity(),
            "postalCode" => $shippingAddress->getPostcode(),
            "thoroughfare" => $shippingAddress->getStreet(),
        ];
    }
}
