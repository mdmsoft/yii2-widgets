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
        'modelClass' => OrderItem::className(),
        'options' => ['tag' => 'tbody'],
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
<td><?= Html::activeInputField($model,"[$key]product_id") ?></td>
<td><?= Html::activeInputField($model,"[$key]qty") ?></td>
<td><a data-action="delete"><span glypicon glypicon-minus></span></a></td>
```
