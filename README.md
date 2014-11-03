yii2-widgets
============

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mdmsoft//yii2-format-converter "*"
```

or add

```
"mdmsoft//yii2-format-converter": "*"
```

to the require section of your `composer.json` file.

Usage
-----

# TabularInput Widget

```php
<?= 
    TabularInput::widget([
        'id' => 'detail-grid',
        'allModels' => $details,
        'modelClass' => OrderItem::className(),
        'options' => ['tag' => 'tbody'],
        'itemOptions' => ['tag' => 'tr'],
        'itemView' => '_item_detail',
        'clientOptions' => [
            'afterAddRow' => new JsExpresion("..."),
        ]
    ]);
?>
```