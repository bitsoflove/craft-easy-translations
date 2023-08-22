<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\records;

use craft\db\ActiveRecord;
use bitsoflove\translations\Constants;
use bitsoflove\translations\records\TranslationRecord;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class SourceRecord extends ActiveRecord
{
    public static function tableName()
    {
        return Constants::TABLE_SOURCES;
    }

    public function getTranslationRecord()
    {
        return $this->hasMany(TranslationRecord::class, ['id' => 'id']);
    }
}
