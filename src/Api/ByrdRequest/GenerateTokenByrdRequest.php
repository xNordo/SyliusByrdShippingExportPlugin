<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Api\ByrdRequest;

use BitBag\SyliusByrdShippingExportPlugin\Api\Exception\InvalidCredentialsException;
use Symfony\Component\HttpFoundation\Request;

final class GenerateTokenByrdRequest extends AbstractByrdRequest implements GenerateTokenByrdRequestInterface
{
    /** @var string */
    protected $requestMethod = Request::METHOD_POST;

    /** @var string */
    protected $requestUrl = '/login';

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
        if ($this->username === null && $this->password === null) {
            throw new InvalidCredentialsException('You have to set up credentials via setCredentials(...) method');
        }

        $body = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        return $this->buildRequestFromParams($body);
    }
}
