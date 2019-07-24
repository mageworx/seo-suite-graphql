<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinksGraphQl\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AddCrosslinksToCategoryPlugin
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
    protected $fullActionName = 'catalog_category_view';

    /**
     * AddCrosslinksToCategoryPlugin constructor.
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
     * @param \Magento\CatalogGraphQl\Model\Resolver\Category\CategoryHtmlAttribute $subject
     * @param string|null $result
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
        if (!$result) {
            return $result;
        }

        /** @var \Magento\Catalog\Model\Category $category */
        $category  = $value['model'];
        $fieldName = $field->getName();

        if ($this->out($category)) {
            return $result;
        }

        $maxReplaceCount = $this->helperData->getReplacemenetCountForCategoryPage();

        // check if crosslinks already exist
        if (strpos($result, $this->helperData->getLinkClass()) !== false) {
            return $result;
        }

        $pairWidget       = [];
        $htmlWidgetCroped = $this->filter->replace($result, $pairWidget);

        /** @var \MageWorx\SeoCrossLinks\Model\Crosslink $crosslink */
        $crosslink              = $this->crosslinkFactory->create();
        $htmlModifyWidgetCroped = $crosslink->replace(
            'category',
            $htmlWidgetCroped,
            $maxReplaceCount,
            null,
            $category->getId()
        );

        if ($htmlModifyWidgetCroped) {
            $modifiedResult = str_replace(array_keys($pairWidget), array_values($pairWidget), $htmlModifyWidgetCroped);
            $category->setData($fieldName, $modifiedResult);
            $result = $modifiedResult;
        }

        return $result;
    }

    /**
     * Check if go out
     *
     * @param \Magento\Catalog\Model\Category $category
     * @param string $fieldName
     * @return boolean
     */
    protected function out($category)
    {
        if (!$this->helperData->isEnabled()) {
            return true;
        }

        if ($this->helperData->getReplacemenetCountForCategoryPage() == 0) {
            return true;
        }

        if ((bool)$category->getUseInCrosslinking() === false) {
            return true;
        }

        return false;
    }
}