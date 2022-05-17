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

use bitsoflove\translation\Translation;

use Craft;
use craft\base\Component;

/**
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class ExportService extends Component
{
    public function overwriteTranslationFile($locale, array $translations)
    {
        $file = $this->getSitePath($locale);
        
        if ($current = @include($file)) { 
            $translations = array_merge($current, $translations);
        }

        $this->writeToFile($translations, $file);
    }

    public function writeToFile($translations, $file)
    {
        $phpFile = "<?php\r\n\r\nreturn ";

        $phpFile .= var_export($translations, true);

        $phpFile .= ';';

        try {
            FileHelper::writeToFile($file, $phpFile);
        }catch (\Throwable $e) {
            throw new \Exception(Craft::t('translation','Something went wrong while saving your translations: '.$e->getMessage()));
        }
    }    

    public function getSitePath($locale, $category = 'site')
    {
        $sitePath = Craft::$app->getPath()->getSiteTranslationsPath();
        $file = $sitePath.DIRECTORY_SEPARATOR.$locale.DIRECTORY_SEPARATOR.$category.'.php';

        return $file;
    }
}