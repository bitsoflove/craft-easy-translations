<?php

/**
 * Translation plugin for Craft CMS 3.x/4.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\records;

use craft\db\ActiveRecord;
use bitsoflove\translation\Constants;
use bitsoflove\translation\records\SourceRecord;

/**
 *
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class TranslationRecord extends ActiveRecord
{
    public static function tableName()
    {
        return Constants::TABLE_TRANSLATIONS;
    }

    public function getSource()
    {
        return $this->hasOne(SourceRecord::class, ['id' => 'id'])->via('source');
    }
}
