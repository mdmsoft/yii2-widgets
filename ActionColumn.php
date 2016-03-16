<?php

namespace mdm\widgets;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * Description of ActionColumn
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ActionColumn extends \yii\grid\ActionColumn
{
    public $iconTemplate = '<span class="glyphicon glyphicon-{icon}"></span>';

    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $buttons = [
            'view' => [
                'title' => Yii::t('yii', 'View'),
                'aria-label' => Yii::t('yii', 'View'),
                'data-pjax' => '0',
                'icon' => 'eye-open',
            ],
            'update' => [
                'title' => Yii::t('yii', 'Update'),
                'aria-label' => Yii::t('yii', 'Update'),
                'data-pjax' => '0',
                'icon' => 'pencil'
            ],
            'delete' => [
                'title' => Yii::t('yii', 'Delete'),
                'aria-label' => Yii::t('yii', 'Delete'),
                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'data-method' => 'post',
                'data-pjax' => '0',
                'icon' => 'trash'
            ]
        ];
        foreach ($buttons as $name => $button) {
            $this->buttons[$name] = array_merge($button, $this->buttonOptions, $this->buttons[$name]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        return preg_replace_callback('/\\{([\w\-\/]+)\\}/', function ($matches) use ($model, $key, $index) {
            $name = $matches[1];

            if (isset($this->visibleButtons[$name])) {
                $isVisible = $this->visibleButtons[$name] instanceof \Closure ? call_user_func($this->visibleButtons[$name], $model, $key, $index)
                        : $this->visibleButtons[$name];
            } else {
                $isVisible = true;
            }

            if ($isVisible) {
                $button = isset($this->buttons[$name]) ? $this->buttons[$name] : [
                    'label' => ucfirst($name),
                    'aria-label' => ucfirst($name),
                    'data-pjax' => '0',
                ];
                $url = $this->createUrl($name, $model, $key, $index);
                if ($button instanceof \Closure) {
                    return call_user_func($button, $url, $model, $key);
                } else {
                    $icon = str_replace('{icon}', ArrayHelper::remove($button, 'icon', ''), $this->iconTemplate);
                    return Html::a($icon, $url, $button);
                }
            }
            return '';
        }, $this->template);
    }
}
