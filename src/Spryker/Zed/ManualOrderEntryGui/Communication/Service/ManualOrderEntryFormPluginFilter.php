<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\ManualOrderEntryGui\Communication\Service;

use Generated\Shared\Transfer\QuoteTransfer;
use Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface;
use Symfony\Component\HttpFoundation\Request;

class ManualOrderEntryFormPluginFilter
{
    /**
     * @var string
     */
    protected $previousStepName;

    /**
     * @var string
     */
    protected $nextStepName;

    /**
     * @param string $previousStepName
     * @param string $nextStepName
     */
    public function __construct($previousStepName, $nextStepName)
    {
        $this->previousStepName = $previousStepName;
        $this->nextStepName = $nextStepName;
    }

    /**
     * @param array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface> $formPlugins
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface>
     */
    public function getFilteredFormPlugins($formPlugins, Request $request, QuoteTransfer $quoteTransfer): array
    {
        $filteredPlugins = [];
        $skippedPlugins = [];
        $isPreviousFormPreFilled = false;

        foreach ($formPlugins as $formPlugin) {
            $pluginAdded = false;

            if ($this->isFormSkipped($formPlugin, $request, $quoteTransfer)) {
                $skippedPlugins[] = $formPlugin;

                continue;
            }

            $isFormSubmitted = $this->isFormSubmitted($formPlugin, $request);
            $isShowNext = $this->isShowNext($request);
            $isFormPreFilled = $this->isFormPreFilled($formPlugin, $quoteTransfer);

            if ($this->isPluginFiltered($isShowNext, $isPreviousFormPreFilled)) {
                $filteredPlugins[] = $formPlugin;
                $pluginAdded = true;
            }

            if ($this->isBreakPluginSearch($isFormSubmitted, $isFormPreFilled)) {
                break;
            }

            if (!$pluginAdded) {
                $filteredPlugins[] = $formPlugin;
            }
            $isPreviousFormPreFilled = $isFormPreFilled;
        }

        $filteredPlugins = $this->augmentFilteredPlugins($formPlugins, $filteredPlugins, $skippedPlugins);

        if ($this->isShowPrevious($request) && count($filteredPlugins) > 1) {
            array_pop($filteredPlugins);
        }

        return $filteredPlugins;
    }

    /**
     * @param array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface> $formPlugins
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface>
     */
    public function getSkippedFormPlugins($formPlugins, Request $request, QuoteTransfer $quoteTransfer): array
    {
        $skippedPlugins = [];

        foreach ($formPlugins as $formPlugin) {
            if ($this->isFormSkipped($formPlugin, $request, $quoteTransfer)) {
                $skippedPlugins[] = $formPlugin;

                continue;
            }

            $isFormSubmitted = $this->isFormSubmitted($formPlugin, $request);
            $isFormPreFilled = $this->isFormPreFilled($formPlugin, $quoteTransfer);

            if ($this->isBreakPluginSearch($isFormSubmitted, $isFormPreFilled)) {
                break;
            }
        }

        return $skippedPlugins;
    }

    protected function isShowNext(Request $request): bool
    {
        return $request->get($this->nextStepName) !== null;
    }

    protected function isShowPrevious(Request $request): bool
    {
        return $request->get($this->previousStepName) !== null;
    }

    protected function isFormPreFilled(ManualOrderEntryFormPluginInterface $formPlugin, QuoteTransfer $quoteTransfer): bool
    {
        return $formPlugin->isFormPreFilled($quoteTransfer);
    }

    protected function isFormSubmitted(ManualOrderEntryFormPluginInterface $formPlugin, Request $request): bool
    {
        return $request->get($formPlugin->getName()) !== null;
    }

    protected function isFormSkipped(ManualOrderEntryFormPluginInterface $formPlugin, Request $request, QuoteTransfer $quoteTransfer): bool
    {
        return $formPlugin->isFormSkipped($request, $quoteTransfer);
    }

    /**
     * @param array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface> $formPlugins
     * @param array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface> $filteredPlugins
     * @param array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface> $skippedPlugins
     *
     * @return array<\Spryker\Zed\ManualOrderEntryGui\Communication\Plugin\ManualOrderEntryFormPluginInterface>
     */
    protected function augmentFilteredPlugins($formPlugins, $filteredPlugins, $skippedPlugins): array
    {
        if (!count($filteredPlugins)) {
            foreach ($formPlugins as $formPlugin) {
                $isSkipped = false;

                foreach ($skippedPlugins as $skippedPlugin) {
                    if ($formPlugin->getName() == $skippedPlugin->getName()) {
                        $isSkipped = true;

                        break;
                    }
                }

                if (!$isSkipped) {
                    $filteredPlugins[] = $formPlugin;

                    break;
                }
            }
        }

        return $filteredPlugins;
    }

    protected function isPluginFiltered(bool $isShowNext, bool $isPreviousFormPreFilled): bool
    {
        return $isShowNext || $isPreviousFormPreFilled;
    }

    protected function isBreakPluginSearch(bool $isFormSubmitted, bool $isFormPreFilled): bool
    {
        return !$isFormSubmitted && !$isFormPreFilled;
    }
}
