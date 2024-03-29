<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Form\DataProvider;

use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\ShipmentMethodsTransfer;
use Generated\Shared\Transfer\ShipmentMethodTransfer;
use Generated\Shared\Transfer\ShipmentTransfer;
use Spryker\Zed\ManualOrderEntryGui\Communication\Form\Shipment\ShipmentType;

class ShipmentDataProvider implements FormDataProviderInterface
{
    /**
     * @var \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToShipmentFacadeInterface
     */
    protected $shipmentFacade;

    /**
     * @var \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToMoneyFacadeInterface
     */
    protected $moneyFacade;

    /**
     * @param \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToShipmentFacadeInterface $shipmentFacade
     * @param \Spryker\Zed\ManualOrderEntryGui\Dependency\Facade\ManualOrderEntryGuiToMoneyFacadeInterface $moneyFacade
     */
    public function __construct($shipmentFacade, $moneyFacade)
    {
        $this->shipmentFacade = $shipmentFacade;
        $this->moneyFacade = $moneyFacade;
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
            ShipmentType::OPTION_SHIPMENT_METHODS_ARRAY => $this->getShipmentMethodList($transfer),
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $transfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function getData($transfer): QuoteTransfer
    {
        if ($transfer->getShipment() === null) {
            $transfer->setShipment(new ShipmentTransfer());
        }
        if ($transfer->getShipment()->getMethod() === null) {
            $transfer->getShipment()->setMethod(new ShipmentMethodTransfer());
        }

        return $transfer;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array
     */
    protected function getShipmentMethodList(QuoteTransfer $quoteTransfer): array
    {
        if (!$quoteTransfer->getStore() || !$quoteTransfer->getCurrency()) {
            return [];
        }

        $shipmentMethodList = [];
        $shipmentMethodsTransfer = $this->resolveShipmentMethods($quoteTransfer);
        foreach ($shipmentMethodsTransfer->getMethods() as $shipmentMethodTransfer) {
            $moneyTransfer = $this->moneyFacade->fromInteger($shipmentMethodTransfer->getStoreCurrencyPrice(), $shipmentMethodTransfer->getCurrencyIsoCode());

            $row = $shipmentMethodTransfer->getCarrierName()
                . ' - '
                . $shipmentMethodTransfer->getName()
                . ': '
                . $this->moneyFacade->formatWithSymbol($moneyTransfer);

            $shipmentMethodList[$shipmentMethodTransfer->getIdShipmentMethod()] = $row;
        }

        return $shipmentMethodList;
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\ShipmentMethodsTransfer
     */
    protected function resolveShipmentMethods(QuoteTransfer $quoteTransfer): ShipmentMethodsTransfer
    {
        $this->setItemLevelEmptyShipmentFromQuote($quoteTransfer);

        $shipmentMethodsCollectionTransfer = $this->shipmentFacade->getAvailableMethodsByShipment($quoteTransfer);
        /** @var \Generated\Shared\Transfer\ShipmentMethodsTransfer $shipmentMethodsTransfer */
        $shipmentMethodsTransfer = $shipmentMethodsCollectionTransfer->getShipmentMethods()->getIterator()->current();

        return $shipmentMethodsTransfer;
    }

    /**
     * @deprecated Exists for Backward Compatibility reasons only.
     *
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    protected function setItemLevelEmptyShipmentFromQuote(QuoteTransfer $quoteTransfer): QuoteTransfer
    {
        $quoteShipmentTransfer = $quoteTransfer->getShipment();
        if ($quoteShipmentTransfer === null) {
            return $quoteTransfer;
        }

        $quoteShipmentTransfer->setShippingAddress($quoteTransfer->getShippingAddress());

        foreach ($quoteTransfer->getItems() as $itemTransfer) {
            if ($itemTransfer->getShipment() === null) {
                $itemTransfer->setShipment($quoteShipmentTransfer);
            }
        }

        return $quoteTransfer;
    }
}
