/**
 * jQuery plugin for tabular input.
 * Allow to add and delete row.
 * 
 * ```javascript
 * $('#id').mdmTabularInput({
 *     
 * });
 * ```
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
(function ($) {
    $.fn.mdmTabularInput = function (method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.mdmTabularInput');
            return false;
        }
    };

    var events = {
        initRow: 'initRow',
        init: 'init',
        beforeAdd: 'beforeAdd',
        afterAdd: 'afterAdd',
        beforeDelete: 'beforeDelete',
        afterDelete: 'afterDelete',
        change: 'change'
    };
    var defaults = {
        container: undefined,
        template: undefined,
        multiSelect: false,
        counter: 0,
        btnDelSelector: '[data-action=\'delete\']',
        serialSelector: '.serial',
        btnAddSelector: undefined,
        itemSelector: undefined,
        formSelector: undefined,
        validations: undefined,
        replaces: {},
    };

    function element($e, container) {
        if (container) {
            return $e.find(container);
        } else {
            return $e;
        }
    }

    function replace(s, r, v) {
        for (var k in r) {
            s = s.replace(r[k], (typeof v == 'object') ? v[k] : v);
        }
        return s;
    }

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
                var $container = element($e, settings.container);

                $e.data('mdmTabularInput', {settings: settings});
                // add button
                if (settings.btnAddSelector) {
                    $(settings.btnAddSelector)
                        .off('click.mdmTabularInput')
                        .on('click.mdmTabularInput', function (event) {
                            $e.mdmTabularInput('addRow');
                            event.preventDefault();
                            return false;
                        });
                }
                // delete button
                if (settings.btnDelSelector) {
                    $container
                        .off('click.mdmTabularInput', settings.btnDelSelector)
                        .on('click.mdmTabularInput', settings.btnDelSelector, function (event) {
                            $e.mdmTabularInput('deleteRow', $(this).closest(settings.itemSelector));
                            event.preventDefault();
                            return false;
                        });
                }
                // select/togle row by click
                $container
                    .off('click.mdmTabularInput', settings.itemSelector)
                    .on('click.mdmTabularInput', settings.itemSelector, function (e) {
                        var $this = $(this);
                        if ($this.is(settings.itemSelector)) {
                            $e.mdmTabularInput(e.ctrlKey ? 'toggleSelectRow' : 'selectRow', $this);
                        }
                    });

                $container
                    .children(settings.itemSelector).each(function () {
                    $e.trigger(events.initRow, [$(this)]);
                });
                $e.trigger(events.init);

                $e.mdmTabularInput('rearrage');
            });
        },
        rearrage: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var no = 1;
            element($e, settings.container).children(settings.itemSelector).each(function () {
                $(this).find(settings.serialSelector).text(no++);
            });
            $e.trigger(events.change);
        },
        addRow: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var counter = settings.counter++;
            var template = replace(settings.template, settings.replaces, counter);
            var $row = $(template);

            var event = $.Event(events.beforeAdd);
            $e.trigger(event, [$row]);
            if (event.result !== false) {
                element($e, settings.container).append($row);
                $e.trigger(events.afterAdd, [$row]);
                // add js
                if (settings.templateJs) {
                    var js = replace(settings.templateJs, settings.replaces, counter);
                    eval(js);
                }
                // validation for active form
                if (settings.formSelector && settings.validations && settings.validations.length) {
                    var $form = $(settings.formSelector);
                    var validations = $.extend(true, {}, settings.validations);
                    $.each(validations, function () {
                        var validation = this;
                        $.each(['id', 'name', 'container', 'input'], function () {
                            validation[this] = replace(validation[this], settings.replaces, counter);
                        });
                        $form.yiiActiveForm('add', validation);
                    });
                }
                $e.mdmTabularInput('rearrage');
            }
            return $row;
        },
        deleteRow: function ($row) {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            if (!$row instanceof jQuery) {
                $row = element($e, settings.container).children(settings.itemSelector).eq($row);
            }

            var event = $.Event(events.beforeDelete);
            $e.trigger(event, [$row]);
            if (event.result !== false) {
                var vals = {};
                for (var k in settings.replaces) {
                    vals[k] = $row.data(k);
                }
                $row.remove();
                $e.trigger(events.afterDelete);
                if (settings.formSelector && settings.validations && settings.validations.length) {
                    var $form = $(settings.formSelector);
                    $.each(settings.validations, function () {
                        if (this.id) {
                            var sid = replace(this.id, settings.replaces, vals);
                            $form.yiiActiveForm('remove', sid);
                        }
                    });
                }
                $e.mdmTabularInput('rearrage');
            }
        },
        getSelectedRows: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var rows = [];
            element($e, settings.container).children(settings.itemSelector)
                .filter('.selected').each(function () {
                rows.push($(this));
            });
            return rows;
        },
        getSelectedRow: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            return element($e, settings.container).children(settings.itemSelector)
                .filter('.selected').first();
        },
        getAllRows: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var rows = [];
            element($e, settings.container).children(settings.itemSelector).each(function () {
                rows.push($(this));
            });
            return rows;
        },
        getValues: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var values = [];
            element($e, settings.container).children(settings.itemSelector).each(function () {
                var value = {};
                $(this).find(':input[data-field]').each(function () {
                    value[$(this).data('field')] = $(this).val();
                });
                values.push(value);
            });
            return values;
        },
        getValue: function ($row) {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            if (!$row instanceof jQuery) {
                $row = element($e, settings.container).children(settings.itemSelector).eq($row);
            }

            var value = {};
            $row.find(':input[data-field]').each(function () {
                value[$(this).data('field')] = $(this).val();
            });
            return value;
        },
        getCount: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            return element($e, settings.container).children(settings.itemSelector).length;
        },
        toggleSelectRow: function ($row) {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            if (!settings.multiSelect) {
                var has = $row.hasClass('selected');
                element($e, settings.container).children(settings.itemSelector).removeClass('selected');
                if (!has) {
                    $row.addClass('selected');
                }
            } else {
                $row.toggleClass('selected');
            }
        },
        selectRow: function ($row) {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            if (!settings.multiSelect) {
                element($e, settings.container).children(settings.itemSelector).removeClass('selected');
            }
            $row.addClass('selected');
        },
        destroy: function () {
            return this.each(function () {
                $(this).unbind('.mdmTabularInput');
                $(this).removeData('mdmTabularInput');
            });
        },
        data: function () {
            return $(this).data('mdmTabularInput');
        }
    };
})(window.jQuery);
