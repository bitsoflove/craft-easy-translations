<?php
/**
 * Translation plugin for Craft CMS 3.x
 *
 * Plugin to manage translations. Export and import functionality.
 *
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translation\models;

use bitsoflove\translation\Translation;

use Craft;
use craft\base\Model;

/**
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class TranslationModel extends Model
{
    public $id;

    public $language;

    public $translation;

    public function rules()
    {
        return [
            [['id', 'language'], 'required'],
            ['id', 'integer'],
            [['translation', 'language'], 'string']
        ];
    }
}
