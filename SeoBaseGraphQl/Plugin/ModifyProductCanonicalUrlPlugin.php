<?php
/**
 * Copyright © MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoBaseGraphQl\Plugin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class ModifyProductCanonicalUrlPlugin
{
    /**
     * @var \MageWorx\SeoBase\Model\CanonicalFactory
     */
    protected $canonicalFactory;

    protected $fullActionName = 'catalog_product_view';

    /**
     * ModifyProductCanonicalUrlPlugin constructor.
     *
     * @param \MageWorx\SeoBase\Model\CanonicalFactory $canonicalFactory
     */
    public function __construct(
        \MageWorx\SeoBase\Model\CanonicalFactory $canonicalFactory
    ) {
        $this->canonicalFactory = $canonicalFactory;
    }

    /**
     * @param \Magento\CatalogGraphQl\Model\Resolver\Product\CanonicalUrl $subject
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
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $value['model'];

        if ($product) {
            $arguments = ['fullActionName' => $this->fullActionName];

            /** @var \MageWorx\SeoBase\Model\CanonicalInterface $canonicalModel */
            $canonicalModel = $this->canonicalFactory->create($this->fullActionName, $arguments);
            $canonicalModel->setEntity($product);
            $canonicalUrl = $canonicalModel->getCanonicalUrl();

            if ($canonicalUrl) {
                return $canonicalUrl;
            }
        }

        return $result;
    }
}