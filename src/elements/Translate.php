<?php

/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\elements;

use Craft;
use craft\base\Element;
use craft\elements\db\ElementQueryInterface;
use craft\web\ErrorHandler;
use bitsoflove\translation\elements\db\TranslateQuery;
use bitsoflove\translation\elements\exporters\TranslateExport;
use bitsoflove\translation\Translation;
use craft\helpers\FileHelper;

class Translate extends Element
{
    public $source;
    public $translateId;
    public $translation;
    public $path;
    public $siteId;
    public $field;

    /**
     * Return element type name.
     *
     * @return string
     */
    public function getName()
    {
        return Craft::t('translation', 'Translations');
    }

    /**
     * Use the name as the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->source;
        } catch (\Exception $e) {
            ErrorHandler::convertExceptionToError($e);
        }
    }

    /**
     * @inheritdoc
     */
    public static function isLocalized(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasStatuses(): bool
    {
        return false;
    }

    public static function find(): ElementQueryInterface
    {
        return new TranslateQuery(get_called_class());
    }

    /**
     * @inheritdoc
     */
    protected static function defineTableAttributes(): array
    {
        $attributes['source'] = ['label' => Craft::t('translation', 'Source')];

        $attributes['field'] = ['label' => Craft::t('translation', 'Translation')];

        return $attributes;
    }

    /**
     * Returns the default table attributes.
     *
     * @param string $source
     *
     * @return array
     */
    protected static function defineDefaultTableAttributes(string $source): array
    {
        return ['source', 'field'];
    }

    /**
     * Don't encode the attribute html.
     *
     * @param string           $attribute
     *
     * @return string
     */
    protected function tableAttributeHtml(string $attribute): string
    {
        return $this->$attribute;
    }

    /**
     * @inheritdoc
     */
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
            'source' => Craft::t('app', 'Source'),
            'field' => Craft::t('app', 'Translation'),
        ];
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

        $templateSources = self::getTemplateSource(Craft::$app->path->getSiteTemplatesPath());

        $sources[] = [
            'label'    => Craft::t('translation', 'All Templates'),
            'key'      => 'all-templates:',
            'criteria' => [
                'path' => [
                    Craft::$app->path->getSiteTemplatesPath()
                ]
            ],
            'nested' => $templateSources
        ];

        return $sources;
    }

    private static function getTemplateSource($path)
    {
        $templateSources = [];

        $options = [
            'recursive' => false,
            'only' => ['*.html', '*.twig', '*.js', '*.json', '*.atom', '*.rss'],
            'except' => ['vendor/', 'node_modules/']
        ];

        $files = FileHelper::findFiles($path, $options);

        foreach ($files as $template) {
            $fileName = basename($template);

            $cleanTemplateKey = str_replace('/', '*', $template);
            $templateSources['templatessources:' . $fileName] = [
                'label' => $fileName,
                'key' => 'templates:' . $cleanTemplateKey,
                'criteria' => [
                    'path' => [
                        $template
                    ],
                ],
            ];
        }

        $options = [
            'recursive' => false,
            'except' => ['vendor/', 'node_modules/']
        ];

        $directories = FileHelper::findDirectories($path, $options);

        foreach ($directories as $template) {
            $fileName = basename($template);

            $cleanTemplateKey = str_replace('/', '*', $template);

            $nestedSources = self::getTemplateSource($template);

            $templateSources['templatessources:' . $fileName] = [
                'label' => $fileName . '/',
                'key' => 'templates:' . $cleanTemplateKey,
                'criteria' => [
                    'path' => [
                        $template
                    ],
                ],
                'nested' => $nestedSources,
            ];
        }

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

        $elements = Translation::$plugin->translation->getTemplateTranslationsByQuery($elementQuery);

        $attributes = Craft::$app->getElementIndexes()->getTableAttributes(static::class, $sourceKey);
        $site = Craft::$app->getSites()->getSiteById($elementQuery->siteId);
        $lang = Craft::$app->getI18n()->getLocaleById($site->language);
        $trans = 'Translation: ' . ucfirst($lang->displayName);
        array_walk_recursive($attributes, function (&$attributes) use ($trans) {
            if ($attributes == 'Translation') {
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
