<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\widgets\ActiveForm;
use yii\web\JsExpression;

/**
 * Description of TabularWidget
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
abstract class TabularWidget extends Widget
{
    public $tag = 'div';
    /**
     * @var array the HTML attributes for the container tag of the list view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = [];
    /**
     * @var array the HTML attributes for the container of the rendering result of each data model.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * If "tag" is false, it means no container element will be rendered.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $itemOptions = [];
    /**
     * @var array the HTML attributes for the container of the rendering result of each data model.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * If "tag" is false, it means no container element will be rendered.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $containerOptions = [];
    /**
     * @var string the HTML code to be displayed between any two consecutive items.
     */
    public $separator = "\n";
    /**
     * @var \yii\base\Model[]|array
     */
    public $allModels = [];
    /**
     * @var mixed
     */
    public $model;
    /**
     * @var ActiveForm
     */
    public $form;
    /**
     * @var array Client option
     */
    public $clientOptions = [];
    /**
     * @var string
     */
    public $layout = "{header}\n{items}";
    /**
     * Header
     * @var string 
     */
    public $header;
    /**
     * Footer
     * @var string
     */
    public $footer;
    /**
     * Part
     * @var array 
     */
    public $sections = [];
    protected $level;
    private static $_level = 0;

    /**
     * Renders a single data model.
     * @param mixed $model the data model to be rendered
     * @param mixed $key the key value associated with the data model
     * @param integer $index the zero-based index of the data model in the model array returned by [[dataProvider]].
     * @return string  the rendering result
     */
    abstract public function renderItem($model, $key, $index);

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->level = self::$_level === 0 ? '' : self::$_level;
        self::$_level++;

        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        if (!empty($this->model) && !is_object($this->model)) {
            $this->model = Yii::createObject($this->model);
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!empty($this->containerOptions['tag']) && empty($this->containerOptions['container'])) {
            Html::addCssClass($this->containerOptions, 'mdm-container');
            $this->containerOptions['container'] = "{$this->containerOptions['tag']}.mdm-container";
        }
        $this->registerClientScript();

        $content = preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) {
            $method = 'render' . $matches[1];
            if (method_exists($this, $method)) {
                return $this->$method();
            } elseif (isset($this->sections[$matches[1]])) {
                return $matches[1];
            }
            return $matches[0];
        }, $this->layout);

        self::$_level--;
        echo Html::tag($this->tag, $content, $this->options);
    }

    /**
     * Renders all data models.
     * @return string the rendering result
     */
    public function renderHeader()
    {
        return $this->header;
    }

    /**
     * Renders all data models.
     * @return string the rendering result
     */
    public function renderItems()
    {
        $rows = [];
        $index = 0;
        foreach ($this->allModels as $key => $model) {
            $rows[] = $this->renderItem($model, $key, $index++);
        }
        $content = implode($this->separator, $rows);
        $options = $this->containerOptions;
        if (empty($options['tag'])) {
            return $content;
        }
        $tag = $options['tag'];
        unset($options['tag']);

        return Html::tag($tag, $content, $options);
    }

    public function renderFooter()
    {
        return $this->footer;
    }
    /**
     * Register script
     */
    protected function registerClientScript()
    {
        $id = $this->options['id'];
        $options = Json::htmlEncode($this->getClientOptions());
        $view = $this->getView();
        TabularAsset::register($view);
        $view->registerJs("jQuery('#$id').mdmTabularInput($options);");
    }

    /**
     * Get client options
     * @return array
     */
    protected function getClientOptions()
    {
        $counter = count($this->allModels) ? max(array_keys($this->allModels)) + 1 : 0;
        $clientOptions = $this->clientOptions;

        $clientOptions['counter'] = $counter;
        if (empty($clientOptions['itemSelector'])) {
            throw new InvalidConfigException('Value of "clientOptions[\'itemSelector\']" must be specified.');
        }
        if ($this->form instanceof ActiveForm) {
            $clientOptions['formSelector'] = '#' . $this->form->options['id'];
            $oldAttrs = $this->form->attributes;
            $this->form->attributes = [];
        }

        // template and js
        $view = $this->getView();
        $oldJs = $view->js;
        $view->js = [];

        $template = $this->renderItem($this->model, "_dkey{$this->level}_", "_dindex{$this->level}_");
        if (isset($oldAttrs)) {
            $clientOptions['validations'] = $this->form->attributes;
            $this->form->attributes = $oldAttrs;
        }
        $js = [];
        foreach ($view->js as $pieces) {
            $js[] = implode("\n", $pieces);
        }
        if (count($js)) {
            $clientOptions['templateJs'] = implode("\n", $js);
        }
        $view->js = $oldJs;
        // ***

        $clientOptions['template'] = $template;
        $clientOptions['replaces'] = [
            'key' => new JsExpression("/_dkey{$this->level}_/g"),
            'index' => new JsExpression("/_dindex{$this->level}_/g"),
        ];
        return $clientOptions;
    }
}
