<?php

namespace mdm\widgets;

use yii\helpers\Html;

/**
 * Description of ButtonColumn
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ButtonColumn extends Column
{
    /**
     * @var string Icon for header
     */
    public $headerIcon = '<span class="glyphicon glyphicon-plus"></span>';
    /**
     * @var string Icon for delete button
     */
    public $deleteIcon = '<span class="glyphicon glyphicon-trash"></span>';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $id = $this->grid->options['id'] . '-add-button';
        $this->header = Html::a($this->headerIcon, '#', ['id' => $id]);
        if (!isset($this->grid->clientOptions['btnAddSelector'])) {
            $this->grid->clientOptions['btnAddSelector'] = '#' . $id;
        }
        $this->value = Html::a($this->deleteIcon, '#', ['data-action' => 'delete']);
    }
}
