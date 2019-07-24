<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoCrossLinksGraphQl\Plugin;

use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\Api\SearchCriteriaInterface;

class AddCrosslinksAttributeToProductCollectionPlugin
{
    /**
     * @var \MageWorx\SeoCrossLinks\Helper\Data
     */
    protected $helperData;

    /**
     * AddCrosslinksAttributeToProductCollectionPlugin constructor.
     *
     * @param \MageWorx\SeoCrossLinks\Helper\Data $helperData
     */
    public function __construct(
        \MageWorx\SeoCrossLinks\Helper\Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * @param CollectionProcessorInterface $subject
     * @param Collection $result
     * @param Collection $collection
     * @param SearchCriteriaInterface $searchCriteria
     * @param array $attributeNames
     * @return Collection
     */
    public function afterProcess(
        CollectionProcessorInterface $subject,
        Collection $result,
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames
    ) {
        if ($this->helperData->isEnabled() && $this->helperData->getReplacemenetCountForProductPage()) {
            foreach ($this->helperData->getProductAttributesForReplace() as $attributeName) {
                if ($result->isAttributeAdded($attributeName)) {
                    $result->addAttributeToSelect('use_in_crosslinking');
                    break;
                }
            }
        }

        return $result;
    }
}