yii2-widgets
============

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mdmsoft/yii2-widgets "~1.0"
```

or add

```
"mdmsoft/yii2-widgets": "~1.0"
```

to the require section of your `composer.json` file.

Usage
-----

# TabularInput Widget

`_form.php`
```php
<?php $form = ActiveForm::begin()?>
<table class="table">
    <thead>
        <tr>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <th><a id="btn-add"><span class="glypicon glypicon-plus"></span></a></th>
        </tr>
    </thead>
<?= 
    TabularInput::widget([
        'id' => 'detail-grid',
        'allModels' => $model->items,
        'model' => OrderItem::className(),
        'tag' => 'tbody',
        'form' => $form,
        'itemOptions' => ['tag' => 'tr'],
        'itemView' => '_item_detail',
        'clientOptions' => [
            'btnAddSelector' => '#btn-add',
        ]
    ]);
?>
</table>
```

`_item_detail.php`
```php
<td><?= $form->field($model,"[$key]product_id")->textInput()->label(false); ?></td>
<td><?= $form->field($model,"[$key]qty")->textInput()->label(false); ?></td>
<td><a data-action="delete"><span glypicon glypicon-minus></span></a></td>
```

# GridInput Widget
```php
<?= 
    GridInput::widget([
        'id' => 'detail-grid',
        'allModels' => $model->items,
        'model' => OrderItem::className(),
        'columns' => [
            ['class' => 'mdm\widgets\SerialColumn'],
            'product_id',
            'qty',
            [
                'attribute' => 'uom_id',
                'items' => [
                    1 => 'Pcs',
                    2 => 'Dozen'
                ]
            ],
            ['class' => 'mdm\widgets\ButtonColumn']
        ],
    ]);
?>
```