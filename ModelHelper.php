<?php

namespace mdm\widgets;

use ReflectionClass;
use Yii;
use yii\base\Model;
use yii\db\BaseActiveRecord;
use yii\helpers\Html;

/**
 * Description of ModelHelper
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class ModelHelper
{

    /**
     * Populates a set of models with the data from end user.
     * This method is mainly used to collect tabular data input.
     * The data to be loaded for each model is `$data[formName][index]`, where `formName`
     * refers to the sort name of model class, and `index` the index of the model in the `$data` array.
     * If `$formName` is empty, `$data[index]` will be used to populate each model.
     * The data being populated to each model is subject to the safety check by [[setAttributes()]].
     * @param string $class Model class name.
     * @param array $data the data array. This is usually `$_POST` or `$_GET`, but can also be any valid array
     * supplied by end user.
     * @param string $formName the form name to be used for loading the data into the models.
     * If not set, it will use the sort name of called class.
     * @param Model[] $origin original models to be populated. It will be check using `$keys` with supplied data.
     * If same then will be used for result model.
     * @param array $options Option to model
     * - scenario for model.
     * - arguments The parameters to be passed to the class constructor as an array.
     * @return boolean|Model[] whether at least one of the models is successfully populated.
     */
    public static function createMultiple($class, $data, $formName = null, &$origin = [], $options = [])
    {
        $reflector = new ReflectionClass($class);
        $args = isset($options['arguments']) ? $options['arguments'] : [];
        if ($formName === null) {
            /* @var $model Model */
            $model = empty($args) ? new $class() : $reflector->newInstanceArgs($args);
            $formName = $model->formName();
        }
        if ($formName != '') {
            $data = isset($data[$formName]) ? $data[$formName] : null;
        }
        if ($data === null) {
            return false;
        }

        $models = [];
        foreach ($data as $i => $row) {
            $model = null;
            if (isset($origin[$i])) {
                $model = $origin[$i];
                unset($origin[$i]);
            } else {
                $model = empty($args) ? new $class() : $reflector->newInstanceArgs($args);
            }
            if (isset($options['scenario'])) {
                $model->scenario = $options['scenario'];
            }
            $model->load($row, '');
            $models[$i] = $model;
        }
        return $models;
    }

    /**
     * Populates a set of models with the data from end user.
     * This method is mainly used to collect tabular data input.
     * The data to be loaded for each model is `$data[formName][index]`, where `formName`
     * refers to the sort name of model class, and `index` the index of the model in the `$data` array.
     * If `$formName` is empty, `$data[index]` will be used to populate each model.
     * The data being populated to each model is subject to the safety check by [[setAttributes()]].
     * @param string $class Model class name.
     * @param array $data the data array. This is usually `$_POST` or `$_GET`, but can also be any valid array
     * supplied by end user.
     * @param string $formName the form name to be used for loading the data into the models.
     * If not set, it will use the sort name of called class.
     * @param Model[] $origin original models to be populated. It will be check using `$keys` with supplied data.
     * If same then will be used for result model.
     * @param array $config Option to model
     * @return boolean|Model[] whether at least one of the models is successfully populated.
     */
    public static function loadMultiple($class, $data, $formName = null, &$origin = [], $config = [])
    {
        $config['class'] = $class;
        if ($formName === null) {
            /* @var $model Model */
            $model = Yii::createObject($config);
            $formName = $model->formName();
        }
        if ($formName != '') {
            $data = isset($data[$formName]) ? $data[$formName] : null;
        }
        if ($data === null) {
            return [];
        }

        $models = [];
        foreach ($data as $i => $row) {
            $model = null;
            if (isset($origin[$i])) {
                $model = $origin[$i];
                unset($origin[$i]);
            } else {
                $model = Yii::createObject($config);
            }
            $model->load($row, '');
            $models[$i] = $model;
        }
        return $models;
    }

    /**
     *
     * @param Model[] $models
     * @param array $options
     * @param array $messages
     * @return boolean
     */
    public static function validateMultiple(array $models, $options = [], &$messages = [])
    {
        /* @var $model Model */
        $validateAll = !empty($options['validateAll']);
        /* @var $model BaseActiveRecord */
        if (isset($options['values'])) {
            foreach ($models as $model) {
                Yii::configure($model, $options['values']);
            }
        }
        $result = true;
        foreach ($models as $i => $model) {
            $oke = !isset($options['beforeValidate']) || call_user_func($options['beforeValidate'], $model) !== false;
            if ($oke && $model->validate()) {
                isset($options['afterValidate']) && call_user_func($options['afterValidate'], $model);
            } else {
                foreach ($model->getErrors() as $attribute => $message) {
                    $messages[Html::getInputId($model, "[$i]$attribute")] = $message;
                }
                if ($validateAll) {
                    $result = false;
                } else {
                    return false;
                }
            }
        }
        return $result;
    }

    /**
     *
     * @param BaseActiveRecord[] $models
     * @param array $options
     * - runValidation boolean default true.
     * - beforeSave callable
     * - afterSave callable
     * - deleteUnsaved boolean default true.
     * @param array $messages
     * @return boolean
     */
    public static function saveMultiple(array $models, $options = [], &$messages = [])
    {
        $runValidation = !isset($options['runValidation']) || $options['runValidation'];
        /* @var $model BaseActiveRecord */
        if (isset($options['values'])) {
            foreach ($models as $model) {
                Yii::configure($model, $options['values']);
            }
        }
        unset($options['values']);
        if (!$runValidation || static::validateMultiple($models, $options, $messages)) {
            foreach ($models as $model) {
                if (!isset($options['beforeSave']) || call_user_func($options['beforeSave'], $model) !== false) {
                    if ($model->save(false)) {
                        isset($options['afterSave']) && call_user_func($options['afterSave'], $model);
                    } else {
                        return false;
                    }
                } elseif (!isset($options['deleteUnsaved']) || $options['deleteUnsaved']) {
                    if (!$model->isNewRecord) {
                        $model->delete();
                    }
                }
            }
            return true;
        }
        return false;
    }
}
