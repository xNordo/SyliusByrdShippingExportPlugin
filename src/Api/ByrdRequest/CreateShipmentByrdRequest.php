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

use BitBag\SyliusByrdShippingExportPlugin\Api\Exception\EmptyProductListException;
use BitBag\SyliusByrdShippingExportPlugin\Api\Factory\ByrdModelFactoryInterface;
use BitBag\SyliusByrdShippingExportPlugin\Api\Model\ByrdProduct;
use BitBag\SyliusByrdShippingExportPlugin\Entity\ByrdProductMappingInterface;
use BitBag\SyliusByrdShippingExportPlugin\Repository\ByrdProductMappingRepositoryInterface;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class CreateShipmentByrdRequest extends AbstractByrdRequest
{
    /** @var ByrdProductMappingRepositoryInterface */
    private $byrdProductMappingRepository;

    /** @var FindProductByrdRequest */
    private $findProductRequest;

    /** @var ByrdModelFactoryInterface */
    private $byrdModelFactory;

    /** @var string */
    protected $requestMethod = Request::METHOD_POST;

    /** @var string */
    protected $requestUrl = "/shipments";

    /** @var OrderInterface|null */
    private $order;

    /** @var ShippingGatewayInterface|null $shippingGateway */
    private $shippingGateway;

    public function __construct(
        HttpClientInterface $httpClient,
        ByrdProductMappingRepositoryInterface $byrdProductMappingRepository,
        FindProductByrdRequest $findProductRequest,
        ByrdModelFactoryInterface $byrdModelFactory,
        string $apiUrl
    ) {
        parent::__construct($httpClient, $apiUrl);

        $this->byrdProductMappingRepository = $byrdProductMappingRepository;
        $this->findProductRequest = $findProductRequest;
        $this->byrdModelFactory = $byrdModelFactory;
    }

    public function setOrder(
        OrderInterface $order
    ): void {
        $this->order = $order;
    }

    public function setShippingGateway(
        ShippingGatewayInterface $shippingGateway
    ): void {
        $this->shippingGateway = $shippingGateway;
    }

    public function buildRequest(): array
    {
        $request = $this->constructNewShippingRequestBase($this->order);

        $request['shipmentItems'] = $this->createShipmentItemsRequest($this->order);
        if (empty($request['shipmentItems'])) {
            throw new EmptyProductListException('Cannot sent request with no product');
        }

        $request['destinationAddress'] = $this->createDestinationAddressRequest($this->order);

        return $this->buildRequestFromParams($request);
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

            $sku = $product->getCode();
            if (!$this->autoMatchBySku()) {
                /** @var ByrdProductMappingInterface|null $byrdMapping */
                $byrdMapping = $this->byrdProductMappingRepository->findOneByProduct($product);
                if (!$byrdMapping) {
                    continue;
                }

                $sku = $byrdMapping->getByrdProductSku();
            }

            $shipmentItems[] = $this->createShipmentItem(
                $sku,
                $item->getQuantity()
            );
        }

        return $shipmentItems;
    }

    private function autoMatchBySku(): bool
    {
        if (!$this->shippingGateway) {
            return true;
        }

        $config = $this->shippingGateway->getConfig();
        return isset($config['auto_sku_matching']) && $config['auto_sku_matching'] === true;
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
        $this->findProductRequest->setByrdProductSku($byrdProductSku);
        $response = $this->findProductRequest->sendAuthorized($this->token);

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
