<?php

namespace mdm\widgets;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\FileHelper;

/**
 * Description of TabularInput
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class TabularInput extends TabularWidget
{
    /**
     * @var string|callable the name of the view for rendering each data item, or a callback (e.g. an anonymous function)
     * for rendering each data item. If it specifies a view name, the following variables will
     * be available in the view:
     *
     * - `$model`: mixed, the data model
     * - `$key`: mixed, the key value associated with the data item
     * - `$index`: integer, the zero-based index of the data item in the items array returned by [[dataProvider]].
     * - `$widget`: ListView, this widget instance
     *
     * Note that the view name is resolved into the view file by the current context of the [[view]] object.
     *
     * If this property is specified as a callback, it should have the following signature:
     *
     * ~~~
     * function ($model, $key, $index, $widget)
     * ~~~
     */
    public $itemView;
    /**
     * @var array additional parameters to be passed to [[itemView]] when it is being rendered.
     * This property is used only when [[itemView]] is a string representing a view name.
     */
    public $viewParams = [];
    /**
     * @var array 
     */
    public $tags = [
        '<@@' => '<?php',
        '<@=' => '<?=',
        '@>' => '?>',
    ];
    /**
     * @var string 
     */
    private $_templateFile;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        ob_start();
        ob_implicit_flush(false);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $template = trim(ob_get_clean());
        if ($this->itemView === null && !empty($template)) {
            $current = $this->getView()->getViewFile();
            $file = sprintf('%x/%x-%s', crc32(dirname($current)) % 0x100, crc32($current), $this->options['id']);
            $this->_templateFile = Yii::getAlias("@runtime/mdm-tabular/{$file}.php");
            if (!is_file($this->_templateFile) || filemtime($current) >= filemtime($this->_templateFile)) {
                FileHelper::createDirectory(dirname($this->_templateFile));
                $template = str_replace(array_keys($this->tags), array_values($this->tags), $template);
                file_put_contents($this->_templateFile, $template, LOCK_EX);
            }
        }
        if (empty($this->clientOptions['itemSelector']) && ($tag = ArrayHelper::getValue($this->itemOptions, 'tag', 'div'))) {
            Html::addCssClass($this->itemOptions, "mdm-item{$this->level}");
            $this->clientOptions['itemSelector'] = "{$tag}.mdm-item{$this->level}";
        }
        parent::run();
    }

    /**
     * @inheritdoc
     */
    public function renderItem($model, $key, $index)
    {
        $params = array_merge([
            'model' => $model,
            'key' => $key,
            'index' => $index,
            'widget' => $this,
            'form' => $this->form,
            ], $this->viewParams);

        // render content
        if ($this->itemView === null) {
            $content = $this->_templateFile ? $this->template($params) : $key;
        } elseif (is_string($this->itemView)) {
            $content = $this->getView()->render($this->itemView, $params);
        } else {
            $content = call_user_func($this->itemView, $model, $key, $index, $this);
        }
        $options = $this->itemOptions;
        $tag = ArrayHelper::remove($options, 'tag', 'div');
        if ($tag === false) {
            return $content;
        }
        $options['data-key'] = (string) $key;
        $options['data-index'] = (string) $index;
        return Html::tag($tag, $content, $options);
    }

    /**
     * Render template
     * @param array $params
     * @return string
     */
    protected function template($params = [])
    {
        return $this->getView()->renderPhpFile($this->_templateFile, $params);
    }
}
