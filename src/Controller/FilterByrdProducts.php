<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Controller;

use BitBag\SyliusByrdShippingExportPlugin\Api\Client\ByrdHttpClientInterface;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
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
        /** @var ShippingGatewayInterface|null $gateway */
        $gateway = $this->shippingGatewayRepository->findOneByCode('byrd');
        if ($gateway === null) {
            return new Response('[]');
        }

        $products = $this->byrdHttpClient->filterProductsBySku($request->query->get('sku'), $gateway);
        return new JsonResponse($products);
    }
}
