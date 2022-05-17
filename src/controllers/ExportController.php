<?php
/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\controllers;


use Craft;
use craft\web\Controller;
use craft\web\Response;
use bitsoflove\translation\Translation;

/**
 *
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class ExportController extends Controller
{
    // redundant and unused for now since there is an exporter defined for the custom Translate element
    public function actionExportCsv() : Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $siteId = Craft::$app->request->getRequiredBodyParam('siteId');
        $language = Craft::$app->getSites()->getSiteById($siteId)->language;

        $translations = Translation::getInstance()->translation->getTranslations('site', $language);

        $filePath = Craft::$app->getPath()->getTempPath() . DIRECTORY_SEPARATOR . 'translations_'. $language . '.csv';
        $file = fopen($filePath, "w");

        $csvHeader = array(
            'Source',
            $language
        );

        fputcsv($file, $csvHeader);

        foreach ($translations as $source => $translation) {
            $row = array($source, $translation);

            fputcsv($file, $row);
        }

        fclose($file);

        return Craft::$app->getResponse()->sendFile($filePath);
    }
}
