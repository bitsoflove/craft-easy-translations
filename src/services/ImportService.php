<?php

/**
 * Translation plugin for Craft CMS 3.x/4.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\services;

use craft\base\Component;

/**
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class ImportService extends Component
{
    public function extractTranslationsFromFile($path)
    {
        $translations = [];
        $handle = fopen($path, 'r');

        while (($row = fgetcsv($handle)) !== false) {
            if (!array_key_exists($row[0], $translations)) {
                $translations[$row[0]] = [];
            }
            if (isset($row[1]) && isset($row[2])) {
                $translationsByCategory = $translations[$row[0]];
                $translationsByCategory[$row[1]] = $row[2];
                $translations[$row[0]] = $translationsByCategory;
            }
        }
        fclose($handle);

        // Remove first line (header) and get language
        $headerLanguage = array_values(array_shift($translations))[0];

        return [$headerLanguage, $translations];
    }
}
