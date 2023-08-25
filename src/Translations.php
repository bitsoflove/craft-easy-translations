<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations;

use bitsoflove\translations\elements\Translation;
use Craft;
use bitsoflove\translations\services\PhpMessageSource;
use bitsoflove\translations\services\TranslationService;
use craft\base\Plugin;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use bitsoflove\translations\services\ImportService;
use craft\events\RegisterUserPermissionsEvent;
use craft\services\UserPermissions;
use yii\base\Event;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class Translations extends Plugin
{
    /**
     * @var Translations
     */
    public static $plugin;

    public $schemaVersion = '1.0.0';
    public $hasCpSettings = false;
    public $hasCpSection = true;

    public function init()
    {
        parent::init();
        self::$plugin = $this;

        $this->registerServices();
        $this->registerPermissions();

        // Register Translation Controller Action
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
              $event->rules['easy-translations'] = 'easy-translations/translations/index';
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
          $categories = array_filter(Translation::sources(''), function ($value, $key) {
            return str_starts_with($key, 'categories');
          }, ARRAY_FILTER_USE_BOTH);

          foreach ($categories as $categorySource) {
            if (array_key_exists('key', $categorySource)) {
              $nestedCategories['easy-translations-viewCategories:' . explode(':', $categorySource['key'])[1]] = [
                'label' => Craft::t('easy-translations', 'View {category}', ['category' => explode(':', $categorySource['key'])[1]]),
              ];
            }
          }

          $event->permissions['Easy Translations'] = [
            'easy-translations-viewTemplates' => [
                'label' => Craft::t('easy-translations', 'View templates'),
            ],
            'easy-translations-viewCategories' => [
                'label' => Craft::t('easy-translations', 'View categories'),
                'nested' => $nestedCategories,
            ],
          ];
        }
    );
    }
}
