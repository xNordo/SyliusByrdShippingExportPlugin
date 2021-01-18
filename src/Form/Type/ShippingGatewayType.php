<?php

/*
 * This file has been created by developers from BitBag.
 * Feel free to contact us once you face any issues or want to start
 * another great project.
 * You can find more information about us on https://bitbag.shop and write us
 * an email on mikolaj.krol@bitbag.pl.
 */

declare(strict_types=1);

namespace BitBag\SyliusByrdShippingExportPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

final class ShippingGatewayType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('api_key', TextType::class, [
                'label' => 'bitbag_sylius_byrd_shipping_export_plugin.ui.api_key',
            ])
            ->add('api_secret', TextType::class, [
                'label' => 'bitbag_sylius_byrd_shipping_export_plugin.ui.api_secret',
            ])
            ->add('shipping_option', ChoiceType::class, [
                'label' => 'bitbag_sylius_byrd_shipping_export_plugin.ui.shipping_option',
                'required' => true,
                'choices' => [
                    'bitbag_sylius_byrd_shipping_export_plugin.ui.shipping_option_type.standard' => 'standard',
                    'bitbag_sylius_byrd_shipping_export_plugin.ui.shipping_option_type.express' => 'express',
                    'bitbag_sylius_byrd_shipping_export_plugin.ui.shipping_option_type.economy' => 'economy',
                ],
                'multiple' => false,
            ])
            ->add('auto_sku_matching', CheckboxType::class, [
                'label' => 'bitbag_sylius_byrd_shipping_export_plugin.ui.auto_sku_matching',
                'required' => false,
            ])
            ->add('auto_export', CheckboxType::class, [
                'label' => 'bitbag_sylius_byrd_shipping_export_plugin.ui.auto_export',
                'required' => false,
            ])
        ;
    }
}
