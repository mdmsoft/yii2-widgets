<?php

namespace mdm\widgets;

use yii\web\AssetBundle;

/**
 * Description of TabularAsset
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class TabularAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@mdm/widgets/assets';

    /**
     * @inheritdoc
     */
    public $css = [
        'css/tabularInput.css'
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/tabularInput.js'
    ];

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\web\JqueryAsset'
    ];

}
