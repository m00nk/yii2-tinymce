<?php

namespace m00nk\tinymce;

use Yii;
use yii\helpers\Json;
use yii\helpers\Url;
use \m00nk\elfinder\PathController;
use yii\web\HttpException;
use yii\web\JsExpression;

class ElFinderController extends PathController
{
	public function actionManager()
	{
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

			'resizable' => false
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

		$options['getFileCallback'] = new JsExpression('function(file){ window.parent.tinymce_filenav_add_file(file); window.close(); }');

		if(!isset($options['lang']))
			$options['lang'] = Yii::$app->language;

		if(!empty($this->disabledCommands))
			$options['commands'] = new JsExpression('ElFinderGetCommands('.Json::encode($this->disabledCommands).')');


		return $this->renderFile(__DIR__."/views/manager.php", ['options' => $options]);
	}
}