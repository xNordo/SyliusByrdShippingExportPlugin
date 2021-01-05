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
use BitBag\SyliusShippingExportPlugin\Entity\ShippingExportInterface;
use BitBag\SyliusShippingExportPlugin\Event\ExportShipmentEvent;
use BitBag\SyliusShippingExportPlugin\Repository\ShippingExportRepositoryInterface;
use BitBag\SyliusShippingExportPlugin\Repository\ShippingGatewayRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\PaymentInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ShippingExportEventListener
{
    /** @var ByrdHttpClientInterface */
    private $byrdHttpClient;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ShippingExportRepositoryInterface */
    private $shippingExportRepository;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var Filesystem */
    private $filesystem;

    /** @var TranslatorInterface */
    private $translator;

    /** @var string */
    private $shippingLabelsPath;

    /** @var ShippingGatewayRepositoryInterface */
    private $shippingGatewayRepository;

    public function __construct(
        ByrdHttpClientInterface $byrdHttpClient,
        EntityManagerInterface $entityManager,
        ShippingExportRepositoryInterface $shippingExportRepository,
        FlashBagInterface $flashBag,
        Filesystem $filesystem,
        TranslatorInterface $translator,
        ShippingGatewayRepositoryInterface $shippingGatewayRepository,
        string $shippingLabelsPath
    ) {
        $this->byrdHttpClient = $byrdHttpClient;
        $this->entityManager = $entityManager;
        $this->shippingExportRepository = $shippingExportRepository;
        $this->flashBag = $flashBag;
        $this->filesystem = $filesystem;
        $this->translator = $translator;
        $this->shippingLabelsPath = $shippingLabelsPath;
        $this->shippingGatewayRepository = $shippingGatewayRepository;
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

    public function autoExport(PaymentInterface $payment): void
    {
        $byrdGateway = $this->shippingGatewayRepository->findOneByCode('byrd');
        if (!$byrdGateway) {
            return;
        }

        $config = $byrdGateway->getConfig();
        if (!isset($config['auto_export']) || !$config['auto_export']) {
            return;
        }

        $order = $payment->getOrder();
        $shipment = $order->getShipments()->first();

        /** @var ShippingExportInterface $exportObject */
        $exportObject = $this->shippingExportRepository->findOneBy([
            'shipment' => $shipment->getId(),
        ]);

        if (!$exportObject) {
            return;
        }

        $event = new ExportShipmentEvent(
            $exportObject,
            $this->flashBag,
            $this->entityManager,
            $this->filesystem,
            $this->translator,
            $this->shippingLabelsPath
        );

        $this->exportShipment($event);
    }
}
