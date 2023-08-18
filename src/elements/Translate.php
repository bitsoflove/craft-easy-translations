<?php

namespace bitsoflove\translation\elements;

use Craft;
use bitsoflove\translation\elements\conditions\TranslateCondition;
use bitsoflove\translation\elements\db\TranslateQuery;
use craft\base\Element;
use craft\elements\User;
use craft\elements\conditions\ElementConditionInterface;
use craft\elements\db\ElementQueryInterface;
use bitsoflove\translation\Translation;
use bitsoflove\translation\elements\exporters\TranslateExport;
use craft\helpers\FileHelper;

/**
 * Translate element type
 */
class Translate extends Element
{
  public string $translation; // Required to enable search on translation value
  public $field; // The actual field template that gets rendered in the column

  // This will be the name of the column header
  public static function displayName(): string
  {
    return Craft::t('craft-translator', 'Source');
  }

  public static function lowerDisplayName(): string
  {
    return Craft::t('craft-translator', 'translation');
  }

  public static function pluralDisplayName(): string
  {
    return Craft::t('craft-translator', 'Translations');
  }

  public static function pluralLowerDisplayName(): string
  {
    return Craft::t('craft-translator', 'translations');
  }

  public static function trackChanges(): bool
  {
    return false;
  }

  public static function hasContent(): bool
  {
    return false;
  }

  public static function hasTitles(): bool
  {
    return true;
  }

  public static function hasUris(): bool
  {
    return false;
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
    return Craft::createObject(TranslateQuery::class, [static::class]);
  }

  public static function createCondition(): ElementConditionInterface
  {
    return Craft::createObject(TranslateCondition::class, [static::class]);
  }

  protected static function defineActions(string $source): array
  {
    // List any bulk element actions here
    return [];
  }

  protected static function includeSetStatusAction(): bool
  {
    return false;
  }

  protected static function defineSortOptions(): array
  {
    return [
      'field' => Craft::t('craft-translator', 'Translation'),
      'title' => Craft::t('craft-translator', 'Source'),
    ];
  }

  protected function tableAttributeHtml(string $attribute): string
  {
    return $this->$attribute;
  }

  protected static function defineTableAttributes(): array
  {
    return [
      'field' => ['label' => Craft::t('craft-translator', 'Translation')],
    ];
  }

  protected function defineRules(): array
  {
    return array_merge(parent::defineRules(), [
      // ...
    ]);
  }

  public function canView(User $user): bool
  {
    return false;
  }

  public function canSave(User $user): bool
  {
    if (parent::canSave($user)) {
      return true;
    }
    // todo: implement user permissions
    return $user->can('saveTranslates');
  }

  public function canDuplicate(User $user): bool
  {
    return false;
  }

  public function canDelete(User $user): bool
  {
    return false;
  }

  public function canCreateDrafts(User $user): bool
  {
    return false;
  }

  protected static function defineExporters(string $source): array
  {
    $exporters[] = TranslateExport::class;
    return $exporters;
  }
  
  /**
   * @inheritdoc
   */
  protected static function defineSources(string $context = null): array
  {
    $sources = [];
    $user = Craft::$app->getUser();

    if ($user->checkPermission('craft-translator-viewTemplates')) {
      $templateSources = self::getTemplateSources(Craft::$app->path->getSiteTemplatesPath());
      sort($templateSources);
      $sources[] = ['heading' => Craft::t('craft-translator', 'Template Path')];

      $sources[] = [
        'label'    => Craft::t('craft-translator', 'All Templates'),
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

    if ($user->checkPermission('craft-translator-viewCategories')) {
      $sources[] = ['heading' => Craft::t('craft-translator', 'Category')];

      $language = Craft::$app->getSites()->getPrimarySite()->language;
      $fallbackLanguage = substr($language, 0, 2);

      $siteTranslationsPath = Craft::$app->getPath()->getSiteTranslationsPath() . DIRECTORY_SEPARATOR . $language;

      if (!is_dir($siteTranslationsPath)) {
        $siteTranslationsPath = Craft::$app->getPath()->getSiteTranslationsPath() . DIRECTORY_SEPARATOR . $fallbackLanguage;
      }

      $files = [];

      if (
        is_dir($siteTranslationsPath)
      ) {
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
        if ($user->checkPermission('craft-translator-viewCategories:' . $fileName)) {
          array_push($filesWithPermissions, $fileName);
        } else {
          array_push($filesWithoutPermissions, $fileName);
        }
      }

      if (empty($filesWithPermissions)) {
        $filesWithPermissions = $filesWithoutPermissions;
      }

      foreach ($filesWithPermissions as $fileName) {
        $sources['categoriessources:' . $fileName] = [
          'label' => ucfirst($fileName),
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
      if (Translation::$plugin->translation->hasStaticTranslations($template)) {
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
      if (Translation::$plugin->translation->hasStaticTranslations($template)) {
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

  /**
   * @inheritdoc
   */
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

    $elements = Translation::$plugin->translation->getTranslations($elementQuery);

    $attributes = Craft::$app->getElementIndexes()->getTableAttributes(static::class, $sourceKey);
    $site = Craft::$app->getSites()->getSiteById($elementQuery->siteId);
    $lang = Craft::$app->getI18n()->getLocaleById($site->language);
    $trans = Craft::t('craft-translator', 'Translation') . ': ' . ucfirst($lang->displayName);
    array_walk_recursive($attributes, function (&$attributes) use ($trans) {
      if ($attributes == Craft::t('craft-translator', 'Translation')) {
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

    Craft::$app->view->registerJs("$('table.fullwidth thead th').css('width', '50%');");

    $template = '_elements/' . $viewState['mode'] . 'view/' . ($includeContainer ? 'container' : 'elements');

    return Craft::$app->view->renderTemplate($template, $variables);
  }
}
