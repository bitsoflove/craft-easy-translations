<?php
/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\records;

use craft\db\ActiveRecord;
use bitsoflove\translation\Constants;
use bitsoflove\translation\records\TranslationRecord;
/**
 *
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class SourceRecord extends ActiveRecord
{
    public static function tableName()
    {
        return Constants::TABLE_SOURCE;
    }

    public function getTranslationRecord()
    {
        return $this->hasMany(TranslationRecord::class, ['id' => 'id']);
    }
}
