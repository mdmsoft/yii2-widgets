<?php

namespace mdm\widgets;

use Closure;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\widgets\ActiveForm;

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
     * @var array|Closure
     */
    public $items;

    /**
     * @var string 
     */
    public $template = '{input} {error}';

    /**
     * @var string|array
     * ```php
     * 
     * ```
     */
    public $widget;

    /**
     *
     * @var string
     */
    public $type = 'text';

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->attribute) {
            $field = str_replace(['[]', '][', '[', ']', ' ', '.'], ['', '-', '-', '', '-', '-'], $this->attribute);
        } else {
            $field = false;
        }
        if (empty($this->inputOptions['data-field']) && $field) {
            $this->inputOptions['data-field'] = $field;
        }
        if (empty($this->contentOptions['data-column']) && $field) {
            $this->contentOptions['data-column'] = $field;
        }
        if (empty($this->headerOptions['data-column']) && $field) {
            $this->headerOptions['data-column'] = $field;
        }
        if ($this->header === null) {
            if ($this->grid->model instanceof Model && !empty($this->attribute)) {
                $this->header = $this->grid->model->getAttributeLabel($this->attribute);
            } else {
                $this->header = Inflector::camel2words($this->attribute);
            }
        }
        if ($this->value === null) {
            $this->value = [$this, 'renderInputCell'];
        } elseif (is_string($this->value)) {
            $this->attribute = $this->value;
            $this->value = [$this, 'renderTextCell'];
        }
    }

    /**
     * Render input cell
     * @param Model $model model for cell
     * @param string $key
     * @param integer $index
     * @return string
     */
    public function renderInputCell($model, $key, $index)
    {
        $form = $this->grid->form;
        $items = $this->items;
        if ($this->widget !== null) {
            if (is_array($this->widget)) {
                list($widget, $options) = $this->widget;
                if ($options instanceof Closure) {
                    $options = call_user_func($options, $model, $key, $index);
                }
            } else {
                $widget = $this->widget;
                $options = [];
            }
            if ($form instanceof ActiveForm) {
                return $form->field($model, "[$key]{$this->attribute}", ['template' => $this->template])
                        ->widget($widget, $options);
            } else {
                $options = array_merge([
                    'model' => $model,
                    'attribute' => "[$key]{$this->attribute}"
                    ], $options);
                return $widget::widget($options);
            }
        } elseif ($items !== null) {
            if ($items instanceof Closure) {
                $items = call_user_func($items, $model, $key, $index);
            }
            switch ($this->type) {
                case 'checkbox':
                    if ($form instanceof ActiveForm) {
                        return $form->field($model, "[$key]{$this->attribute}", ['template' => $this->template])
                                ->checkboxList($items, $this->inputOptions);
                    } else {
                        return Html::activeCheckboxList($model, "[$key]{$this->attribute}", $items, $this->inputOptions);
                    }
                    break;

                default:
                    if ($form instanceof ActiveForm) {
                        return $form->field($model, "[$key]{$this->attribute}", ['template' => $this->template])
                                ->dropDownList($items, $this->inputOptions);
                    } else {
                        return Html::activeDropDownList($model, "[$key]{$this->attribute}", $items, $this->inputOptions);
                    }
                    break;
            }
        } else {
            switch ($this->type) {
                case 'checkbox':
                    if ($form instanceof ActiveForm) {
                        return $form->field($model, "[$key]{$this->attribute}", ['template' => $this->template])
                                ->checkbox($this->inputOptions, false);
                    } else {
                        return Html::activeCheckbox($model, "[$key]{$this->attribute}", $this->inputOptions);
                    }
                    break;

                default:
                    if ($form instanceof ActiveForm) {
                        return $form->field($model, "[$key]{$this->attribute}", ['template' => $this->template])
                                ->textInput($this->inputOptions);
                    } else {
                        return Html::activeTextInput($model, "[$key]{$this->attribute}", $this->inputOptions);
                    }
                    break;
            }
        }
    }

    /**
     * Render input cell
     * @param Model $model model for cell
     * @param string $key
     * @return string
     */
    public function renderTextCell($model, $key)
    {
        return Html::getAttributeValue($model, "[$key]{$this->attribute}");
    }
}
