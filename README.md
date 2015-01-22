yii2-widgets
============

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require mdmsoft//yii2-widgets "~1.0"
```

or add

```
"mdmsoft//yii2-widgets": "~1.0"
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
