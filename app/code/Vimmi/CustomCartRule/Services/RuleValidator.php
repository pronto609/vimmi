<?php
declare(strict_types=1);
namespace Vimmi\CustomCartRule\Services;

use Magento\Eav\Model\Entity\Attribute as ModelAttribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\Exception\LocalizedException;

class RuleValidator
{
    private const ATTRIBUTE_CODE = 'color';
    private const ATTRIBUTE_VALUE = 'Red';

    /**
     * @param Attribute $eavManager
     * @param ModelAttribute $modelAttribute
     * @param $isVald
     */
    public function __construct(
        private readonly Attribute $eavManager,
        private readonly ModelAttribute $modelAttribute,
        private $isVald = false
    ) {
    }

    public function validate(\Magento\Framework\Model\AbstractModel $model)
    {
        $items = $model->getQuote()->getItems();
        foreach ($items as $item) {
            $product = $item->getProduct();
            if ($product->getTypeId() === 'configurable') {
                $productOptions = $product->getTypeInstance()->getConfigurableOptions($product);
                $this->checkConfCondition($item, $productOptions);
            }
            $this->checkSimple($product);
        }
        return $this->isVald;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    private function checkSimple(\Magento\Catalog\Model\Product $product): bool
    {
        if ($this->isVald) {
            return $this->isVald;
        }
        return $this->isVald = (int)$product->getColor() === $this->getOptionId(self::ATTRIBUTE_VALUE);
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @param array $productOptions
     * @return bool
     */
    private function checkConfCondition(\Magento\Quote\Model\Quote\Item $item, array $productOptions): bool
    {
        if ($this->isVald) {
            return $this->isVald;
        }
        foreach ($productOptions[$this->getAttributeId(self::ATTRIBUTE_CODE)] as $productOption) {
            if ($productOption['sku'] !== $item->getSku()) {
                continue;
            }
            if (
                $productOption['attribute_code'] === self::ATTRIBUTE_CODE &&
                $productOption['default_title'] === self::ATTRIBUTE_VALUE
            ) {
                return $this->isVald = true;
            }
        }
        return false;
    }

    /**
     * @param string $attrCode
     * @return int
     */
    private function getAttributeId(string $attrCode): int
    {
        return $this->eavManager->getIdByCode(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, $attrCode);
    }

    /**
     * @param string $attrCode
     * @return false|ModelAttribute
     */
    private function getAttribute(string $attrCode): ?ModelAttribute
    {
        try {
            return $this->modelAttribute->loadByCode(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE, self::ATTRIBUTE_CODE);
        } catch (LocalizedException $exception) {
            return false;
        }
    }

    /**
     * @param string $attrCode
     * @return int|null
     */
    private function getOptionId(string $attrCode): ?int
    {
        try {
            $attribute = $this->getAttribute($attrCode);
            if (!$attribute) {
                return null;
            }
            $attrOptions = $attribute->getSource()->getAllOptions();
            foreach ($attrOptions as $option) {
                if ($option['label'] === self::ATTRIBUTE_VALUE) {
                    return $option['value'];
                }
            }
        } catch (LocalizedException $exception) {
            return null;
        }
        return null;
    }
}
