<?php

/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation;

use bitsoflove\translation\elements\Translate;
use Craft;
use craft\base\Plugin;
use craft\web\UrlManager;
use craft\events\RegisterUrlRulesEvent;
use bitsoflove\translation\services\PhpMessageSource;
use bitsoflove\translation\services\TranslationService;
use bitsoflove\translation\services\ImportService;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
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

    public $schemaVersion = '0.0.1';
    public $hasCpSettings = false;
    public $hasCpSection = true;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->registerServices();
        $this->registerPermissions();

        // Register CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge($event->rules, [
                    'translation' => 'translation/translation/index',
                ]);
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

    private function registerPermissions() {
      Event::on(
        UserPermissions::class,
        UserPermissions::EVENT_REGISTER_PERMISSIONS,
        function(RegisterUserPermissionsEvent $event) {
          $nestedCategories = [];
          $categories = array_slice(Translate::sources(), 3);

          foreach ($categories as $categorySource) {
            if (array_key_exists('key', $categorySource)) {
              $nestedCategories['craft-translator-viewCategories:' . $categorySource['key']] = [
                'label' => Craft::t('craft-translator', 'View {category}', ['category' => $categorySource['key']]),
              ];
            }
          }

          $event->permissions['Craft Translator'] = [
            'craft-translator-viewTemplates' => [
                'label' => Craft::t('craft-translator', 'View templates'),
            ],
            'craft-translator-viewCategories' => [
                'label' => Craft::t('craft-translator', 'View categories'),
                'nested' => $nestedCategories,
            ],
          ];
        }
    );
    }
}
