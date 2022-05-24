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
use craft\web\View;
use bitsoflove\translation\Translation;

/**
 *
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class TranslationController extends Controller
{
    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        return $this->renderTemplate(
            'translation/index.twig',
            [],
            View::TEMPLATE_MODE_CP
        );
    }

    public function actionSave(): Response
    {
        $this->requireCpRequest();
        $this->requirePostRequest();

        $translations = Craft::$app->request->getRequiredBodyParam('translation');
        $siteId = Craft::$app->request->getRequiredBodyParam('siteId');
        $category = Craft::$app->request->getRequiredBodyParam('category');

        try {
            Translation::$plugin->translation->save($translations, $siteId, $category);

            Craft::$app->getSession()->setNotice(Craft::t('translation', 'Translations were saved succesfully'));
        } catch (\Exception $e) {
            Craft::$app->getSession()->setError(Craft::t('translation', 'Something went wrong while saving the translations'));
        }

        return $this->redirectToPostedUrl();
    }
}
