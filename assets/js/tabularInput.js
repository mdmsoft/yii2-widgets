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
        template: undefined,
        multiSelect: false,
        counter: 0,
        btnDelSelector: '[data-action=\'delete\']',
        serialSelector: '.serial',
        btnAddSelector: undefined,
        itemSelector: undefined,
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var settings = $.extend({}, defaults, options || {});
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
                    $e.off('click.mdmTabularInput', settings.btnDelSelector)
                        .on('click.mdmTabularInput', settings.btnDelSelector, function (event) {
                            $e.mdmTabularInput('deleteRow', $(this).closest(settings.itemSelector));
                            event.preventDefault();
                            return false;
                        });
                }
                // select/togle row by click
                $e.off('click.mdmTabularInput', settings.itemSelector)
                    .on('click.mdmTabularInput', settings.itemSelector, function (e) {
                        var $this = $(this);
                        if ($this.is(settings.itemSelector)) {
                            $e.mdmTabularInput(e.ctrlKey ? 'toggleSelectRow' : 'selectRow', $this);
                        }
                    });

                $e.find(settings.itemSelector).each(function () {
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
            $e.find(settings.itemSelector).each(function () {
                $(this).find(settings.serialSelector).text(no++);
            });
            $e.trigger(events.change);
        },
        addRow: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var counter = settings.counter++;
            var template = settings.template.replace(/_dkey_/g, counter).replace(/_dindex_/g, counter);
            var $row = $(template);

            var event = $.Event(events.beforeAdd);
            $e.trigger(event, [$row]);
            if (event.result !== false) {
                $e.append($row);
                $e.trigger(events.afterAdd, [$row]);
                $e.mdmTabularInput('rearrage');
            }
            return $row;
        },
        deleteRow: function ($row) {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            if (!$row instanceof jQuery) {
                $row = $e.find(settings.itemSelector).eq($row);
            }

            var event = $.Event(events.beforeDelete);
            $e.trigger(event, [$row]);
            if (event.result !== false) {
                $row.remove();
                $e.trigger(events.afterDelete);
                $e.mdmTabularInput('rearrage');
            }
        },
        getSelectedRows: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var rows = [];
            $e.find(settings.itemSelector).filter('.selected').each(function () {
                rows.push($(this));
            });
            return rows;
        },
        getSelectedRow: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            return $e.find(settings.itemSelector).filter('.selected').first();
        },
        getAllRows: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var rows = [];
            $e.find(settings.itemSelector).each(function () {
                rows.push($(this));
            });
            return rows;
        },
        getValues: function () {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            var values = [];
            $e.find(settings.itemSelector).each(function () {
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
                $row = $e.find(settings.itemSelector).eq($row);
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
            return $e.find(settings.itemSelector).length;
        },
        toggleSelectRow: function ($row) {
            var $e = $(this);
            var settings = $e.data('mdmTabularInput').settings;
            if (!settings.multiSelect) {
                var has = $row.hasClass('selected');
                $e.find(settings.itemSelector).removeClass('selected');
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
                $e.find(settings.itemSelector).removeClass('selected');
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
