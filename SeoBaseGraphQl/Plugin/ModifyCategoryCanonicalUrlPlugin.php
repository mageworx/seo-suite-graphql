<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBaseGraphQl\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ModifyCategoryCanonicalUrlPlugin
{
    /**
     * @var \MageWorx\SeoBase\Model\CanonicalFactory
     */
    protected $canonicalFactory;

    protected $fullActionName = 'catalog_category_view';

    /**
     * ModifyCategoryCanonicalUrlPlugin constructor.
     *
     * @param \MageWorx\SeoBase\Model\CanonicalFactory $canonicalFactory
     */
    public function __construct(
        \MageWorx\SeoBase\Model\CanonicalFactory $canonicalFactory
    ) {
        $this->canonicalFactory = $canonicalFactory;
    }

    /**
     * @param \ScandiPWA\CatalogGraphQl\Model\Resolver\Category\CanonicalUrl $subject
     * @param string $result
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
        /** @var \Magento\Catalog\Model\Category $product */
        $category = $value['model'];


        if ($category) {
            $arguments = ['fullActionName' => $this->fullActionName];

            /** @var \MageWorx\SeoBase\Model\CanonicalInterface $canonicalModel */
            $canonicalModel = $this->canonicalFactory->create($this->fullActionName, $arguments);
            $canonicalModel->setEntity($category);
            $canonicalUrl = $canonicalModel->getCanonicalUrl();

            if ($canonicalUrl) {
                return $canonicalUrl;
            }
        }

        return $result;
    }
}