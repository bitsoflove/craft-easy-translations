<?php
/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\elements\exporters;

use Craft;
use craft\base\EagerLoadingFieldInterface;
use craft\base\ElementExporter;
use craft\elements\db\ElementQuery;
use craft\elements\db\ElementQueryInterface;
use bitsoflove\translation\Translation;

class TranslateExport extends ElementExporter
{
    public static function displayName(): string
    {
        return Craft::t('translation', 'Translations');
    }

    public function export(ElementQueryInterface $query): array
    {
        $results = [];

        $language = Craft::$app->getSites()->getSiteById($query->siteId)->language;
        $elements = $translations = Translation::getInstance()->translation->getTranslations('site', $language);

        foreach ($elements as $source => $translation) {
            $results[] = [
                'source' => $source ?? '',
                $language => $translation ?? '',
            ];
        }

        return $results;
    }
}
