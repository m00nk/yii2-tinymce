<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com), {@link http://tanitacms.net}
 * Date: 01.04.15, Time: 14:05
 *
 * @author        Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */

namespace m00nk\tinymce;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

class TinyMce extends InputWidget
{
	public $language = false;
	public $jsOptions = [];

	/** @var array Список ссылок
	 *
	 * Формат:
	 *      [
	 *          [ 'title'=>'My page 1', 'value'=>'http://www.tinymce.com' ],
	 *          [ 'title'=>'My page 2', 'value'=>'http://www.moxiecode.com' ],
	 *      ]
	 */
	public $linkList = [];

	/** @var bool|string путь к файлу со стилями для контекста в редакторе или FALSE еслин не нужен */
	public $contentCssFile = false;

	//todo-m00nk DELETE IT
	/** @var bool|string путь к папке загружаемых файлов относительно папки, заданной в настройках elFinder или false если нужно отключить файловый менеджер */
	public $filesBasePath = false;

	/** @var bool|array массив параметров виджетов FileManager */
	public $fileManager = [

		/** @var string идентификатор модуля файлового менеджера */
		'fileManagerModuleId' => 'fileman',

		/** @var array массив параметров виджетов FileManager для разных типов контента или FALSE чтобы отключить менеджер файлов */
		'medias' => [
			'file' => [
				'storages' => ['lsAdmin', 'flyYandex'],
			],

			'image' => [
				'storages' => ['lsAdmin', 'pixabay'],
				'filetypes' => ['jpg', 'png', 'jpeg', 'gif']
			],

			'media' => [
				'storages' => ['lsAdmin'],
				'filetypes' => ['avi', 'mov', 'mp4', 'flv']
			]
		]
	];

	/** @var bool флаг, определяющий, нужно ли переносить данные из редактора в исходный контрол перед валидацией.
	 * Должен быть TRUE, если используется валидация на стороне клиента, иначе при сабмите форма будет валидировать неизмененный контент.
	 */
	public $triggerSaveOnBeforeValidateForm = true;

	public function run()
	{
		/* static */
		$defaultJsOptions = [
			'plugins' => [
				"advlist autolink lists link charmap print preview anchor image",
				"searchreplace visualblocks code fullscreen",
				"insertdatetime media table contextmenu paste",
				'autosave hr nonbreaking pagebreak textcolor visualchars',
//				'autoresize bbcode save spellchecker wordcount importcss emoticons'
			],

			'theme' => 'modern',

			'toolbar' => [
				'print preview fullscreen code visualblocks visualchars | undo redo cut copy paste pastetext searchreplace | removeformat forecolor backcolor 
				bold italic underline strikethrough blockquote | styleselect formatselect',
				'link unlink anchor | bullist numlist outdent indent alignleft aligncenter alignright alignjustify| image media hr nonbreaking pagebreak 
				charmap insertdatetime table | restoredraft help'
			],

			// визуальное масштабирование картинок, таблиц и медиа-объектов
			'object_resizing' => true,

			// расширенный диалог вставки картинок
			'image_advtab' => true,

			// когда юзер жмет ТАБ, редактор вставит три неразрывных пробела
			'nonbreaking_force_tab' => true,

			// HTML-код вставляемый кнопкой "Разрыв страницы"
			'pagebreak_separator' => '<hr class="page-break"><!-- page break --></hr>',

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

			'mode' => 'exact',

			// список ссылок
			'link_list' => $this->linkList,
		];

		if($this->hasModel())
			echo Html::activeTextarea($this->model, $this->attribute, $this->options);
		else
			echo Html::textarea($this->name, $this->value, $this->options);

		$js = [];

		$view = $this->getView();

		TinyMceAsset::register($view);
		TinyMcePluginsAsset::register($view);

		$this->jsOptions = array_merge($defaultJsOptions, $this->jsOptions);

		//-----------------------------------------
		// стили контента
		if($this->contentCssFile !== false)
			$this->jsOptions['content_css'] = $this->contentCssFile;
		else
		{
			$assetBundle = TinyMceContentStylesAsset::register($view);
			$this->jsOptions['content_css'] = $assetBundle->baseUrl.'/tinymce.content.css';
		}

		//-----------------------------------------
		// настройки языка
		if($this->language === false) $this->language = substr(\Yii::$app->language, 0, 2);
		$langFile = "langs/{$this->language}.js";
		$langAssetBundle = TinyMceLangAsset::register($view);
		$langAssetBundle->js[] = $langFile;
		$this->jsOptions['language_url'] = $langAssetBundle->baseUrl."/{$langFile}";

		$id = $this->options['id'];
		$this->jsOptions['selector'] = '#'.$id;

		//-----------------------------------------
		// подключаем менеджер файлов
		if($this->fileManager !== false)
		{
			$_ass = Yii::$app->assetManager->publish('@vendor/m00nk/yii2-file-manager/assets');
			$this->registerJsFile($_ass[1].'/js/fileman.js');

			TinyMceFileManagerAsset::register($view);
			$this->jsOptions['file_browser_callback'] = new JsExpression('tinymce_filenav');

			$_m = [];
			foreach($this->fileManager['medias'] as $type => $wOpts)
			{
				$_m[$type] = array_merge($wOpts, [
					'hash' => \m00nk\filemanager\FileManager::registerFakeWidget(
						$id,
						array_key_exists('storages', $wOpts) ? $wOpts['storages'] : [],
						array_key_exists('filetypes', $wOpts) ? $wOpts['filetypes'] : null,
						array_key_exists('maxFileSize', $wOpts) ? $wOpts['maxFileSize'] : null,
						array_key_exists('options', $wOpts) ? $wOpts['options'] : []
					),
				]);
			}

			$this->jsOptions['fileManager'] = [
				'medias' => $_m,
				'url' => Url::to(['/'.$this->fileManager['fileManagerModuleId'].'/rest'], true),
				'csrf' => Yii::$app->request->csrfToken,
			];

//			$sessionCode = md5(time().rand(100000, 999999).$this->filesBasePath.rand(100000, 999999));
//
//			\Yii::$app->session->set($sessionCode, $this->filesBasePath);
//
//			TinyMceFileManagerAsset::register($view);
//			$this->jsOptions['file_browser_callback'] = new JsExpression('tinymce_filenav');
//			$this->jsOptions['fileManagerPath'] = '/elfinder/manager?lang='.$this->language.'&sc='.$sessionCode; // &callback=w0 &filter=image
		}

		$js[] = 'tinymce.init('.Json::encode($this->jsOptions).');';
		if($this->triggerSaveOnBeforeValidateForm)
			$js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";

		$view->registerJs(implode("\n", $js));
	}
}