<?php
/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\elements\db;

use craft\elements\db\ElementQuery;
use craft\helpers\Db;

class TranslateQuery extends ElementQuery
{

    // General - Properties
    // =========================================================================
    public $id;
    public $path;
    public $translateId;

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
