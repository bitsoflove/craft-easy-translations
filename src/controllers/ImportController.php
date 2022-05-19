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
use craft\web\UploadedFile;
use bitsoflove\translation\Translation;

/**
 *
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class ImportController extends Controller
{
    public function actionImportCsv(): Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $siteId = Craft::$app->request->getRequiredBodyParam('siteId');
        $language = Craft::$app->getSites()->getSiteById($siteId)->language;

        $csvFile = UploadedFile::getInstanceByName('translation-import');

        if ($csvFile->getExtension() !== 'csv') {
            Craft::$app->getSession()->setError(Craft::t('translation', 'No csv file was imported'));
        } else {
            try {
                $path = Craft::$app->getPath()->getTempAssetUploadsPath() . DIRECTORY_SEPARATOR . $csvFile->name;
                $csvFile->saveAs($path);

                [$headerLanguage, $translations] = Translation::$plugin->import->extractTranslationsFromFile($path);

                if ($translations) {
                    if ($headerLanguage === $language) {
                        $total = count($translations);

                        // Save translations 
                        Translation::$plugin->translation->save($translations, $siteId);

                        Craft::$app->getSession()->setNotice(Craft::t('translation', $total . ' translations were imported succesfully'));
                    } else {
                        Craft::$app->getSession()->setError(Craft::t('translation', 'The language of the translations does not match the current site'));
                    }
                } else {
                    Craft::$app->getSession()->setError(Craft::t('translation', 'No translations found in uploaded file'));
                }
            } catch (\Exception $e) {
                Craft::$app->getSession()->setError(Craft::t('translation', $e->getMessage()));
            }
        }

        return $this->redirectToPostedUrl();
    }
}
