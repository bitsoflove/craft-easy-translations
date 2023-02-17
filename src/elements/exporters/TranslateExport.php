<?php

/**
 * Translation plugin for Craft CMS 3.x/4.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\elements\exporters;

use Craft;
use craft\base\ElementExporter;
use craft\elements\db\ElementQueryInterface;
use bitsoflove\translation\Translation;

class TranslateExport extends ElementExporter
{
    public static function displayName(): string
    {
        return Craft::t('craft-translator', 'Translations');
    }

    public function export(ElementQueryInterface $query): array
    {
        $results = [];

        $language = Craft::$app->getSites()->getSiteById($query->siteId)->language;

        if (empty($query->orderBy)) {
            if (isset($viewState['order']) && isset($viewState['sort'])) {
                $query->orderBy = [$viewState['order'] => $viewState['sort']];
            } else {
                $query->orderBy = ['title' => 'asc'];
            }
        }

        $elements = Translation::$plugin->translation->getTranslations($query);

        foreach ($elements as $translation) {
            $results[] = [
                'category' => $query->category,
                'source' => $translation->title ?? '',
                $language => $translation->translation ?? '',
            ];
        }

        return $results;
    }
}
