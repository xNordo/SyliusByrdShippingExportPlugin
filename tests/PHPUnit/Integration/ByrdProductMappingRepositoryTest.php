<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace Tests\BitBag\SyliusByrdShippingExportPlugin\Integration;

use BitBag\SyliusByrdShippingExportPlugin\Repository\ByrdProductMappingRepositoryInterface;
use Sylius\Component\Core\Repository\ProductRepositoryInterface;

final class ByrdProductMappingRepository extends IntegrationTestCase
{
    /** @var ProductRepositoryInterface */
    private $productRepository = null;

    /** @var ByrdProductMappingRepositoryInterface */
    private $byrdProductMappingRepository = null;

    public function SetUp(): void
    {
        parent::SetUp();

        $this->productRepository = self::$container->get('sylius.repository.product');
        $this->byrdProductMappingRepository = self::$container->get('bitbag.byrd_shipping_export_plugin.repository.byrd_product_mapping');
    }

    public function test_mapping_for_product_was_found(): void
    {
        $this->loadFixturesFromFiles(['test_mapping_for_product_was_found.yaml']);

        $product = $this->productRepository->findOneByCode('RANDOM_JACKET_CODE');
        $this->assertNotNull($product);

        $mapping = $this->byrdProductMappingRepository->findForProduct($product);
        $this->assertNotNull($mapping);
    }

    public function test_mapping_for_product_was_not_found(): void
    {
        $this->loadFixturesFromFiles(['test_mapping_for_product_was_not_found.yaml']);

        $product = $this->productRepository->findOneByCode('RANDOM_JACKET_CODE');
        $this->assertNotNull($product);

        $mapping = $this->byrdProductMappingRepository->findForProduct($product);
        $this->assertNull($mapping);
    }
}
