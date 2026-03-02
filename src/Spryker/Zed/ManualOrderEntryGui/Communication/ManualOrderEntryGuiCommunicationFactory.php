<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;
use Spryker\Zed\ManualOrderEntryGui\Communication\Controller\CreateController;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Address\AddressCollectionType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Constraint\SkuExists;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Customer\CustomersListType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Customer\CustomerType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\AddressCollectionDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\CustomerDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\CustomersListDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\ItemCollectionDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\OrderDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\OrderSourceListDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\PaymentDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\ProductCollectionDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\ShipmentDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\StoreDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\SummaryDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider\VoucherDataProvider;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Order\OrderType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\OrderSource\OrderSourceListType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Payment\PaymentType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Product\ItemCollectionType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Product\ProductCollectionType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Shipment\ShipmentType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Store\StoreType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Summary\SummaryType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Voucher\VoucherType;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\AddressFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\CustomerFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\FormHandlerInterface;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\ItemFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\OrderSourceFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\PaymentFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\ProductFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\ShipmentFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\StoreFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Handler\VoucherFormHandler;
use Spryker\Zed\ManualOrderEntryGui\Communication\Service\ManualOrderEntryFormPluginFilter;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCalculationFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCartFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCheckoutFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCurrencyFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToCustomerFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToDiscountFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToManualOrderEntryFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToMessengerFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToMoneyFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToPaymentFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToProductFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToShipmentFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToStoreFacadeInterface;
use Spryker\Zed\ManualOrderEntryGui\Dependency\QueryContainer\ManualOrderEntryGuiToCustomerQueryContainerInterface;
use Spryker\Zed\ManualOrderEntryGui\ManualOrderEntryGuiDependencyProvider;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;

/**
 * @method \Spryker\Zed\ManualOrderEntryGui\ManualOrderEntryGuiConfig getConfig()
 */
class ManualOrderEntryGuiCommunicationFactory extends AbstractCommunicationFactory
{
    public function getCustomerFacade(): ManualOrderEntryGuiToCustomerFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_CUSTOMER);
    }

    public function getProductFacade(): ManualOrderEntryGuiToProductFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_PRODUCT);
    }

    public function getCartFacade(): ManualOrderEntryGuiToCartFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_CART);
    }

    public function getDiscountFacade(): ManualOrderEntryGuiToDiscountFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_DISCOUNT);
    }

    public function getMessengerFacade(): ManualOrderEntryGuiToMessengerFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_MESSENGER);
    }

    public function getCurrencyFacade(): ManualOrderEntryGuiToCurrencyFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_CURRENCY);
    }

    public function getShipmentFacade(): ManualOrderEntryGuiToShipmentFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_SHIPMENT);
    }

    public function getMoneyFacade(): ManualOrderEntryGuiToMoneyFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_MONEY);
    }

    public function getPaymentFacade(): ManualOrderEntryGuiToPaymentFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_PAYMENT);
    }

    public function getCheckoutFacade(): ManualOrderEntryGuiToCheckoutFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_CHECKOUT);
    }

    public function getCalculationFacade(): ManualOrderEntryGuiToCalculationFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_CALCULATION);
    }

    public function getManualOrderEntryFacade(): ManualOrderEntryGuiToManualOrderEntryFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_MANUAL_ORDER_ENTRY);
    }

    public function getStoreFacade(): ManualOrderEntryGuiToStoreFacadeInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::FACADE_STORE);
    }

    public function getCustomerQueryContainer(): ManualOrderEntryGuiToCustomerQueryContainerInterface
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::QUERY_CONTAINER_CUSTOMER);
    }

    /**
     * @return array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface>
     */
    public function getManualOrderEntryFormPlugins(): array
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::PLUGINS_MANUAL_ORDER_ENTRY_FORM);
    }

    /**
     * @param array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface> $formPlugins
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface>
     */
    public function getManualOrderEntryFilteredFormPlugins($formPlugins, Request $request, QuoteTransfer $quoteTransfer): array
    {
        return $this->createManualOrderEntryFormPluginFilter()
            ->getFilteredFormPlugins($formPlugins, $request, $quoteTransfer);
    }

    /**
     * @param array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface> $formPlugins
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface>
     */
    public function getManualOrderEntrySkippedFormPlugins($formPlugins, Request $request, QuoteTransfer $quoteTransfer): array
    {
        return $this->createManualOrderEntryFormPluginFilter()
            ->getSkippedFormPlugins($formPlugins, $request, $quoteTransfer);
    }

    public function createManualOrderEntryFormPluginFilter(): ManualOrderEntryFormPluginFilter
    {
        return new ManualOrderEntryFormPluginFilter(
            CreateController::PREVIOUS_STEP_NAME,
            CreateController::NEXT_STEP_NAME,
        );
    }

    public function createCustomersListDataProvider(Request $request): CustomersListDataProvider
    {
        return new CustomersListDataProvider(
            $this->getCustomerQueryContainer(),
            $request,
        );
    }

    public function createCustomersListForm(Request $request, QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createCustomersListDataProvider($request);

        return $this->getFormFactory()->create(
            CustomersListType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createOrderSourceListDataProvider(): OrderSourceListDataProvider
    {
        return new OrderSourceListDataProvider(
            $this->getManualOrderEntryFacade(),
        );
    }

    public function createOrderSourceListForm(Request $request, QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createOrderSourceListDataProvider();

        return $this->getFormFactory()->create(
            OrderSourceListType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createCustomerDataProvider(): CustomerDataProvider
    {
        return new CustomerDataProvider();
    }

    public function createCustomerForm(CustomerDataProvider $customerFormDataProvider): FormInterface
    {
        $customerTransfer = new CustomerTransfer();

        return $this->getFormFactory()->create(
            CustomerType::class,
            $customerFormDataProvider->getData($customerTransfer),
            $customerFormDataProvider->getOptions($customerTransfer),
        );
    }

    public function createAddressCollectionDataProvider(): AddressCollectionDataProvider
    {
        return new AddressCollectionDataProvider($this->getStoreFacade());
    }

    public function createAddressCollectionForm(QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createAddressCollectionDataProvider();

        return $this->getFormFactory()->create(
            AddressCollectionType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createStoreDataProvider(): StoreDataProvider
    {
        return new StoreDataProvider(
            $this->getCurrencyFacade(),
        );
    }

    public function createStoreForm(Request $request, QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createStoreDataProvider();

        return $this->getFormFactory()->create(
            StoreType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createProductsCollectionDataProvider(): ProductCollectionDataProvider
    {
        return new ProductCollectionDataProvider();
    }

    public function createProductsCollectionForm(QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createProductsCollectionDataProvider();

        return $this->getFormFactory()->create(
            ProductCollectionType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createSkuExistsConstraint(): Constraint
    {
        return new SkuExists([
            SkuExists::OPTION_PRODUCT_FACADE => $this->getProductFacade(),
        ]);
    }

    public function createItemsCollectionDataProvider(): ItemCollectionDataProvider
    {
        return new ItemCollectionDataProvider();
    }

    public function createItemsCollectionForm(QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createItemsCollectionDataProvider();

        return $this->getFormFactory()->create(
            ItemCollectionType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createVoucherDataProvider(): VoucherDataProvider
    {
        return new VoucherDataProvider();
    }

    public function createVoucherForm(QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createVoucherDataProvider();

        return $this->getFormFactory()->create(
            VoucherType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createShipmentDataProvider(): ShipmentDataProvider
    {
        return new ShipmentDataProvider(
            $this->getShipmentFacade(),
            $this->getMoneyFacade(),
        );
    }

    public function createShipmentForm(Request $request, QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createShipmentDataProvider();

        return $this->getFormFactory()->create(
            ShipmentType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createPaymentDataProvider(): PaymentDataProvider
    {
        return new PaymentDataProvider(
            $this->getPaymentMethodSubFormPlugins(),
        );
    }

    public function createPaymentForm(Request $request, QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createPaymentDataProvider();

        return $this->getFormFactory()->create(
            PaymentType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    /**
     * @return array<\Spryker\Zed\ManualOrderEntryGuiExtension\Dependency\Plugin\PaymentSubFormPluginInterface>
     */
    public function getPaymentMethodSubFormPlugins(): array
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::PAYMENT_SUB_FORMS);
    }

    public function createSummaryDataProvider(): SummaryDataProvider
    {
        return new SummaryDataProvider(
            $this->getCalculationFacade(),
            $this->getMessengerFacade(),
        );
    }

    public function createSummaryForm(QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createSummaryDataProvider();

        return $this->getFormFactory()->create(
            SummaryType::class,
            $formDataProvider->getData($quoteTransfer),
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @return array<\Spryker\Zed\ManualOrderEntryGui\Dependency\Plugin\QuoteExpanderPluginInterface>
     */
    public function getQuoteExpanderPlugins(): array
    {
        return $this->getProvidedDependency(ManualOrderEntryGuiDependencyProvider::PLUGINS_QUOTE_EXPANDER);
    }

    public function createAddressFormHandler(): FormHandlerInterface
    {
        return new AddressFormHandler(
            $this->getCustomerFacade(),
        );
    }

    public function createCustomerFormHandler(): FormHandlerInterface
    {
        return new CustomerFormHandler(
            $this->getCustomerFacade(),
        );
    }

    public function createPaymentFormHandler(): FormHandlerInterface
    {
        return new PaymentFormHandler(
            $this->getPaymentFacade(),
            $this->getPaymentMethodSubFormPlugins(),
        );
    }

    public function createProductFormHandler(): FormHandlerInterface
    {
        return new ProductFormHandler(
            $this->getCartFacade(),
            $this->getProductFacade(),
        );
    }

    public function createItemFormHandler(): FormHandlerInterface
    {
        return new ItemFormHandler(
            $this->getCartFacade(),
            $this->getMessengerFacade(),
        );
    }

    public function createShipmentFormHandler(): FormHandlerInterface
    {
        return new ShipmentFormHandler(
            $this->getShipmentFacade(),
        );
    }

    public function createStoreFormHandler(): FormHandlerInterface
    {
        return new StoreFormHandler(
            $this->getCurrencyFacade(),
        );
    }

    public function createVoucherFormHandler(): FormHandlerInterface
    {
        return new VoucherFormHandler(
            $this->getCartFacade(),
        );
    }

    public function createOrderSourceFormHandler(): FormHandlerInterface
    {
        return new OrderSourceFormHandler(
            $this->getManualOrderEntryFacade(),
        );
    }

    public function createOrderForm(QuoteTransfer $quoteTransfer): FormInterface
    {
        $formDataProvider = $this->createOrderDataProvider();

        return $this->getFormFactory()->create(
            OrderType::class,
            $formDataProvider->getOptions($quoteTransfer),
        );
    }

    public function createOrderDataProvider(): OrderDataProvider
    {
        return new OrderDataProvider();
    }
}
