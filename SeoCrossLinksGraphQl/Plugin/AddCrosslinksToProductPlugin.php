<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinksGraphQl\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AddCrosslinksToProductPlugin
{
    /**
     * Crosslink factory
     *
     * @var CrosslinkFactory
     */
    protected $crosslinkFactory;

    /**
     * Filter object
     *
     * @var \MageWorx\SeoCrossLinks\Model\Filter
     */
    protected $filter;

    /**
     * @var \MageWorx\SeoCrossLinks\Helper\Data
     */
    protected $helperData;

    /**
     * @var string
     */
    protected $fullActionName = 'catalog_product_view';

    /**
     * AddCrosslinksToProductPlugin constructor.
     *
     * @param \MageWorx\SeoCrossLinks\Model\CrosslinkFactory $crosslinkFactory
     * @param \MageWorx\SeoCrossLinks\Helper\Data $helperData
     * @param \MageWorx\SeoCrossLinks\Model\Filter $filter
     */
    public function __construct(
        \MageWorx\SeoCrossLinks\Model\CrosslinkFactory $crosslinkFactory,
        \MageWorx\SeoCrossLinks\Helper\Data $helperData,
        \MageWorx\SeoCrossLinks\Model\Filter $filter
    ) {
        $this->crosslinkFactory = $crosslinkFactory;
        $this->helperData       = $helperData;
        $this->filter           = $filter;
    }

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Product\ProductComplexTextAttribute $subject
     * @param array $result
     * @param Field $field
     * @param $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     */
    public function afterResolve(
        $subject,
        $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (empty($result['html'])) {
            return $result;
        }

        $html = $result['html'];

        /** @var \Magento\Catalog\Model\Product $product */
        $product   = $value['model'];
        $fieldName = $field->getName();

        if ($this->out($product, $fieldName)) {
            return $result;
        }

        $maxReplaceCount = $this->helperData->getReplacemenetCountForProductPage();

        // check if crosslinks already exist
        if (strpos($html, $this->helperData->getLinkClass()) !== false) {
            return $result;
        }

        $pairWidget       = [];
        $htmlWidgetCroped = $this->filter->replace($html, $pairWidget);

        /** @var \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink */
        $crosslink              = $this->crosslinkFactory->create();
        $htmlModifyWidgetCroped = $crosslink->replace(
            'product',
            $htmlWidgetCroped,
            $maxReplaceCount,
            null,
            $product->getSku()
        );

        if ($htmlModifyWidgetCroped) {
            $modifiedResult = str_replace(array_keys($pairWidget), array_values($pairWidget), $htmlModifyWidgetCroped);
            $product->setData($fieldName, $modifiedResult);
            $result['html'] = $modifiedResult;
        }

        return $result;
    }

    /**
     * Check if go out
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $fieldName
     * @return boolean
     */
    protected function out($product, $fieldName)
    {
        if (!$this->helperData->isEnabled()) {
            return true;
        }

        if ($this->helperData->getReplacemenetCountForProductPage() == 0) {
            return true;
        }

        if (!in_array($fieldName, $this->helperData->getProductAttributesForReplace())) {
            return true;
        }

        if ((bool)$product->getUseInCrosslinking() === false) {
            return true;
        }

        return false;
    }
}