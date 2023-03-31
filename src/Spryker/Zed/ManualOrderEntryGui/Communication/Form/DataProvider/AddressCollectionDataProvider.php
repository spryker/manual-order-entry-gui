<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Address\AddressCollectionType;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToStoreFacadeInterface;

class AddressCollectionDataProvider implements FormDataProviderInterface
{
    /**
     * @var \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToStoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @var \Generated\Shared\Transfer\CustomerTransfer
     */
    protected $customerTransfer;

    /**
     * @param \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToStoreFacadeInterface $storeFacade
     */
    public function __construct(ManualOrderEntryGuiToStoreFacadeInterface $storeFacade)
    {
        $this->storeFacade = $storeFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $transfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function getData($transfer): QuoteTransfer
    {
        $this->customerTransfer = $transfer->getCustomer();

        $transfer->setShippingAddress($this->getShippingAddress($transfer));
        $transfer->setBillingAddress($this->getBillingAddress($transfer));

        if ($transfer->getBillingAddress()->toArray() == $transfer->getShippingAddress()->toArray()) {
            $transfer->setBillingSameAsShipping(true);
        }

        return $transfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $transfer
     *
     * @return array<string, mixed>
     */
    public function getOptions($transfer): array
    {
        return [
            'data_class' => QuoteTransfer::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
            AddressCollectionType::OPTION_ADDRESS_CHOICES => $this->getAddressChoices(),
            AddressCollectionType::OPTION_COUNTRY_CHOICES => $this->getAvailableCountries(),
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    protected function getShippingAddress(QuoteTransfer $quoteTransfer): AddressTransfer
    {
        $shippingAddressTransfer = new AddressTransfer();
        if ($quoteTransfer->getShippingAddress() !== null) {
            $shippingAddressTransfer = $quoteTransfer->getShippingAddress();
        }

        if ($this->customerTransfer !== null && $quoteTransfer->getShippingAddress() === null) {
            /** @var int $idCustomerAddress */
            $idCustomerAddress = $this->customerTransfer->getDefaultShippingAddress();
            $shippingAddressTransfer->setIdCustomerAddress($idCustomerAddress);
        }

        return $shippingAddressTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    protected function getBillingAddress(QuoteTransfer $quoteTransfer): AddressTransfer
    {
        $billingAddressTransfer = new AddressTransfer();
        if ($quoteTransfer->getBillingAddress() !== null) {
            $billingAddressTransfer = $quoteTransfer->getBillingAddress();
        }

        if ($this->customerTransfer !== null && $quoteTransfer->getBillingAddress() === null) {
            /** @var int $idCustomerAddress */
            $idCustomerAddress = $this->customerTransfer->getDefaultBillingAddress();
            $billingAddressTransfer->setIdCustomerAddress($idCustomerAddress);
        }

        return $billingAddressTransfer;
    }

    /**
     * @return array
     */
    protected function getAddressChoices(): array
    {
        if ($this->customerTransfer === null) {
            return [];
        }

        $customerAddressesTransfer = $this->customerTransfer->getAddresses();

        if ($customerAddressesTransfer === null) {
            return [];
        }

        $choices = [];
        foreach ($customerAddressesTransfer->getAddresses() as $address) {
            $choices[$address->getIdCustomerAddress()] = sprintf(
                '%s %s %s, %s %s, %s %s',
                $address->getSalutation(),
                $address->getFirstName(),
                $address->getLastName(),
                $address->getAddress1(),
                $address->getAddress2(),
                $address->getZipCode(),
                $address->getCity(),
            );
        }

        return $choices;
    }

    /**
     * @return array
     */
    protected function getAvailableCountries(): array
    {
        $countries = [];

        foreach ($this->storeFacade->getAllStores() as $storeTransfer) {
            foreach ($storeTransfer->getCountries() as $iso2Code) {
                $countries[$iso2Code] = $iso2Code;
            }
        }

        return $countries;
    }
}
