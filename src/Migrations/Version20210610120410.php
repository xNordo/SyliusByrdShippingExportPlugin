<?php

declare(strict_types=1);

namespace Sylius\Bundle\AdminApiBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20210610120410 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE bitbag_byrd_product_mapping (id INT AUTO_INCREMENT NOT NULL, product_id INT DEFAULT NULL, byrd_product_sku VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_4786BBBF2DE8F118 (byrd_product_sku), UNIQUE INDEX UNIQ_4786BBBF4584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bitbag_shipping_export (id INT AUTO_INCREMENT NOT NULL, shipment_id INT DEFAULT NULL, shipping_gateway_id INT DEFAULT NULL, exported_at DATETIME DEFAULT NULL, label_path VARCHAR(255) DEFAULT NULL, state VARCHAR(255) NOT NULL, external_id VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_20E62D9F7BE036FC (shipment_id), INDEX IDX_20E62D9FEF84DE5E (shipping_gateway_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bitbag_shipping_gateway (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(255) NOT NULL, config JSON NOT NULL COMMENT \'(DC2Type:json_array)\', name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE bitbag_shipping_gateway_method (shipping_gateway_id INT NOT NULL, shipping_method_id INT NOT NULL, INDEX IDX_8606B9CBEF84DE5E (shipping_gateway_id), INDEX IDX_8606B9CB5F7D6850 (shipping_method_id), PRIMARY KEY(shipping_gateway_id, shipping_method_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE bitbag_byrd_product_mapping ADD CONSTRAINT FK_4786BBBF4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id)');
        $this->addSql('ALTER TABLE bitbag_shipping_export ADD CONSTRAINT FK_20E62D9F7BE036FC FOREIGN KEY (shipment_id) REFERENCES sylius_shipment (id)');
        $this->addSql('ALTER TABLE bitbag_shipping_export ADD CONSTRAINT FK_20E62D9FEF84DE5E FOREIGN KEY (shipping_gateway_id) REFERENCES bitbag_shipping_gateway (id)');
        $this->addSql('ALTER TABLE bitbag_shipping_gateway_method ADD CONSTRAINT FK_8606B9CBEF84DE5E FOREIGN KEY (shipping_gateway_id) REFERENCES bitbag_shipping_gateway (id)');
        $this->addSql('ALTER TABLE bitbag_shipping_gateway_method ADD CONSTRAINT FK_8606B9CB5F7D6850 FOREIGN KEY (shipping_method_id) REFERENCES sylius_shipping_method (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE bitbag_shipping_export DROP FOREIGN KEY FK_20E62D9FEF84DE5E');
        $this->addSql('ALTER TABLE bitbag_shipping_gateway_method DROP FOREIGN KEY FK_8606B9CBEF84DE5E');
        $this->addSql('DROP TABLE bitbag_byrd_product_mapping');
        $this->addSql('DROP TABLE bitbag_shipping_export');
        $this->addSql('DROP TABLE bitbag_shipping_gateway');
        $this->addSql('DROP TABLE bitbag_shipping_gateway_method');
    }
}
