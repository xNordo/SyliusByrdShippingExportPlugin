Usage
----

Customization
----
##### You can [decorate](https://symfony.com/doc/current/service_container/service_decoration.html) available services and [extend](https://symfony.com/doc/current/form/create_form_type_extension.html) current forms.

Run the below command to see what Symfony services are shared with this plugin:

```bash
bin/console debug:container | grep bitbag_sylius_byrd_shipping_export_plugin
```

### Parameters you can override in your parameters.yml(.dist) file
```bash
bin/console debug:container --parameters | grep grep bitbag_sylius_byrd_shipping_export_plugin
```
