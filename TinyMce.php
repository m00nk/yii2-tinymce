<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com), {@link http://tanitacms.net}
 * Date: 01.04.15, Time: 14:05
 *
 * @author Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */

namespace m00nk\tinymce;

use yii\helpers\ArrayHelper;
use yii\web\JsExpression;
use yii\widgets\InputWidget;
use yii\helpers\Html;
use yii\helpers\Json;

class TinyMce extends InputWidget
{
	public $language = false;
	public $jsOptions = [];

	/** @var bool|string путь к папке загружаемых файлов относительно корня сайта или false если нужно отключить файловый менеджер  */
	public $filesBasePath = false;

	public function run()
	{
		static $defaultJsOptions = [
			'plugins' => [
				"advlist autolink lists link charmap print preview anchor image",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste"
			],

			'toolbar' => "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",

			// визуальное масштабирование картинок, таблиц и медиа-объектов
			'object_resizing' => true,

			// расширенный диалог вставки картинок
			'image_advtab' => true,

			// когда юзер жмет ТАБ, редактор вставит три неразрывных пробела
			'nonbreaking_force_tab' => true,

			// HTML-код вставляемый кнопкой "Разрыв страницы"
			'pagebreak_separator' => '<hr class="page-break" id="page_break"><!-- page break --></hr>',

			// форматы для команд вставки даты и времени, детали: http://www.tinymce.com/wiki.php/Plugin:insertdatetime
			'insertdatetime_formats' => array("%d.%m.%Y", "%H:%M"),

			// XHTML compliance
			'convert_fonts_to_spans' => true,

			// нажатие Enter создает новый параграф
			'force_p_newlines' => true,

			// включаем относительные УРЛы
			'relative_urls' => true,

			// если включить, то протокол и хост будут вырезаться из УРЛов, создаваемых менеджером фалов. Работает только если relative_urls==false
			'remove_script_host' => false,

			// avoid problems with browser tabs
			'fullscreen_new_window' => false,

			// ?????
			'convert_urls' => false,

			'mode' => 'exact'
		];

		$this->jsOptions = ArrayHelper::merge($defaultJsOptions, $this->jsOptions);


		if($this->hasModel())
			echo Html::activeTextarea($this->model, $this->attribute, $this->options);
		else
			echo Html::textarea($this->name, $this->value, $this->options);

		$js = [];

		$view = $this->getView();

		TinyMceAsset::register($view);

		//-----------------------------------------
		// настройки языка
		if($this->language === null) $this->language = \Yii::$app->language;
		$langFile = "langs/{$this->language}.js";
		$langAssetBundle = TinyMceLangAsset::register($view);
		$langAssetBundle->js[] = $langFile;
		$this->jsOptions['language_url'] = $langAssetBundle->baseUrl."/{$langFile}";

		//-----------------------------------------
		// подключаем менеджер файлов
		if($this->filesBasePath !== false)
		{
			TinyMceFileManagerAsset::register($view);
			$this->jsOptions['file_browser_callback'] = new JsExpression('tinymce_filenav');
			$this->jsOptions['fileManagerPath'] = '/elfinder/manager?lang='.$this->language.'&callback=elFinderTest'; // &callback=w0 &filter=image
		}

		$id = $this->options['id'];
		$this->jsOptions['selector'] = '#'.$id;

		$js[] = 'tinymce.init('.Json::encode($this->jsOptions).');';
//		if($this->triggerSaveOnBeforeValidateForm)
//		{
//			$js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";
//		}
		$view->registerJs(implode("\n", $js));
	}

}