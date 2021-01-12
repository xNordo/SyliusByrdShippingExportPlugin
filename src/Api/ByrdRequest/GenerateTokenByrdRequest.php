<?php

/*
 * This file was created by the developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest;

use Symfony\Component\HttpFoundation\Request;

final class GenerateTokenByrdRequest extends AbstractByrdRequest implements GenerateTokenByrdRequestInterface
{
    /** @var string */
    protected $requestMethod = Request::METHOD_POST;

    /** @var string */
    protected $requestUrl = "/login";

    /** @var string|null */
    private $username;

    /** @var string|null */
    private $password;

    public function setCredentials(string $username, string $password): void
    {
        $this->username = $username;
        $this->password = $password;
    }

    public function buildRequest(?string $authorizationToken): array
    {
        $body = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        return $this->buildRequestFromParams($body);
    }
}
