<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\base\Model;
use yii\helpers\Json;
use yii\widgets\ActiveForm;

/**
 * Description of GridInput
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class GridInput extends \yii\base\Widget
{
    /**
     * @var Model[]
     */
    public $allModels = [];
    /**
     * @var Model
     */
    public $model;
    /**
     * @var Column[]
     */
    public $columns = [];
    /**
     * @var array
     */
    public $hiddens = [];
    /**
     *
     * @var string
     */
    public $header;
    /**
     *
     * @var array
     */
    public $itemOptions = [];
    /**
     *
     * @var array
     */
    public $headerOptions = [];
    /**
     *
     * @var string
     */
    public $defaultColumnClass = 'mdm\widgets\DataColumn';
    /**
     *
     * @var array
     */
    public $options = ['class' => 'table table-striped'];
    /**
     *
     * @var array
     */
    public $clientOptions = [];
    /**
     * @var ActiveForm
     */
    public $form;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->getId();
        }
        foreach ($this->columns as $i => $column) {
            if (is_string($column)) {
                $column = Yii::createObject([
                        'class' => $this->defaultColumnClass,
                        'attribute' => $column,
                        'grid' => $this,
                ]);
            } elseif (is_array($column)) {
                if (!isset($column['class'])) {
                    $column['class'] = $this->defaultColumnClass;
                }
                $column['grid'] = $this;
                $column = Yii::createObject($column);
            }

            $this->columns[$i] = $column;
        }
        if (!($this->model instanceof Model)) {
            $this->model = Yii::createObject($this->model);
        }
        Html::addCssClass($this->itemOptions, 'mdm-tabular-item');
    }

    /**
     * Register client option
     */
    public function registerClientOption()
    {
        $id = $this->options['id'];
        $options = Json::htmlEncode($this->getClientOptions());
        $view = $this->getView();
        TabularAsset::register($view);
        $view->registerJs("jQuery('#$id').mdmTabularInput($options);");
    }

    /**
     * Get client option
     * @return array
     */
    protected function getClientOptions()
    {
        $counter = count($this->allModels) ? max(array_keys($this->allModels)) + 1 : 0;
        $clientOptions = $this->clientOptions;

        $clientOptions['counter'] = $counter;
        $clientOptions['container'] = 'tbody';

        if (empty($clientOptions['itemSelector'])) {
            $clientOptions['itemSelector'] = 'tr.mdm-tabular-item';
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
     * @inheritdoc
     */
    public function run()
    {
        $this->registerClientOption();
        echo Html::beginTag('table', $this->options);
        echo $this->renderHeader();
        echo $this->renderBody();
        echo '</table>';
    }

    /**
     * Render header
     * @return string
     */
    public function renderHeader()
    {
        if ($this->header === false) {
            return '';
        }
        if ($this->header === null) {
            $cols = [];
            foreach ($this->columns as $column) {
                $cols[] = $column->renderHeaderCell();
            }
            if (count($this->hiddens)) {
                $cols[] = '<th style="display:none;" class"hidden-col"></th>';
            }
            $rows = [Html::tag('tr', implode("\n", $cols), $this->headerOptions)];
        } else {
            $rows = [];
            $colspan = count($this->columns);
            foreach ((array) $this->header as $header) {
                $rows[] = Html::tag('th', "<th colspan=\"{$colspan}\">{$header}</th>>", $this->headerOptions);
            }
        }
        return Html::tag('thead', implode("\n", $rows));
    }

    /**
     * Render header
     * @return string
     */
    public function renderBody()
    {
        $rows = [];
        $index = 0;
        foreach ($this->allModels as $key => $model) {
            $rows[] = $this->renderItem($model, $key, $index++);
        }
        return Html::tag('tbody', implode("\n", $rows));
    }

    /**
     * Render item
     * @param Model $model
     * @param integer $key
     * @param integer $index
     * @return string
     */
    protected function renderItem($model, $key, $index)
    {
        $cols = [];
        /* @var $column Column */
        foreach ($this->columns as $column) {
            $cols[] = $column->renderDataCell($model, $key, $index);
        }
        if (count($this->hiddens)) {
            $hiddens = [];
            foreach ($this->hiddens as $options) {
                if (is_string($options)) {
                    $attribute = $options;
                    $options = [];
                } else {
                    $attribute = ArrayHelper::remove($options, 'attribute');
                }
                $field = str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], $attribute);
                $options['data-field'] = $field;
                $options['id'] = false;
                $hiddens[] = Html::activeHiddenInput($model, "[$key]{$attribute}", $options);
            }
            $cols[] = Html::tag('td', implode("\n", $hiddens), ['style' => ['display' => 'none'], 'class' => 'hidden-col']);
        }
        $options = $this->itemOptions;
        $options['data-key'] = (string) $key;
        $options['data-index'] = (string) $index;
        return Html::tag('tr', implode("\n", $cols), $options);
    }
}
