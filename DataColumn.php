<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\base\Model;
use yii\helpers\Inflector;

/**
 * Description of DataColumn
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class DataColumn extends Column
{
    /**
     * @var string attribute
     */
    public $attribute;
    /**
     * @var array option for input
     */
    public $inputOptions = ['class' => 'form-control'];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $field = str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], $this->attribute);
        if (empty($this->inputOptions['data-field'])) {
            $this->inputOptions['data-field'] = $field;
        }
        if (!array_key_exists('id', $this->inputOptions)) {
            $this->inputOptions['id'] = false;
        }
        if (empty($this->contentOptions['data-column'])) {
            $this->contentOptions['data-column'] = $field;
        }
        if (empty($this->headerOptions['data-column'])) {
            $this->headerOptions['data-column'] = $field;
        }
        if ($this->header === null) {
            if ($this->grid->model instanceof Model && !empty($this->attribute)) {
                $this->header = $this->grid->model->getAttributeLabel($this->attribute);
            } else {
                $this->header = Inflector::camel2words($this->attribute);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function renderDataCell($model, $key, $index)
    {
        if ($this->value !== null) {
            if ($this->value instanceof \Closure) {
                $value = call_user_func($this->value, $model, $key, $index);
            } else {
                $value = Html::getAttributeValue($model, "[$key]{$this->attribute}");
            }
            if ($this->format !== null) {
                $value = Yii::$app->getFormatter()->format($value, $this->format);
            }
        } else {
            $value = $this->renderInputCell($model, $key);
        }
        return Html::tag('td', $value, $this->contentOptions);
    }

    /**
     * Render input cell
     * @param Model $model model for cell
     * @param string $key
     * @return string
     */
    public function renderInputCell($model, $key)
    {
        $items = ArrayHelper::getValue($this->inputOptions, 'items');
        if ($items !== null) {
            if ($items instanceof \Closure) {
                $items = call_user_func($items, $model, $key);
            }
            return Html::activeDropDownList($model, "[$key]{$this->attribute}", $items, $this->inputOptions);
        } else {
            return Html::activeTextInput($model, "[$key]{$this->attribute}", $this->inputOptions);
        }
    }
}
