<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Form\Customer;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Gui\Communication\Form\Type\Select2ComboBoxType;
use Spryker\Zed\Kernel\Communication\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @method \Spryker\Zed\ManualOrderEntryGui\Communication\ManualOrderEntryGuiCommunicationFactory getFactory()
 * @method \Spryker\Zed\ManualOrderEntryGui\ManualOrderEntryGuiConfig getConfig()
 */
class CustomersListType extends AbstractType
{
    /**
     * @var string
     */
    public const TYPE_NAME = 'customers';

    /**
     * @var string
     */
    public const FIELD_CUSTOMER = 'idCustomer';

    /**
     * @var string
     */
    public const OPTION_CUSTOMER_ARRAY = 'option-category-array';

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array<string, mixed> $options
     *
     * @return void
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this->addCustomerIdField(
            $builder,
            $options[static::OPTION_CUSTOMER_ARRAY],
        );
    }

    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     *
     * @return void
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(static::OPTION_CUSTOMER_ARRAY);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $customerList
     *
     * @return $this
     */
    protected function addCustomerIdField(FormBuilderInterface $builder, array $customerList)
    {
        $builder->add(static::FIELD_CUSTOMER, Select2ComboBoxType::class, [
            'property_path' => QuoteTransfer::CUSTOMER . '.' . CustomerTransfer::ID_CUSTOMER,
            'label' => 'Select Customer',
            'choices' => array_flip($customerList),
            'multiple' => false,
            'required' => true,
        ]);

        return $this;
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return static::TYPE_NAME;
    }
}
