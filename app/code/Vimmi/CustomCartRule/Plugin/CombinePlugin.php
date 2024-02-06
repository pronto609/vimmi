<?php
declare(strict_types=1);
namespace Vimmi\CustomCartRule\Plugin;

use Magento\SalesRule\Model\Rule\Condition\Combine;

class CombinePlugin
{
    /**
     * @param Combine $subject
     * @param array $result
     * @return array
     */
    public function afterGetNewChildSelectOptions(Combine $subject, array $result): array
    {
        foreach ($result as $key => &$condition) {
            if ($condition['label']->getText() === 'Cart Attribute') {
                $condition['value'][] = [
                    'value' => 'Vimmi\CustomCartRule\Model\Rule\Condition\RedCondition|quote_has_item_red_color',
                    'label' => 'The cart has an item with red color'
                ];
            }
        }
        return $result;
    }
}
