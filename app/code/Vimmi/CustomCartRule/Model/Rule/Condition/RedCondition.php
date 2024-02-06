<?php
declare(strict_types=1);

namespace Vimmi\CustomCartRule\Model\Rule\Condition;

use Magento\Config\Model\Config\Source\Yesno as SourceConfig;
use Magento\Rule\Model\Condition\Context;
use Vimmi\CustomCartRule\Services\RuleValidator;

class RedCondition extends \Magento\Rule\Model\Condition\AbstractCondition
{
    /**
     * @param Context $context
     * @param SourceConfig $sourceConfig
     * @param RuleValidator $ruleValidator
     * @param array $data
     */
    public function __construct(
        protected Context $context,
        private readonly SourceConfig $sourceConfig,
        private readonly  RuleValidator $ruleValidator,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Load attribute options
     *
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([
            'quote_has_item_red_color' => __('The cart has an item with red color')
        ]);
        return $this;
    }

    /**
     * Get input type
     *
     * @return string
     */
    public function getInputType()
    {
        return 'boolean';
    }

    /**
     * Get value element type
     *
     * @return string
     */
    public function getValueElementType(): string
    {
        return 'select';
    }

    /**
     * Get value select options
     *
     * @return array
     */
    public function getValueSelectOptions(): array
    {
        return $this->sourceConfig->toOptionArray();
    }

    /**
     * Validate Customer Group Rule Condition
     *
     * @param \Magento\Framework\Model\AbstractModel $model
     * @return bool
     */
    public function validate(\Magento\Framework\Model\AbstractModel $model): bool
    {
        if ($model->getQuote() && $model->getQuote()->getItems()) {
            return $this->ruleValidator->validate($model);
        }
        return false;
    }
}
