<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\base\Model;
use yii\base\InvalidConfigException;

/**
 * Description of GridInput
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class GridInput extends TabularWidget
{
    /**
     * @inheritdoc
     */
    public $tag = 'table';

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
     * @var string
     */
    public $footer;

    /**
     *
     * @var array
     */
    public $headerOptions = [];

    /**
     *
     * @var array
     */
    public $footerOptions = [];

    /**
     *
     * @var string
     */
    public $defaultColumnClass = 'mdm\widgets\DataColumn';

    /**
     * @inheritdoc
     */
    public $options = ['class' => 'table table-striped'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!($this->model instanceof Model)) {
            $property = __CLASS__ . '::$model';
            throw new InvalidConfigException("Value of \"{$property}\" must be specified.");
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
        $this->containerOptions['tag'] = 'tbody';
        $this->clientOptions = array_merge([
            'container' => "tbody.mdm-container{$this->level}",
            'itemSelector' => "tr.mdm-item{$this->level}"
            ], $this->clientOptions);
        Html::addCssClass($this->itemOptions, "mdm-item{$this->level}");
        Html::addCssClass($this->containerOptions, "mdm-container{$this->level}");
    }

    /**
     * @inheritdoc
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
            foreach ((array) $this->header as $header) {
                $rows[] = Html::tag('tr', $header, $this->headerOptions);
            }
        }
        return Html::tag('thead', implode("\n", $rows));
    }

    /**
     * @inheritdoc
     */
    public function renderItem($model, $key, $index)
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

    /**
     * @inheritdoc
     */
    public function renderFooter()
    {
        if ($this->footer === false) {
            return '';
        }
        if ($this->footer === null) {
            $cols = [];
            foreach ($this->columns as $column) {
                $cols[] = $column->renderFooterCell();
            }
            if (count($this->hiddens)) {
                $cols[] = '<td style="display:none;" class"hidden-col"></td>';
            }
            $rows = [Html::tag('tr', implode("\n", $cols), $this->footerOptions)];
        } else {
            $rows = [];
            foreach ((array) $this->footer as $footer) {
                $rows[] = Html::tag('tr', $footer, $this->footerOptions);
            }
        }
        return Html::tag('tfoot', implode("\n", $rows));
    }
}
