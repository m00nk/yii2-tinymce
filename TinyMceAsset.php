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

class TinyMceAsset extends AssetBundle
{
	public $sourcePath = '@vendor/tinymce/tinymce';

	public function init()
	{
		parent::init();
		$this->js[] = YII_DEBUG ? 'tinymce.js' : 'tinymce.min.js';
	}

}