<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusByrdShippingExportPlugin\Api\Model;

use BitBag\SyliusByrdShippingExportPlugin\Api\Model\ByrdProduct;
use PhpSpec\ObjectBehavior;

class ByrdProductSpec extends ObjectBehavior
{
    function let(): void
    {
        $this->beConstructedWith("12", "ProductName", "ProductDescription");
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(ByrdProduct::class);
    }

    function it_returns_identifier(): void
    {
        $this->getId()->shouldReturn("12");
    }

    function it_returns_product_name(): void
    {
        $this->getName()->shouldReturn("ProductName");
    }

    function it_returns_product_description(): void
    {
        $this->getDescription()->shouldReturn("ProductDescription");
    }

    function it_returns_nullable_product_description(): void
    {
        $this->beConstructedWith("12", "ProductName", null);

        $this->getDescription()->shouldReturn(null);
    }

}
