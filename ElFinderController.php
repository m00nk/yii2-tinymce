<?php

namespace m00nk\tinymce;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
//- use \m00nk\elfinder\PathController;
use mihaildev\elfinder\PathController;
use yii\web\HttpException;
use yii\web\JsExpression;

class ElFinderController extends PathController
{
	public function actionManager()
	{
		// документация: https://github.com/Studio-42/elFinder/wiki/Client-configuration-options-2.1

		$localPath = Yii::$app->session->get(Yii::$app->request->get('sc'));

		if(!$localPath) throw new HttpException(403);

		$options = [
			'url' => Url::toRoute([
				'connect',
				'path' => $localPath
			]),

			'customData' => [
				Yii::$app->request->csrfParam => Yii::$app->request->csrfToken
			],

			'resizable' => false,

			'rememberLastDir' => true,

		];

		if(isset($_GET['filter']))
		{
			if(is_array($_GET['filter']))
				$options['onlyMimes'] = $_GET['filter'];
			else
				$options['onlyMimes'] = [$_GET['filter']];
		}

		if(isset($_GET['lang']))
			$options['lang'] = $_GET['lang'];

		$options['getFileCallback'] = new JsExpression('elFinderFileCallback');

		if(!isset($options['lang']))
			$options['lang'] = Yii::$app->language;

		if(!empty($this->disabledCommands))
			$options['commands'] = new JsExpression('ElFinderGetCommands('.Json::encode($this->disabledCommands).')');


		return $this->renderFile(__DIR__."/views/manager.php", ['options' => $options]);
	}
}