<?php

namespace bitsoflove\translation\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

/**
 * Translate condition
 */
class TranslateCondition extends ElementCondition
{
    protected function conditionRuleTypes(): array
    {
        return array_merge(parent::conditionRuleTypes(), [
            // ...
        ]);
    }
}
