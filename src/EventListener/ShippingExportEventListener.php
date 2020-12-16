<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\EventListener;

use BitBag\SyliusByrdShippingExportPlugin\Api\Client\ByrdHttpClientInterface;
use BitBag\SyliusByrdShippingExportPlugin\Api\Exception\ByrdApiException;
use BitBag\SyliusShippingExportPlugin\Event\ExportShipmentEvent;
use Doctrine\ORM\EntityManagerInterface;

final class ShippingExportEventListener
{
    /** @var ByrdHttpClientInterface */
    private $byrdHttpClient;

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ByrdHttpClientInterface $byrdHttpClient,
        EntityManagerInterface $entityManager
    ) {
        $this->byrdHttpClient = $byrdHttpClient;
        $this->entityManager = $entityManager;
    }

    public function exportShipment(ExportShipmentEvent $exportShipmentEvent): void
    {
        $shippingExport = $exportShipmentEvent->getShippingExport();
        $shipping = $shippingExport->getShipment();
        $order = $shipping->getOrder();
        $shipmentGateway = $shippingExport->getShippingGateway();

        try {
            $this->byrdHttpClient->createShipment($order, $shipmentGateway);
        } catch (ByrdApiException $e) {
            $exportShipmentEvent->getShippingExport()->setState('failed');
            $this->entityManager->flush();

            $exportShipmentEvent->addErrorFlash(
                sprintf("Byrd error for order %s: %s", $order->getNumber(), $e->getMessage())
            );
            return;
        }

        $exportShipmentEvent->addSuccessFlash();
        $exportShipmentEvent->exportShipment();
    }
}
