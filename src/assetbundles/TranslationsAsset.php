<?php

/**
 * @link      https://www.bitsoflove.be/
 * @copyright Copyright (c) 2022 bitsoflove
 */

namespace bitsoflove\translations\assetbundles;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Bits of Love
 * @package   craft-easy-translations
 * @since     1.0.0
 */
class TranslationsAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = "@bitsoflove/translations/assetbundles/src";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/index.js',
        ];

        $this->css = [
            'css/index.css',
        ];

        parent::init();
    }
}
