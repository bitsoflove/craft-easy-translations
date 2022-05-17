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

use craft\base\Model;

/**
 * @author    bitsoflove
 * @package   Translation
 * @since     0.0.1
 */
class SourceModel extends Model
{
    public $category;

    public $message;

    public function rules()
    {
        return [
            ['message', 'required'],
            [['category', 'message'], 'string']
        ];
    }
}
