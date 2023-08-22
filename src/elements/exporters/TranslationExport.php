<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\elements\exporters;

use Craft;
use craft\base\ElementExporter;
use craft\elements\db\ElementQueryInterface;
use bitsoflove\translations\Translations;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class TranslationExport extends ElementExporter
{
    public static function displayName(): string
    {
        return Craft::t('easy-translations', 'Translations');
    }

    public function export(ElementQueryInterface $query): array
    {
        $results = [];

        $language = Craft::$app->getSites()->getSiteById($query->siteId)->language;

        if (empty($query->orderBy)) {
            if (isset($viewState['order']) && isset($viewState['sort'])) {
                $query->orderBy = [$viewState['order'] => $viewState['sort']];
            } else {
                $query->orderBy = ['source' => 'asc'];
            }
        }

        $elements = Translations::$plugin->translation->getTranslations($query);

        foreach ($elements as $source => $translation) {
            $results[] = [
                'category' => $query->category,
                'source' => $source ?? '',
                $language => $translation->translation ?? '',
            ];
        }

        return $results;
    }
}
