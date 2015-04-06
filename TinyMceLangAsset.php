<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com), {@link http://tanitacms.net}
 * Date: 01.04.15, Time: 14:09
 *
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */
namespace m00nk\tinymce;

use yii\web\AssetBundle;

class TinyMceLangAsset extends AssetBundle
{
    public $sourcePath = '@vendor/m00nk/yii2-tinymce/assets';

    public $depends = [
        'm00nk\tinymce\TinyMceAsset'
    ];
}
