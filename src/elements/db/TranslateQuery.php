<?php

namespace bitsoflove\translation\elements\db;

use Craft;
use craft\elements\db\ElementQuery;

/**
 * Translate query
 */
class TranslateQuery extends ElementQuery
{
    public mixed $id;
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
