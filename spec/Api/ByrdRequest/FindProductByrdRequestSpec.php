<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest;

use BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest\FindProductByrdRequest;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FindProductByrdRequestSpec extends ObjectBehavior
{
    function let(HttpClientInterface $httpClient): void
    {
        $this->beConstructedWith("http://byrd-api-fake-url");
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(FindProductByrdRequest::class);
    }

    function it_returns_get_request_method(): void
    {
        $this->getRequestMethod()->shouldReturn("GET");
    }

    function it_returns_request_url(): void
    {
        $this->getRequestUrl()->shouldReturn("http://byrd-api-fake-url/warehouse/products");
    }

    function it_builds_request_without_using_authorization_token(): void
    {
        $this->buildRequest("authorization-token")->shouldReturn(["headers" => [
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ]]);
    }

    function it_accepts_nullable_authorization_token(): void
    {
        $this->buildRequest(null)->shouldReturn(["headers" => [
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ]]);
    }
}
