<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusByrdShippingExportPlugin\Api\Factory;

use BitBag\SyliusByrdShippingExportPlugin\Api\Factory\ByrdModelFactory;
use BitBag\SyliusByrdShippingExportPlugin\Api\Model\ByrdProduct;
use PhpSpec\ObjectBehavior;

class ByrdModelFactorySpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(ByrdModelFactory::class);
    }

    function it_creates_model(): void
    {
        $this->create("10", "name", "description")
            ->shouldReturnAnInstanceOf(ByrdProduct::class);
    }

    function it_creates_model_with_nullable_description(): void
    {
        $this->create("10", "name", null)
            ->shouldReturnAnInstanceOf(ByrdProduct::class);
    }

}
