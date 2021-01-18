<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace spec\BitBag\SyliusByrdShippingExportPlugin\Api;

use BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest\GenerateTokenByrdRequestInterface;
use BitBag\SyliusByrdShippingExportPlugin\Api\RequestSender;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RequestSenderSpec extends ObjectBehavior
{
    function let(HttpClientInterface $httpClient): void
    {
        $this->beConstructedWith($httpClient);
    }

    function it_is_initializable(): void
    {
        $this->shouldHaveType(RequestSender::class);
    }

    function it_sends_request(
        GenerateTokenByrdRequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ): void {
        $request->getRequestMethod()->willReturn('POST');
        $request->getRequestUrl()->willReturn('//request-url');

        $request->buildRequest(null)->willReturn([
            'somedata' => false,
        ]);

        $httpClient->request("POST", "//request-url", [
            'somedata' => false,
        ])->willReturn($response);

        $this->send($request)->shouldReturn($response);
    }

    function it_sends_authorized_request(
        GenerateTokenByrdRequestInterface $request,
        ResponseInterface $response,
        HttpClientInterface $httpClient
    ): void {
        $request->getRequestMethod()->willReturn('POST');
        $request->getRequestUrl()->willReturn('//request-url');

        $request->buildRequest("authorization-token")->willReturn([
            'somedata' => false,
        ]);

        $httpClient->request("POST", "//request-url", [
            'somedata' => false,
            'headers' => [
                'Authorization' => 'Bearer authorization-token'
            ],
        ])->willReturn($response);

        $this->sendAuthorized($request, "authorization-token")->shouldReturn($response);
    }
}
