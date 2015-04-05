<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com), {@link http://tanitacms.net}
 * Date: 01.04.15, Time: 14:05
 *
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */

namespace m00nk\tinymce;

use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;

class TinyMce extends InputWidget
{
	public $language = false;
	public $options = [];

	public function run()
	{
		if($this->hasModel())
		{
			echo Html::activeTextarea($this->model, $this->attribute, $this->options);
		}
		else
		{
			echo Html::textarea($this->name, $this->value, $this->options);
		}

		$js = [];
		$view = $this->getView();

		TinyMceAsset::register($view);

		$id = $this->options['id'];

		$this->clientOptions['selector'] = "#$id";
		// @codeCoverageIgnoreStart
		if($this->language !== null)
		{
			$langFile = "langs/{$this->language}.js";
			$langAssetBundle = TinyMceLangAsset::register($view);
			$langAssetBundle->js[] = $langFile;
			$this->clientOptions['language_url'] = $langAssetBundle->baseUrl."/{$langFile}";
		}
		// @codeCoverageIgnoreEnd

		$options = Json::encode($this->clientOptions);

		$js[] = "tinymce.init($options);";
		if($this->triggerSaveOnBeforeValidateForm)
		{
			$js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";
		}
		$view->registerJs(implode("\n", $js));
	}

}