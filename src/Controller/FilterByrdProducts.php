<?php

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Controller;

use BitBag\SyliusByrdShippingExportPlugin\Api\Client\ByrdHttpClientInterface;
use BitBag\SyliusShippingExportPlugin\Repository\ShippingGatewayRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class FilterByrdProducts
{
    /** @var ByrdHttpClientInterface */
    private $byrdHttpClient;

    /** @var ShippingGatewayRepositoryInterface */
    private $shippingGatewayRepository;

    public function __construct(
        ByrdHttpClientInterface $byrdHttpClient,
        ShippingGatewayRepositoryInterface $shippingGatewayRepository
    ) {
        $this->byrdHttpClient = $byrdHttpClient;
        $this->shippingGatewayRepository = $shippingGatewayRepository;
    }

    public function __invoke(Request $request): Response
    {
        $gateway = $this->shippingGatewayRepository->findOneByCode('byrd');
        if (!$gateway) {
            return new Response('[]');
        }

        $products = $this->byrdHttpClient->filterProductsBySku($request->query->get('sku'), $gateway);
        return new JsonResponse($products);
    }
}
