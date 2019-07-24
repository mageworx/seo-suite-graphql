<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinksGraphQl\Plugin;

use GraphQL\Language\AST\FieldNode;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\CatalogGraphQl\Model\AttributesJoiner;

class AddCrosslinksAttributeToCategoryCollectionPlugin
{
    /**
     * @var \MageWorx\SeoCrossLinks\Helper\Data
     */
    protected $helperData;

    /**
     * AddCrosslinksAttributeToCategoryCollectionPlugin constructor.
     *
     * @param \MageWorx\SeoCrossLinks\Helper\Data $helperData
     */
    public function __construct(
        \MageWorx\SeoCrossLinks\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Adds "use_in_crosslinking" attribute to category collection if needed
     *
     * @param AttributesJoiner $subject
     * @param $result
     * @param FieldNode $fieldNode
     * @param AbstractCollection $collection
     */
    public function afterJoin(
        AttributesJoiner $subject,
        $result,
        FieldNode $fieldNode,
        AbstractCollection $collection
    ): void {
        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Category\Collection) {
            if ($this->helperData->isEnabled() && $this->helperData->getReplacemenetCountForProductPage()) {
                if ($collection->isAttributeAdded('description')) {
                    $collection->addAttributeToSelect('use_in_crosslinking');
                }
            }
        }
    }
}