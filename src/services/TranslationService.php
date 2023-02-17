<?php

/**
 * Translation plugin for Craft CMS 3.x/4.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\services;

use bitsoflove\translation\elements\Translate;
use Craft;
use craft\base\Component;
use craft\helpers\FileHelper;
use craft\helpers\ElementHelper;
use bitsoflove\translation\records\TranslationRecord;
use bitsoflove\translation\records\SourceRecord;
use craft\elements\db\ElementQueryInterface;

/**
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class TranslationService extends Component
{
    private $_expressions = [];

    public function init(): void
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

    public function getTranslations(ElementQueryInterface $query)
    {
        if (!empty($query->path)) {
            $translations = $this->getTemplateTranslations($query);
        } else {
            $translations = $this->getCategoryTranslations($query);
        }

        $translations = $this->filterTranslations($translations, $query);
        return $translations;
    }

    private function getTemplateTranslations(ElementQueryInterface $query)
    {
        if (!is_array($query->path)) {
            $query->path = [$query->path];
        }

        $translations = [];
        $elementId = 0;
        $language = Craft::$app->getSites()->getSiteById($query->siteId)->language;
        $currentTranslations = $this->getCurrentTranslations($query->category, $language);

        foreach ($query->path as $path) {
            if (is_dir($path)) {
                $options = [
                    'recursive' => true,
                    'only' => ['*.php', '*.html', '*.twig'],
                    'except' => ['vendor/', 'node_modules/']
                ];

                $files = FileHelper::findFiles($path, $options);

                foreach ($files as $file) {

                    $elements = $this->processTemplate($file, $query, $language, $elementId, $currentTranslations);

                    $translations = array_merge($translations, $elements);
                }
            } elseif (file_exists($path)) {

                $elements = $this->processTemplate($path, $query, $language, $elementId, $currentTranslations);

                $translations = array_merge($translations, $elements);
            }
        }

        return $translations;
    }

    private function filterTranslations($translations, $query)
    {
        $sort = $query->orderBy;

        if (array_key_first($sort) == 'title') {
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

        return $translations;
    }

    private function processTemplate($file, ElementQueryInterface $query, $language, &$elementId, $currentTranslations)
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

                    if (array_key_exists($source, $currentTranslations)) {
                        $translation = Craft::t($query->category, $source, null, $language);
                    } else {
                        $translation = '';
                    }

                    $element = $this->createTranslateElement($source, $translation, $elementId);

                    if ($query->search && !stristr($element->title, $query->search) && !stristr($element->translation, $query->search)) {
                        continue;
                    }

                    $translations[$element->title] = $element;
                }
            }
        }

        return $translations;
    }

    private function createTranslateElement($source, $translation, $elementId)
    {
        $translateSlug = ElementHelper::generateSlug($source);

        $field = Craft::$app->getView()->renderTemplate('_includes/forms/text', [
            'id' => $translateSlug,
            'name' => 'translation[' . $source . ']',
            'value' => $translation,
            'placeholder' => $translation,
        ]);

        $element = new Translate([
            'id' => $elementId,
            'title' => $source,
            'translation' => $translation,
            'field' => $field,
        ]);

        return $element;
    }

    public function getCategoryTranslations(ElementQueryInterface $query)
    {
        $language = Craft::$app->getSites()->getSiteById($query->siteId)->language;
        $currentTranslations = $this->getCurrentTranslations($query->category, $language);
        $elementId = 0;
        $translations = [];

        foreach ($currentTranslations as $source => $translation) {
            $elementId++;

            $element = $this->createTranslateElement($source, $translation, $elementId);

            if ($query->search && !stristr($element->title, $query->search) && !stristr($element->translation, $query->search)) {
                continue;
            }

            $translations[$element->title] = $element;
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

    private function getCurrentTranslations($category, $language)
    {
        $staticTranslations = $this->getStaticTranslations($category, $language);
        $dbTranslations = $this->getDbTranslations($category, $language);

        $translations = array_merge($staticTranslations, $dbTranslations);

        return $translations;
    }

    public function save($translations, $siteId, $category)
    {
        $language = Craft::$app->getSites()->getSiteById($siteId)->language;

        foreach ($translations as $source => $translation) {
            $oldSource = SourceRecord::findOne(['category' => $category, 'message' => $source]);

            if ($oldSource == null) {
                $oldSource = new SourceRecord;
                $oldSource->category = $category;
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
