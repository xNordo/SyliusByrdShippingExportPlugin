<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Api;

use BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest\AbstractByrdRequest;
use BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest\AbstractByrdRequestInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface RequestSenderInterface
{
    public function send(AbstractByrdRequestInterface $byrdRequest): ResponseInterface;

    public function sendAuthorized(AbstractByrdRequestInterface $byrdRequest, string $token): ResponseInterface;
}
