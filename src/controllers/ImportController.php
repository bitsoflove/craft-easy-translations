<?php

/**
 * Translation plugin for Craft CMS 3.x/4.x
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
            Craft::$app->getSession()->setError(Craft::t('craft-translator', 'The imported file needs to be a csv file'));
        } else {
            try {
                $path = Craft::$app->getPath()->getTempAssetUploadsPath() . DIRECTORY_SEPARATOR . $csvFile->name;
                $csvFile->saveAs($path);

                [$headerLanguage, $translationsByCategory] = Translation::$plugin->import->extractTranslationsFromFile($path);

                if ($translationsByCategory) {
                    if ($headerLanguage === $language) {
                        $total = 0;

                        foreach ($translationsByCategory as $category => $translations) {
                            Translation::$plugin->translation->save($translations, $siteId, $category);
                            $total += count($translations);
                        }

                        Craft::$app->getSession()->setNotice(Craft::t('craft-translator', '{amount} translations were imported succesfully', ['amount' => $total]));
                    } else {
                        Craft::$app->getSession()->setError(Craft::t('craft-translator', 'The language of the translations does not match the current site'));
                    }
                } else {
                    Craft::$app->getSession()->setError(Craft::t('craft-translator', 'No translations found in uploaded file'));
                }
            } catch (\Exception $e) {
                Craft::$app->getSession()->setError(Craft::t('craft-translator', $e->getMessage()));
            }
        }

        return $this->redirectToPostedUrl();
    }
}
