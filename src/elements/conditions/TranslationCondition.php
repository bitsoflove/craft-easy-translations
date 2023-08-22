<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\elements\conditions;

use Craft;
use craft\elements\conditions\ElementCondition;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class TranslationCondition extends ElementCondition
{
    protected function conditionRuleTypes(): array
    {
        return array_merge(parent::conditionRuleTypes(), [
            // ...
        ]);
    }
}
