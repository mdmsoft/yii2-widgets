<?php

use yii\web\View;
use yii\helpers\Html;

/* @var $this View */
?>

<div <?= Html::renderTagAttributes($options) ?> >
    <div class="container"></div>
    <div style="display: none;">
        <input type="hidden" name="<?= $cropParam; ?>[x]" data-attr="x">
        <input type="hidden" name="<?= $cropParam; ?>[y]" data-attr="y">
        <input type="hidden" name="<?= $cropParam; ?>[w]" data-attr="w">
        <input type="hidden" name="<?= $cropParam; ?>[h]" data-attr="h">
    </div>
    <?= $fileInput ?>
</div>