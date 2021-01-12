<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusByrdShippingExportPlugin\Entity;

use BitBag\SyliusByrdShippingExportPlugin\Entity\ByrdProductMapping;
use PhpSpec\ObjectBehavior;
use Sylius\Component\Core\Model\ProductInterface;

class ByrdProductMappingSpec extends ObjectBehavior
{
    function let(ProductInterface $product): void
    {
        $this->setProduct($product);
        $this->setByrdProductSku('SKU-123');
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ByrdProductMapping::class);
    }

    function it_returns_product(ProductInterface $product): void
    {
        $this->getProduct()->shouldReturn($product);
    }

    function it_returns_byrd_sku(): void
    {
        $this->getByrdProductSku()->shouldReturn('SKU-123');
    }
}
