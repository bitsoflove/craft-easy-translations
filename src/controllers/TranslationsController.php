<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\controllers;


use Craft;
use craft\web\Controller;
use craft\web\Response;
use craft\web\View;
use bitsoflove\translations\Translations;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class TranslationsController extends Controller
{
    public function actionIndex(): Response
    {
        $this->requireCpRequest();

        return $this->renderTemplate(
            'easy-translations/index.twig',
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
            Translations::$plugin->translation->save($translations, $siteId, $category);

            Craft::$app->getSession()->setNotice(Craft::t('easy-translations', 'Translations were saved succesfully'));
        } catch (\Exception $e) {
            Craft::$app->getSession()->setError(Craft::t('easy-translations', 'Something went wrong while saving the translations'));
        }

        return $this->redirectToPostedUrl();
    }
}
