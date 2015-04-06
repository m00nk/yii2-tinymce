<?php

namespace m00nk\tinymce;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use \mihaildev\elfinder\PathController;
use yii\web\JsExpression;

class ElFinderController extends PathController
{
	public function actionManager()
	{
		$connectRoute = ['connect', 'path' => Yii::$app->request->getQueryParam('path', '')];
		$options = [
			'url' => Url::toRoute($connectRoute),
			'customData' => [
				Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
			],
			'resizable' => false
		];

		if(isset($_GET['CKEditor']))
		{
			$options['getFileCallback'] = new JsExpression('function(file){ '.
				'window.opener.CKEDITOR.tools.callFunction('.Json::encode($_GET['CKEditorFuncNum']).', file.url); '.
				'window.close(); }');

			$options['lang'] = $_GET['langCode'];
		}

		if(isset($_GET['filter']))
		{
			if(is_array($_GET['filter']))
				$options['onlyMimes'] = $_GET['filter'];
			else
				$options['onlyMimes'] = [$_GET['filter']];
		}

		if(isset($_GET['lang']))
			$options['lang'] = $_GET['lang'];

		$options['getFileCallback'] = new JsExpression('function(file){ window.parent.tinymce_filenav_add_file(file); window.close(); }');

		if(!isset($options['lang']))
			$options['lang'] = Yii::$app->language;

		if(!empty($this->disabledCommands))
			$options['commands'] = new JsExpression('ElFinderGetCommands('.Json::encode($this->disabledCommands).')');


		return $this->renderFile(__DIR__."/views/manager.php", ['options' => $options]);
	}
}