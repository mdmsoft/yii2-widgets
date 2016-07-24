<?php

namespace mdm\widgets;

use yii\base\Model;
/**
 * Description of MultipleTrait
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
trait MultipleTrait
{

    /**
     * Populates a set of models with the data from end user.
     * This method is mainly used to collect tabular data input.
     * The data to be loaded for each model is `$data[formName][index]`, where `formName`
     * refers to the sort name of model class, and `index` the index of the model in the `$data` array.
     * If `$formName` is empty, `$data[index]` will be used to populate each model.
     * The data being populated to each model is subject to the safety check by [[setAttributes()]].
     * @param array $data the data array. This is usually `$_POST` or `$_GET`, but can also be any valid array
     * supplied by end user.
     * @param string $formName the form name to be used for loading the data into the models.
     * If not set, it will use the sort name of called class.
     * @param Model[] $origin original models to be populated. It will be check using `$keys` with supplied data.
     * If same then will be used for result model.
     * @param array $options Option to model
     * - 'scenario' for model.
     * - 'arguments' The parameters to be passed to the class constructor as an array.
     * @return boolean|Model[] whether at least one of the models is successfully populated.
     */
    public static function createMultiple($data, $formName = null, &$origin = [], $options = [])
    {
        return ModelHelper::createMultiple(get_called_class(), $data, $formName, $origin, $options);
    }
}
