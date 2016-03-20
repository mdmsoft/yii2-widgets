<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\base\Widget;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\widgets\ActiveForm;

/**
 * Description of TabularInput
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class TabularInput extends Widget
{
    /**
     * @var array the HTML attributes for the container tag of the list view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'tabular'];
    /**
     * @var array the HTML attributes for the container of the rendering result of each data model.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * If "tag" is false, it means no container element will be rendered.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $itemOptions = ['class' => 'table-row'];
    /**
     * @var string|callable the name of the view for rendering each data item, or a callback (e.g. an anonymous function)
     * for rendering each data item. If it specifies a view name, the following variables will
     * be available in the view:
     *
     * - `$model`: mixed, the data model
     * - `$key`: mixed, the key value associated with the data item
     * - `$index`: integer, the zero-based index of the data item in the items array returned by [[dataProvider]].
     * - `$widget`: ListView, this widget instance
     *
     * Note that the view name is resolved into the view file by the current context of the [[view]] object.
     *
     * If this property is specified as a callback, it should have the following signature:
     *
     * ~~~
     * function ($model, $key, $index, $widget)
     * ~~~
     */
    public $itemView;
    /**
     * @var array additional parameters to be passed to [[itemView]] when it is being rendered.
     * This property is used only when [[itemView]] is a string representing a view name.
     */
    public $viewParams = [];
    /**
     * @var string the HTML code to be displayed between any two consecutive items.
     */
    public $separator = "\n";
    /**
     * @var \yii\base\Model[]|array
     */
    public $allModels = [];
    /**
     * @var string|array
     * @deprecated since version 1.2 Use [[model]] instead.
     */
    public $modelClass;
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
     * @var array 
     */
    public $tags = [
        '<@@' => '<?php',
        '<@=' => '<?=',
        '@>' => '?>',
    ];
    /**
     * @var string 
     */
    private $_templateFile;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        if (empty($this->model)) {
            if (!empty($this->modelClass)) {
                $this->model = Yii::createObject($this->modelClass);
            }
        } elseif (!is_object($this->model)) {
            $this->model = Yii::createObject($this->model);
        }
        Html::addCssClass($this->itemOptions, 'mdm-tabular-item');
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $template = trim(ob_get_clean());
        if ($this->itemView === null && !empty($template)) {
            $current = $this->getView()->getViewFile();
            $file = sprintf('%x/%x-%s', crc32(dirname($current)) % 0x100, crc32($current), $this->options['id']);
            $this->_templateFile = Yii::getAlias("@runtime/mdm-tabular/{$file}.php");
            if (!is_file($this->_templateFile) || filemtime($current) >= filemtime($this->_templateFile)) {
                FileHelper::createDirectory(dirname($this->_templateFile));
                $template = str_replace(array_keys($this->tags), array_values($this->tags), $template);
                file_put_contents($this->_templateFile, $template, LOCK_EX);
            }
        }

        $this->registerClientScript();

        $tag = ArrayHelper::remove($this->options, 'tag', 'div');
        $content = $this->renderItems();
        echo Html::tag($tag, $content? : '', $this->options);
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

        return implode($this->separator, $rows);
    }

    /**
     * Renders a single data model.
     * @param  mixed   $model the data model to be rendered
     * @param  mixed   $key   the key value associated with the data model
     * @param  integer $index the zero-based index of the data model in the model array returned by [[dataProvider]].
     * @return string  the rendering result
     */
    public function renderItem($model, $key, $index)
    {
        $params = array_merge([
            'model' => $model,
            'key' => $key,
            'index' => $index,
            'widget' => $this,
            'form' => $this->form,
            ], $this->viewParams);

        // render content
        if ($this->itemView === null) {
            $content = $this->_templateFile ? $this->template($params) : $key;
        } elseif (is_string($this->itemView)) {
            $content = $this->getView()->render($this->itemView, $params);
        } else {
            $content = call_user_func($this->itemView, $model, $key, $index, $this);
        }
        $options = $this->itemOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        $options['data-key'] = (string) $key;
        $options['data-index'] = (string) $index;
        return Html::tag($tag, $content, $options);
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
        $itemTag = ArrayHelper::getValue($this->itemOptions, 'tag', 'div');
        if (empty($clientOptions['itemSelector'])) {
            $clientOptions['itemSelector'] = "{$itemTag}.mdm-tabular-item";
        }
        if (empty($clientOptions['itemSelector'])) {
            throw new InvalidConfigException('Value of "clientOptions[\'itemSelector\']" must be specified.');
        }
        if ($this->form instanceof ActiveForm) {
            $clientOptions['formSelector'] = '#' . $this->form->options['id'];
        }

        // template and js
        $view = $this->getView();
        $oldJs = $view->js;
        $view->js = [];
        if ($this->form instanceof ActiveForm) {
            $offset = count($this->form->attributes);
        }
        $template = $this->renderItem($this->model, '_dkey_', '_dindex_');
        if ($this->form instanceof ActiveForm) {
            $clientOptions['validations'] = array_slice($this->form->attributes, $offset);
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
        return $clientOptions;
    }

    /**
     * Render template
     * @param array $params
     * @return string
     */
    protected function template($params = [])
    {
        return $this->getView()->renderPhpFile($this->_templateFile, $params);
    }
}
