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

use BitBag\SyliusByrdShippingExportPlugin\Api\Factory\ByrdModelFactoryInterface;
use BitBag\SyliusByrdShippingExportPlugin\Api\Model\ByrdProduct;
use BitBag\SyliusByrdShippingExportPlugin\Entity\ByrdProductMappingInterface;
use BitBag\SyliusByrdShippingExportPlugin\Repository\ByrdProductMappingRepositoryInterface;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ByrdHttpClient implements ByrdHttpClientInterface
{
    /** @var HttpClientInterface */
    private $httpClient;

    /** @var ByrdProductMappingRepositoryInterface */
    private $byrdProductMappingRepository;

    /** @var ByrdModelFactoryInterface */
    private $byrdModelFactory;

    /** @var string */
    private $apiUrl;

    /** @var string */
    private $token;

    public function __construct(
        HttpClientInterface $httpClient,
        ByrdProductMappingRepositoryInterface $byrdProductMappingRepository,
        ByrdModelFactoryInterface $byrdModelFactory,
        string $apiUrl
    ) {
        $this->httpClient = $httpClient;
        $this->byrdProductMappingRepository = $byrdProductMappingRepository;
        $this->byrdModelFactory = $byrdModelFactory;
        $this->apiUrl = $apiUrl;
    }

    public function createShipment(
        OrderInterface $order,
        ShippingGatewayInterface $shippingGateway
    ): void {
        $this->token = $this->receiveAuthorizationToken($shippingGateway);

        $requestBody = $this->constructNewShipmentRequest($order);
        $request = $this->buildRequest($requestBody, $this->token);

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
        ?array $parameters = null,
        ?string $token = null
    ): array {
        $request = [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ];

        if ($parameters !== null) {
            $request['body'] = json_encode($parameters);
        }

        if ($token !== null) {
            $request['headers']['Authorization'] = 'Bearer '.$token;
        }

        return $request;
    }

    private function constructNewShipmentRequest(
        OrderInterface $order
    ): array {
        $request = $this->constructNewShippingRequestBase($order);

        $request['shipmentItems'] = $this->createShipmentItemsRequest($order);
        $request['destinationAddress'] = $this->createDestinationAddressRequest($order);

        return $request;
    }

    private function constructNewShippingRequestBase(
        OrderInterface $order
    ): array {
        $customer = $order->getCustomer();
        $shippingAddress = $order->getShippingAddress();

        return [
            "destinationName" => $shippingAddress->getFullName(),
            "destinationPhone" => $shippingAddress->getPhoneNumber(),
            "destinationEmail" => $customer->getEmailCanonical(),
            "destinationCompany" => $shippingAddress->getCompany(),
            "description" => $order->getNotes(),
            "fragile" => false,
            "option" => 'standard',
            "status" => "new",
        ];
    }

    private function createShipmentItemsRequest(
        OrderInterface $order
    ): array {
        $shipmentItems = [];

        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();

            /** @var ByrdProductMappingInterface|null $byrdMapping */
            $byrdMapping = $this->byrdProductMappingRepository->findOneByProduct($product);
            if (!$byrdMapping) {
                continue;
            }

            $shipmentItems[] = $this->createShipmentItem(
                $byrdMapping->getByrdProductSku(),
                $item->getQuantity()
            );
        }

        return $shipmentItems;
    }

    private function createShipmentItem(
        string $byrdProductSku,
        int $quantity
    ): array {
        $byrdProduct = $this->fetchByrdProductInformation($byrdProductSku);

        return [
            "amount" => $quantity,
            "byrdProductID" => $byrdProduct->getId(),
            "description" => $byrdProduct->getDescription(),
            "productName" => $byrdProduct->getName(),
            "sku" => $byrdProductSku,
        ];
    }

    private function fetchByrdProductInformation(string $byrdProductSku): ByrdProduct
    {
        $response = $this->httpClient->request(
            Request::METHOD_GET,
            $this->createUrl('/warehouse/products?sku='.$byrdProductSku),
            $this->buildRequest(null, $this->token)
        );

        $content = json_decode($response->getContent());
        $product = current($content->data);

        return $this->byrdModelFactory->create(
            $product->id,
            $product->name,
            $product->description
        );
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
