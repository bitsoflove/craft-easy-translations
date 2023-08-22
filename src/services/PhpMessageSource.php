<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\services;

use Craft;
use bitsoflove\translations\Translations;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class PhpMessageSource extends \craft\i18n\PhpMessageSource
{
    protected function loadMessages($category, $language)
    {
        $translations = parent::loadMessages($category, $language);

        $dbTranslations = Translations::$plugin->translation->getDbTranslations($category, $language);

        $translations = array_merge($translations, $dbTranslations);

        return $translations;
    }
}
