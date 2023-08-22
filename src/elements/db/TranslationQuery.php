<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\elements\db;

use craft\elements\db\ElementQuery;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class TranslationQuery extends ElementQuery
{

    // General - Properties
    // =========================================================================
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

    /**
     * @inheritdoc
     */
    protected function beforePrepare(): bool
    {
        return false;
    }
}
