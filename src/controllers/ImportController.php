<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\controllers;


use Craft;
use craft\web\Controller;
use craft\web\Response;
use craft\web\UploadedFile;
use bitsoflove\translations\Translations;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
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
            Craft::$app->getSession()->setError(Craft::t('easy-translations', 'The imported file needs to be a csv file'));
        } else {
            try {
                $path = Craft::$app->getPath()->getTempAssetUploadsPath() . DIRECTORY_SEPARATOR . $csvFile->name;
                $csvFile->saveAs($path);

                [$headerLanguage, $translationsByCategory] = Translations::$plugin->import->extractTranslationsFromFile($path);

                if ($translationsByCategory) {
                    if ($headerLanguage === $language) {
                        $total = 0;

                        foreach ($translationsByCategory as $category => $translations) {
                            Translations::$plugin->translation->save($translations, $siteId, $category);
                            $total += count($translations);
                        }

                        Craft::$app->getSession()->setNotice(Craft::t('easy-translations', '{amount} translations were imported succesfully', ['amount' => $total]));
                    } else {
                        Craft::$app->getSession()->setError(Craft::t('easy-translations', 'The language of the translations does not match the current site'));
                    }
                } else {
                    Craft::$app->getSession()->setError(Craft::t('easy-translations', 'No translations found in uploaded file'));
                }
            } catch (\Exception $e) {
                Craft::$app->getSession()->setError(Craft::t('easy-translations', $e->getMessage()));
            }
        }

        return $this->redirectToPostedUrl();
    }
}
