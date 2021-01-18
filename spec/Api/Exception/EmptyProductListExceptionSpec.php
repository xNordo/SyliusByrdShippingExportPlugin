<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusByrdShippingExportPlugin\Api\Exception;

use BitBag\SyliusByrdShippingExportPlugin\Api\Exception\ByrdApiException;
use BitBag\SyliusByrdShippingExportPlugin\Api\Exception\EmptyProductListException;
use PhpSpec\ObjectBehavior;

class EmptyProductListExceptionSpec extends ObjectBehavior
{
    function it_is_initializable(): void
    {
        $this->shouldHaveType(EmptyProductListException::class);
        $this->shouldHaveType(ByrdApiException::class);
    }
}
