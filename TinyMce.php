<?php
/**
 * @copyright (C) FIT-Media.com (fit-media.com), {@link http://tanitacms.net}
 * Date: 01.04.15, Time: 14:05
 *
 * @author        Dmitrij "m00nk" Sheremetjev <m00nk1975@gmail.com>
 * @package
 */

namespace m00nk\tinymce;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\JsExpression;
use yii\widgets\InputWidget;

class TinyMce extends InputWidget
{
	public $language = false;
	public $jsOptions = [];

	/**
	 * @var array|string|function Массив URL'ов страниц для редактора ссылок. Документация: https://www.tiny.cloud/docs-4x/plugins/link/#link_list
	 *
	 * Если передана строка, то она расценивается как URL, который вернет массив URL'ов страниц
	 * Если передана функция, то она будет вызвана для генерации массива URL'ов страниц
	 *
	 * Формат массива:
	 *      [
	 *          [ 'title'=>'My page 1', 'value'=>'http://www.tinymce.com' ],
	 *          [ 'title'=>'My page 2', 'value'=>'http://www.moxiecode.com' ],
	 *          [ 'title' => 'Sub Menu' , 'menu' => [
	 *                 [ 'title' => 'TinyMCE documentation', 'value' => 'https://www.tiny.cloud/docs/' ],
	 *                 [ 'title' => 'TinyMCE forum', 'value' => 'https://community.tinymce.com/']
	 *          ]
	 *      ]
	 */
	public $linkList = [];

	/**
	 * @var array массив классов ссылок, документация: https://www.tiny.cloud/docs-4x/plugins/link/#link_class_list
	 *
	 * Формат массива:
	 *      [
	 *          [ 'title'=>'Класс 1', 'value'=>'link-class-1' ],
	 *          [ 'title'=>'Мой класс 2', 'value'=>'my-link-2' ],
	 *      ]
	 */
	public $linkClassList = [];

	/**
	 * @var array массив параметров атрибута rel ссылок. Документация: https://www.tiny.cloud/docs-4x/plugins/link/#rel_list
	 *
	 * Формат массива:
	 *      [
	 *          [ 'title'=>'открывать в LightBox', 'value'=>'lightbox' ],
	 *          [ 'title'=>'Содержание', 'value'=>'toc' ],
	 *      ]
	 */
	public $linkRelList = [];

	/** @var bool|string путь к файлу со стилями для контекста в редакторе или FALSE еслин не нужен */
	public $contentCssFile = false;

	/**
	 * @var bool|array массив параметров виджетов FileManager или FALSE чтобы отключить файловые менеджеры
	 *
	 * ```
	 * [
	 *        // @var string идентификатор модуля файлового менеджера. Обязательное поле.
	 *        'fileManagerModuleId' => 'fileman',
	 *
	 *        // @var array параметры менеджера файлов для разного типа контента. Обязательное поле.
	 *        'medias' => [ // ключи - типы файлов (TinyMCE разделяет поддерживает три типа: file, image и media)
	 *            'file' => [  // если здесь передать FALSE, то менеджер для данного типа файлов не будет отображаться
	 *
	 *              // заголовок соответствующего окна менеджера файлов в TinyMCE
	 *                'title' => 'Файлы',
	 *
	 *              // остальные значения - параметры m00nk\filemanager\widgets\BaseWidget
	 *                'storages' => ['lsAdmin', 'flyYandex'],
	 *            ],
	 *
	 *            'image' => [
	 *                'title' => 'Изображения',
	 *                'storages' => ['lsAdmin', 'pixabay'],
	 *                'filetypes' => ['jpg', 'png', 'jpeg', 'gif']
	 *            ],
	 *
	 *            'media' => [
	 *                'title' => 'Медиа-файлы',
	 *                'storages' => ['lsAdmin'],
	 *                'filetypes' => ['avi', 'mov', 'mp4', 'flv', 'mp3', 'wma']
	 *            ]
	 *        ]
	 *    ]
	 * ```
	 */
	public $fileManager = false;

	/**
	 * @var bool флаг, определяющий, нужно ли переносить данные из редактора в исходный контрол перед валидацией.
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
			'insertdatetime_formats' => ["%d.%m.%Y", "%H:%M"],

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
			'link_class_list' => $this->linkClassList,
			'rel_list' => $this->linkRelList,
		];

		if($this->hasModel())
			echo Html::activeTextarea($this->model, $this->attribute, $this->options);
		else
			echo Html::textarea($this->name, $this->value, $this->options);

		$js = [];

		$view = $this->getView();

		$assetBundle = TinyMceAsset2::register($view);

		$this->jsOptions = array_merge($defaultJsOptions, $this->jsOptions);

		//-----------------------------------------
		// стили контента
		if($this->contentCssFile !== false)
			$this->jsOptions['content_css'] = $this->contentCssFile;
		else
			$this->jsOptions['content_css'] = $assetBundle->baseUrl.'/tinymce.content.css';

		//-----------------------------------------
		// настройки языка
		if($this->language === false) $this->language = substr(\Yii::$app->language, 0, 2);
		$langFile = "langs/{$this->language}.js";
		$assetBundle->js[] = $langFile;
		$this->jsOptions['language_url'] = $assetBundle->baseUrl."/{$langFile}";

		$id = $this->options['id'];
		$this->jsOptions['selector'] = '#'.$id;

		//-----------------------------------------
		// подключаем менеджер файлов
		if($this->fileManager !== false)
		{
			$fmModuleId = $this->fileManager['fileManagerModuleId'];
			$fmModule = Yii::$app->getModule($fmModuleId, $id);
			if($fmModule)
			{
				$fmOpts = $fmModule->getTinyMceOptions($this->fileManager['medias'], $this->id);
				$this->jsOptions = ArrayHelper::merge($this->jsOptions, $fmOpts);
			}
		}

		$js[] = 'tinymce.init('.Json::encode($this->jsOptions).');';
		if($this->triggerSaveOnBeforeValidateForm)
			$js[] = "$('#{$id}').parents('form').on('beforeValidate', function() { tinymce.triggerSave(); });";

		$view->registerJs(implode("\n", $js));
	}
}