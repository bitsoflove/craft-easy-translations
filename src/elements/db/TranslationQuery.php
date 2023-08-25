<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\elements\db;

use Craft;
use craft\elements\db\ElementQuery;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class TranslationQuery extends ElementQuery
{
    public $id;
    public $path;
    public $category;

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        parent::__set($name, $value);
    }

    /**
     * @inheritdoc
     */
    public function path($value)
    {
        $this->path = $value;
    }
    protected function beforePrepare(): bool
    {
        // todo: join the `translates` table
        // $this->joinElementTable('translates');

        // todo: apply any custom query params
        // ...

        return false;
    }
}
