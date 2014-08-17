<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Description of DropdownLink
 *
 * @author Misbahul D Munir (mdmunir) <misbahuldmunir@gmail.com>
 */
class DropdownLink extends \yii\widgets\InputWidget
{
    public $items = [];
    public $links = [];
    public $paramName = '';
    public $route = '';
    public $params;

    public function run()
    {
        $this->registerClientScript();
        if ($this->hasModel()) {
            echo Html::activeDropDownList($this->model, $this->attribute, $this->items, $this->options);
        } else {
            if ($this->value === null) {
                $params = $this->params !== null ? $this->params : Yii::$app->getRequest()->getQueryParams();
                $this->value = isset($params[$this->paramName]) ? $params[$this->paramName] : null;
            }
            echo Html::dropDownList($this->name, $this->value, $this->items, $this->options);
        }
    }

    protected function registerClientScript()
    {
        $id = $this->options['id'];
        $params = $params = $this->params !== null ? $this->params : Yii::$app->getRequest()->getQueryParams();
        unset($params[$this->paramName]);
        $links = array();
        $params[0] = $this->route;
        foreach ($this->items as $key => $value) {
            $params[$this->paramName] = $key;
            $links[$key] = Url::to($params);
        }
        $links = array_merge($links, $this->links);

        $options = Json::encode(['links' => $links]);
        $view = $this->getView();
        list($basePath, $baseUrl) = $view->assetManager->publish('@mdm/widgets/assets');
        $view->registerJsFile($baseUrl . '/mdm.dropdownLink.js', ['yii\web\JqueryAsset']);
        $view->registerJs("jQuery('#$id').mdmDropdownLink($options);");
    }
}