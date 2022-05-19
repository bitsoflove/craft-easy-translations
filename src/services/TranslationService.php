<?php

/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\services;

use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;
use craft\helpers\ElementHelper;
use bitsoflove\translation\records\TranslationRecord;
use bitsoflove\translation\records\SourceRecord;
use bitsoflove\translation\elements\Translate;
use craft\elements\db\ElementQueryInterface;

/**
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class TranslationService extends Component
{
    private $_expressions = [];

    public function init()
    {
        parent::init();

        // Only twig, php and html files
        $this->_expressions = [
            // Craft::t('category', '..')
            'php' => [
                'matchPosition' => 3,
                'regex' => [
                    '/Craft::(t|translate)\(.*?\'(.*?)\'.*?\,.*?\'(.*?)\'.*?\)/',
                    '/Craft::(t|translate)\(.*?"(.*?)".*?\,.*?"(.*?)".*?\)/',
                ]
            ],

            //  '...'|t('category')
            'twig' => [
                'matchPosition' => 1,
                'regex' => [
                    '/\(?\'((?:[^\']|\\\\\')*)\'\)?\s*\|\s*t(?:ranslate)?\b/',
                    '/\(?"((?:[^"]|\\\\")*)"\)?\s*\|\s*t(?:ranslate)?\b/',
                ]
            ]
        ];

        $this->_expressions['html'] = $this->_expressions['twig'];
    }

    public function getTemplateTranslationsByQuery(ElementQueryInterface $query, $category = 'site')
    {
        if (!is_array($query->path)) {
            $query->path = [$query->path];
        }

        $translations = [];
        $elementId = 0;
        $language = Craft::$app->getSites()->getSiteById($query->siteId)->language;
        $currentTranslations = $this->getCurrentTranslations($category, $language);

        foreach ($query->path as $path) {
            if (is_dir($path)) {
                $options = [
                    'recursive' => true,
                    'only' => ['*.php', '*.html', '*.twig'],
                    'except' => ['vendor/', 'node_modules/']
                ];

                $files = FileHelper::findFiles($path, $options);

                foreach ($files as $file) {

                    $elements = $this->processTemplateByQuery($path, $file, $query, $category, $language, $elementId, $currentTranslations);

                    $translations = array_merge($translations, $elements);
                }
            } elseif (file_exists($path)) {

                $elements = $this->processTemplateByQuery($path, $path, $query, $category, $language, $elementId, $currentTranslations);

                $translations = array_merge($translations, $elements);
            }
        }

        $translations = $this->filterTranslations($translations, $query);

        return $translations;
    }

    private function filterTranslations($translations, $query)
    {
        $sort = $query->orderBy;

        if (array_key_first($sort) == 'source') {
            if (array_values($sort)[0] == 'asc') {
                ksort($translations, 10);
            } else {
                krsort($translations, 10);
            }
        } else {
            if (array_values($sort)[0] == 'asc') {
                usort($translations, function ($a, $b) {
                    return strcasecmp($a->translation, $b->translation);
                });
            } else {
                usort($translations, function ($a, $b) {
                    return strcasecmp($b->translation, $a->translation);
                });
            }
        }

        return array_slice($translations, $query->offset, $query->limit);
    }

    private function processTemplateByQuery($path, $file, ElementQueryInterface $query, $category, $language, &$elementId, $currentTranslations)
    {
        $translations = [];
        $contents = file_get_contents($file);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $expressions = $this->_expressions[$extension];
        foreach ($expressions['regex'] as $regex) {
            if (preg_match_all($regex, $contents, $matches)) {
                $matchPosition = $expressions['matchPosition'];
                foreach ($matches[$matchPosition] as $source) {
                    $elementId++;
                    $translateId = ElementHelper::generateSlug($source);

                    if (array_key_exists($source, $currentTranslations)) {
                        $translation = Craft::t($category, $source, null, $language);
                    } else {
                        $translation = '';
                    }

                    $field = Craft::$app->getView()->renderTemplate('_includes/forms/text', [
                        'id' => $translateId,
                        'name' => 'translation[' . $source . ']',
                        'value' => $translation,
                        'placeholder' => $translation,
                    ]);

                    $element = new Translate([
                        'id' => $elementId,
                        'translateId' => $translateId,
                        'source' => $source,
                        'translation' => $translation,
                        'field' => $field,
                        'path' => $path,
                    ]);

                    if ($query->search && !stristr($element->source, $query->search) && !stristr($element->translation, $query->search)) {
                        continue;
                    }

                    $translations[$element->source] = $element;
                }
            }
        }

        return $translations;
    }

    public function getTemplateTranslations($category = 'site', $language)
    {
        $translations = [];

        $path = Craft::$app->path->getSiteTemplatesPath();

        $options = [
            'recursive' => true,
            'only' => ['*.php', '*.html', '*.twig'],
            'except' => ['vendor/', 'node_modules/']
        ];

        $files = FileHelper::findFiles($path, $options);

        foreach ($files as $file) {
            $occurences = $this->processTemplate($file, $language, $category);

            $translations = array_merge($translations, $occurences);
        }

        return $translations;
    }

    public function getStaticTranslations($category, $language)
    {
        $file = $this->getSitePath($language, $category);
        $translations = [];

        if ($current = @include($file)) {
            $translations = array_merge($current, $translations);
        }

        return $translations;
    }

    public function getDbTranslations($category, $language)
    {
        $result = SourceRecord::find()
            ->select(['message', 'translation'])
            ->innerJoinWith('translationRecord', true)
            ->where(['category' => $category])
            ->andWhere(['language' => $language])
            ->asArray()
            ->all();

        $translations = [];

        foreach ($result as $row) {
            $translations[$row['message']] = $row['translation'];
        }

        return $translations;
    }

    public function getTranslations($category, $language)
    {
        $templateTranslations = $this->getTemplateTranslations($category, $language);

        $translations = $this->getCurrentTranslations($category, $language);

        // Adds sources found in template but not in db or static
        foreach (array_keys($templateTranslations) as $source) {
            if (!array_key_exists($source, $translations)) {
                $translations[$source] = '';
            }
        }

        return $translations;
    }

    private function getCurrentTranslations($category, $language)
    {
        $staticTranslations = $this->getStaticTranslations($category, $language);
        $dbTranslations = $this->getDbTranslations($category, $language);

        $translations = array_merge($staticTranslations, $dbTranslations);

        return $translations;
    }

    private function processTemplate($file, $language, $category)
    {
        $translations = [];
        $contents = file_get_contents($file);
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $fileOptions = $this->_expressions[$extension];
        foreach ($fileOptions['regex'] as $regex) {
            if (preg_match_all($regex, $contents, $matches)) {
                $matchPosition = $fileOptions['matchPosition'];

                foreach ($matches[$matchPosition] as $source) {
                    $translation = Craft::t($category, $source, null, $language);

                    $translations[$source] = $translation;
                }
            }
        }

        return $translations;
    }

    public function save($translations, $siteId, $category = 'site')
    {
        $language = Craft::$app->getSites()->getSiteById($siteId)->language;

        foreach ($translations as $source => $translation) {
            $oldSource = SourceRecord::findOne(['category' => $category, 'message' => $source]);

            if ($oldSource == null) {
                $oldSource = new SourceRecord;
                $oldSource->message = $source;
                $oldSource->insert();
            }

            $oldTranslation = TranslationRecord::findOne(['id' => $oldSource->id, 'language' => $language]);

            if ($oldTranslation == null) {
                if (!empty($translation)) {
                    if (Craft::t($category, $source, null, $language) != $translation) {
                        $newTranslation = new TranslationRecord;
                        $newTranslation->id = $oldSource->id;
                        $newTranslation->language = $language;
                        $newTranslation->translation = $translation;
                        $newTranslation->insert();
                    }
                }
            } else {
                if ($oldTranslation->translation != $translation) {
                    if (!empty($translation)) {
                        $oldTranslation->translation = $translation;
                        $oldTranslation->update();
                    } else {
                        $oldTranslation->delete();
                    }
                }
            }
        }
    }

    public function getSitePath($locale, $category)
    {
        $sitePath = Craft::$app->getPath()->getSiteTranslationsPath();
        $file = $sitePath . DIRECTORY_SEPARATOR . $locale . DIRECTORY_SEPARATOR . $category . '.php';

        return $file;
    }
}
