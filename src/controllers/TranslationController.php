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

        Translation::getInstance()->translation->save($translations, $siteId);

        return $this->redirectToPostedUrl();
    }
}
