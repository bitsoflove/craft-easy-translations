<?php

/**
 * Translation plugin for Craft CMS 3.x/4.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation;

use Craft;
use bitsoflove\translation\services\ImportService;
use bitsoflove\translation\services\PhpMessageSource;
use bitsoflove\translation\services\TranslationService;
use craft\base\Plugin;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;
use craft\services\Elements;
use craft\web\UrlManager;
use yii\base\Event;

/**
 *
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 *
 */
class Translation extends Plugin
{
    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * Translation::$plugin
     *
     * @var Translation
     */
    public static $plugin;

    public string $schemaVersion = '0.0.1';
    public bool $hasCpSettings = false;
    public bool $hasCpSection = true;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->registerServices();

        // Register Translation Controller Action
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
              $event->rules['craft-translator'] = 'craft-translator/translation/index';
            }
        );

        $this->initTranslations();
    }

    private function registerServices()
    {
        $this->setComponents([
            'translation' => TranslationService::class,
            'import' => ImportService::class,
        ]);
    }

    // set i18n component to custom class
    private function initTranslations()
    {
        $i18n = Craft::$app->getComponents(false)['i18n'];

        foreach ($i18n->translations as $key => $source) {
            if (is_array($i18n->translations[$key])) {
                $i18n->translations[$key]['class'] = PhpMessageSource::class;
            } else {
                $i18n->translations[$key] = [
                    'class' => PhpMessageSource::class,
                    'sourceLanguage' => $i18n->translations[$key]->sourceLanguage,
                    'basePath' => $i18n->translations[$key]->basePath,
                    'forceTranslation' => $i18n->translations[$key]->forceTranslation,
                    'allowOverrides' => $i18n->translations[$key]->allowOverrides,
                ];
            }
        }

        Craft::$app->setComponents(
            [
                'i18n' => $i18n
            ]
        );
    }
}
