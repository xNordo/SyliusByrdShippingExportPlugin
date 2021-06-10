## Installation

```bash
$ composer require bitbag/byrd-shipping-export-plugin
```

Add plugin dependencies to your `config/bundles.php` file:
```php
return [
    ...
    
    BitBag\SyliusByrdShippingExportPlugin\BitBagByrdShippingExportPlugin::class => ['all' => true],
];
```

Import required config in your `config/packages/_sylius.yaml` file:
```yaml
# config/packages/_sylius.yaml

imports:
    ...

  - { resource: "@BitBagByrdShippingExportPlugin/Resources/config/config.yaml" }
```

Import the routing in your config/routes.yaml file:
```yaml
# config/routes.yaml

imports:
    ...

  bitbag_sylius_byrd_shipping_export_plugin:
    resource: "@BitBagByrdShippingExportPlugin/Resources/config/routing.yaml"
    prefix: /admin
```

Apply migration to your database
```bash
bin/console doctrine:migrations:migrate
```

**Note:** If you are running it on production, add the `-e prod` flag to this command.
