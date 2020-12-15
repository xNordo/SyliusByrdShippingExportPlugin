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
use BitBag\SyliusShippingExportPlugin\Event\ExportShipmentEvent;

final class ShippingExportEventListener
{
    /** @var ByrdHttpClientInterface */
    private $byrdHttpClient;

    public function __construct(ByrdHttpClientInterface $byrdHttpClient)
    {
        $this->byrdHttpClient = $byrdHttpClient;
    }

    public function exportShipment(ExportShipmentEvent $exportShipmentEvent): void
    {
        $shippingExport = $exportShipmentEvent->getShippingExport();
        $shipping = $shippingExport->getShipment();
        $order = $shipping->getOrder();
        $shipmentGateway = $shippingExport->getShippingGateway();

        try {
            $this->byrdHttpClient->createShipment($order, $shipmentGateway);
        } catch (\Exception $e) {
            $exportShipmentEvent->addErrorFlash(
                sprintf("Byrd error for order %s: %s", $order->getNumber(), $e->getMessage())
            );
            return;
        }

        $exportShipmentEvent->addSuccessFlash();
        $exportShipmentEvent->exportShipment();
    }
}
