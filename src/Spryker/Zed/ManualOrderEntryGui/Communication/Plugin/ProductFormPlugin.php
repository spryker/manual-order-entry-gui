<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Plugin;

use Generated\Shared\Transfer\CartChangeTransfer;
use Generated\Shared\Transfer\ItemTransfer;
use Spryker\Zed\Cart\Business\CartFacade;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method \Spryker\Zed\ManualOrderEntryGui\Communication\ManualOrderEntryGuiCommunicationFactory getFactory()
 */
class ProductFormPlugin extends AbstractPlugin implements ManualOrderEntryFormPluginInterface
{

    /**
     * @var CartFacade
     */
    protected $cartFacade;

    /**
     * @todo @Artem use bridge
     * @param CartFacade $cartFacade
     */
    public function __construct($cartFacade)
    {
        $this->cartFacade = $cartFacade;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Spryker\Shared\Kernel\Transfer\AbstractTransfer $dataTransfer
     *
     * @return \Symfony\Component\Form\FormInterface
     */
    public function createForm(Request $request, $dataTransfer = null)
    {
        return $this->getFactory()->createProductsCollectionForm();
    }

    /**
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function handleData($quoteTransfer)
    {
        $cartChangeTransfer = new CartChangeTransfer();
        $cartChangeTransfer->setQuote($quoteTransfer);

        foreach($quoteTransfer->getManualOrderProducts() as $manualOrderProduct) {
            if (!strlen($manualOrderProduct->getSku())
                || (int)$manualOrderProduct->getQuantity()<=0
//                || !$this->productFacade->hasProductConcrete($manualOrderProduct->getSku())
            ) {
                continue;
            }
//            $productConcreteTransfer = $this->productFacade->getProductConcrete($manualOrderProduct->getSku());

            $itemTransfer = new ItemTransfer();
            $itemTransfer->setSku($manualOrderProduct->getSku())
                ->setQuantity((int)$manualOrderProduct->getQuantity())
            ;

            $cartChangeTransfer->addItem($itemTransfer);

//            $manualOrderProduct->setSku('');
//            $manualOrderProduct->getQuantity(1);
        }
        $quoteTransfer = $this->cartFacade->add($cartChangeTransfer);

        return $quoteTransfer;
    }

}
