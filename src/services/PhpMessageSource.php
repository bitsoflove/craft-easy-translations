<?php

namespace bitsoflove\translation\services;

use Craft;
use bitsoflove\translation\Translation;

class PhpMessageSource extends \craft\i18n\PhpMessageSource
{
    protected function loadMessages($category, $language): array
    {
        $translations = parent::loadMessages($category, $language);

        $dbTranslations = Translation::$plugin->translation->getDbTranslations($category, $language);

        $translations = array_merge($translations, $dbTranslations);

        return $translations;
    }
}
