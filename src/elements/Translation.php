<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\web\ErrorHandler;
use bitsoflove\translations\elements\db\TranslationQuery;
use bitsoflove\translations\elements\exporters\TranslationExport;
use bitsoflove\translations\Translations;
use craft\helpers\FileHelper;

class Translation extends Element
{
    public $source;
    public $translateId;
    public $translation;
    public $path;
    public $siteId;
    public $field;
    public $category;

    public function getName()
    {
        return Craft::t('easy-translations', 'Translations');
    }

    public function __toString()
    {
        try {
            return $this->source;
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
        }
    }

    public static function isLocalized(): bool
    {
        return true;
    }

    public static function hasStatuses(): bool
    {
        return false;
    }

    public static function find(): ElementQueryInterface
    {
        return new TranslationQuery(get_called_class());
    }

    protected static function defineTableAttributes(): array
    {
        $attributes['source'] = ['label' => Craft::t('easy-translations', 'Source')];

        $attributes['field'] = ['label' => Craft::t('easy-translations', 'Translation')];

        return $attributes;
    }

    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['source', 'field'];
    }

    protected function tableAttributeHtml(string $attribute): string
    {
        return $this->$attribute;
    }

    protected static function defineSearchableAttributes(): array
    {
        return [
            'source',
            'field',
        ];
    }

    protected static function defineSortOptions(): array
    {
        return [
            'source' => Craft::t('easy-translations', 'Source'),
            'field' => Craft::t('easy-translations', 'Translation'),
        ];
    }

    protected static function defineExporters(string $source): array
    {
        $exporters[] = TranslationExport::class;
        return $exporters;
    }

    protected static function defineSources(string $context = null): array
    {
        $sources = [];
        $user = Craft::$app->getUser();

        if($user->checkPermission('easy-translations-viewTemplates')) {
          $templateSources = self::getTemplateSources(Craft::$app->path->getSiteTemplatesPath());
          rsort($templateSources);
          $sources[] = ['heading' => Craft::t('easy-translations', 'Templates')];

          $sources[] = [
              'label'    => Craft::t('easy-translations', 'All Templates'),
              'key'      => 'templates:',
              'criteria' => [
                  'path' => [
                      Craft::$app->path->getSiteTemplatesPath()
                  ],
                  'category' => 'site'
              ],
              'nested' => $templateSources
          ];
        }

        if($user->checkPermission('easy-translations-viewCategories')) {
          $sources[] = ['heading' => Craft::t('easy-translations', 'Categories')];

          $language = Craft::$app->getSites()->getPrimarySite()->language;
          $fallbackLanguage = substr($language, 0, 2);

          $siteTranslationsPath = Craft::$app->getPath()->getSiteTranslationsPath() . DIRECTORY_SEPARATOR . $language;

          if (!is_dir($siteTranslationsPath)) {
            $siteTranslationsPath = Craft::$app->getPath()->getSiteTranslationsPath() . DIRECTORY_SEPARATOR . $fallbackLanguage;
          }

          $files = [];

          if(
              is_dir($siteTranslationsPath)
          ){
              $options = [
                  'recursive' => false,
                  'only' => ['*.php'],
                  'except' => ['vendor/', 'node_modules/']
              ];

              $files = FileHelper::findFiles($siteTranslationsPath, $options);
          }

          sort($files);

          $filesWithPermissions = [];
          $filesWithoutPermissions = [];

          foreach ($files as $categoryFile) {
              $fileName = substr(basename($categoryFile), 0, -4);
              if($user->checkPermission('easy-translations-viewCategories:' . $fileName)) {
                array_push($filesWithPermissions, $fileName);
              } else {
                array_push($filesWithoutPermissions, $fileName);
              }
          }

          if (empty($filesWithPermissions)) {
            $filesWithPermissions = $filesWithoutPermissions;
          }

          foreach ($filesWithPermissions as $fileName) {
            $sources['categories:' . $fileName] = [
              'label' => ucfirst(str_replace('-', ' ',$fileName)),
              'key' => $fileName,
              'criteria' => [
                  'category' => $fileName
              ],
            ];
          }
        }


        return $sources;
    }

    private static function getTemplateSources($path)
    {
        $templateSources = [];

        $options = [
            'recursive' => false,
            'only' => ['*.html', '*.twig', '*.js', '*.json', '*.atom', '*.rss'],
            'except' => ['vendor/', 'node_modules/']
        ];

        $files = FileHelper::findFiles($path, $options);

        foreach ($files as $template) {
          if (Translations::$plugin->translation->hasStaticTranslations($template)) {
            $fileName = basename($template);

            $cleanTemplateKey = str_replace('/', '*', $template);
            $templateSources['templatessources:' . $fileName] = [
                'label' => $fileName,
                'key' => 'templates:' . $cleanTemplateKey,
                'criteria' => [
                    'path' => [
                        $template
                    ],
                    'category' => 'site'
                ],
            ];
          }
        }

        $options = [
            'recursive' => false,
            'except' => ['vendor/', 'node_modules/']
        ];

        $directories = FileHelper::findDirectories($path, $options);

        foreach ($directories as $template) {
            if (Translations::$plugin->translation->hasStaticTranslations($template)) {
              $fileName = basename($template);

              $cleanTemplateKey = str_replace('/', '*', $template);

              $nestedSources = self::getTemplateSources($template);

              sort($nestedSources);

              $templateSources['templatessources:' . $fileName] = [
                  'label' => $fileName . '/',
                  'key' => 'templates:' . $cleanTemplateKey,
                  'criteria' => [
                      'path' => [
                          $template
                      ],
                      'category' => 'site'
                  ],
                  'nested' => $nestedSources,
              ];
            }
        }
        sort($templateSources);

        return $templateSources;
    }

    public static function indexHtml(ElementQueryInterface $elementQuery, array $disabledElementIds = null, array $viewState, string $sourceKey = null, string $context = null, bool $includeContainer, bool $showCheckboxes): string
    {

        if (empty($elementQuery->siteId)) {
            $primarySite = Craft::$app->getSites()->getPrimarySite();
            $elementQuery->siteId = $primarySite->id;
        }

        if (empty($elementQuery->orderBy)) {
            if (isset($viewState['order']) && isset($viewState['sort'])) {
                $elementQuery->orderBy = [$viewState['order'] => $viewState['sort']];
            } else {
                $elementQuery->orderBy = ['source' => 'asc'];
            }
        }

        $elements = Translations::$plugin->translation->getTranslations($elementQuery);

        $attributes = Craft::$app->getElementIndexes()->getTableAttributes(static::class, $sourceKey);
        $site = Craft::$app->getSites()->getSiteById($elementQuery->siteId);
        $lang = Craft::$app->getI18n()->getLocaleById($site->language);
        $trans = Craft::t('easy-translations', 'Translation') . ' - ' . ucfirst($lang->displayName);
        array_walk_recursive($attributes, function (&$attributes) use ($trans) {
            if ($attributes == Craft::t('easy-translations', 'Translation')) {
                $attributes = $trans;
            }
        });

        $variables = [
            'viewMode' => $viewState['mode'],
            'context' => $context,
            'disabledElementIds' => $disabledElementIds,
            'attributes' => $attributes,
            'elements' => $elements,
            'showCheckboxes' => $showCheckboxes,
        ];

        Craft::$app->view->registerJs("$('table.fullwidth thead th').css('width', '50%'); $('.checkbox-cell').remove(); $('table tbody tr:not(:last-child)').css('border-bottom', '2px solid rgba(96,125,159,.1)');");

        $template = '_elements/' . $viewState['mode'] . 'view/' . ($includeContainer ? 'container' : 'elements');

        return Craft::$app->view->renderTemplate($template, $variables);
    }
}
