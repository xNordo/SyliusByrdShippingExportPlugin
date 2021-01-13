<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusByrdShippingExportPlugin\Twig;

use BitBag\SyliusByrdShippingExportPlugin\Twig\ByrdExtension;
use BitBag\SyliusShippingExportPlugin\Entity\ShippingGatewayInterface;
use BitBag\SyliusShippingExportPlugin\Repository\ShippingGatewayRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Twig\Extension\AbstractExtension;

class ByrdExtensionSpec extends ObjectBehavior
{
    function let(
        ShippingGatewayRepositoryInterface $shippingGatewayRepository
    ): void {
        $this->beConstructedWith(
            $shippingGatewayRepository
        );
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ByrdExtension::class);
        $this->shouldHaveType(AbstractExtension::class);
    }

    function it_returns_one_function(): void
    {
        $this->getFunctions()->shouldHaveCount(1);
        $this->getFunctions()->shouldBeArray();
    }

    function it_returns_automapping_state_false_when_no_gateway_found(
        ShippingGatewayRepositoryInterface $shippingGatewayRepository
    ): void {
        $shippingGatewayRepository->findOneByCode('byrd')->willReturn(null);

        $this->isAutoMappingState()->shouldReturn(false);
    }

    function it_returns_automapping_state_false_when_no_key_set_in_configuration(
        ShippingGatewayRepositoryInterface $shippingGatewayRepository,
        ShippingGatewayInterface $shippingGateway
    ): void {
        $shippingGatewayRepository->findOneByCode('byrd')->willReturn($shippingGateway);

        $shippingGateway->getConfig()->willReturn([]);

        $this->isAutoMappingState()->shouldReturn(false);
    }

    function it_returns_automapping_state_false_when_key_set_to_false_in_configuration(
        ShippingGatewayRepositoryInterface $shippingGatewayRepository,
        ShippingGatewayInterface $shippingGateway
    ): void {
        $shippingGatewayRepository->findOneByCode('byrd')->willReturn($shippingGateway);

        $shippingGateway->getConfig()->willReturn([
            'auto_sku_matching' => false,
        ]);

        $this->isAutoMappingState()->shouldReturn(false);
    }

    function it_returns_automapping_state_true_when_key_set_to_true_in_configuration(
        ShippingGatewayRepositoryInterface $shippingGatewayRepository,
        ShippingGatewayInterface $shippingGateway
    ): void {
        $shippingGatewayRepository->findOneByCode('byrd')->willReturn($shippingGateway);

        $shippingGateway->getConfig()->willReturn([
            'auto_sku_matching' => true,
        ]);

        $this->isAutoMappingState()->shouldReturn(true);
    }
}
