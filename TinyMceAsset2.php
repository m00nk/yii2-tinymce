<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com)
 * Date: 01.04.15, Time: 14:09
 *
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */
namespace m00nk\tinymce;

use yii\web\AssetBundle;

/**
 * Бандл для подключения кастомных плагинов и стилей
 */
class TinyMceAsset2 extends AssetBundle
{
    public $sourcePath = '@vendor/m00nk/yii2-tinymce/assets';

    public $depends = [
        'm00nk\tinymce\TinyMceAsset'
    ];

	public $css = [
		'linkManager/link-manager.css'
	];

    public $js = [
	    'linkManager/link-manager.js'
    ];
}
