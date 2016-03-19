<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\Html;
use yii\base\Model;

/**
 * Description of Column
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Column extends \yii\base\Object
{
    /**
     * @var GridInput
     */
    public $grid;
    /**
     *
     * @var string|\Closure
     */
    public $value;
    /**
     *
     * @var string header text
     */
    public $header;
    /**
     *
     * @var array
     */
    public $headerOptions = [];
    /**
     *
     * @var array
     */
    public $contentOptions = [];
    /**
     * @var string
     */
    public $format;

    /**
     * Render header cell
     * @return string
     */
    public function renderHeaderCell()
    {
        return Html::tag('th', $this->header, $this->headerOptions);
    }

    /**
     * Render data cell
     * @param Model $model model for cell
     * @param string $key
     * @param integer $index
     * @return string
     */
    public function renderDataCell($model, $key, $index)
    {
        if ($this->value instanceof \Closure) {
            $value = call_user_func($this->value, $model, $key, $index);
        } else {
            $value = $this->value;
        }
        if ($this->format !== null) {
            $value = Yii::$app->getFormatter()->format($value, $this->format);
        }
        return Html::tag('td', $value, $this->contentOptions);
    }
}
