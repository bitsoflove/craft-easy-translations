<?php

/**
 * Translation plugin for Craft CMS 3.x
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
            if (isset($row[0]) && isset($row[1])) {
                $translations[$row[0]] = $row[1];
            }
        }
        fclose($handle);

        // Remove first line (header)
        $headerLanguage = array_shift($translations);

        return [$headerLanguage, $translations];
    }
}
