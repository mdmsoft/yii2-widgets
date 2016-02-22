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
        arrange: 'arrange'
    };
    var defaults = {
        template: undefined,
        multiSelect: false,
        itemTag: 'div',
        counter: 0,
        btnDelSelector: '[data-action=\'delete\']',
        serialSelector: '.serial',
        btnAddSelector: undefined,
        itemSelector: undefined,
    };

    var listData = {
    };

    var methods = {
        init: function (options) {
            return this.each(function () {
                var $e = $(this);
                var id = $e.prop('id');
                var settings = $.extend({}, defaults, options || {});
                listData[id] = {settings: settings};

                var btnDelSel = "#" + id + ' ' + settings.btnDelSelector;
                if (settings.itemSelector === undefined) {
                    settings.itemSelector = "#" + id + " > " + settings.itemTag;
                }

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
                $(document)
                    .off('click.mdmTabularInput', btnDelSel)
                    .on('click.mdmTabularInput', btnDelSel, function (event) {
                        $e.mdmTabularInput('deleteRow', $(this).closest(settings.itemSelector));
                        event.preventDefault();
                        return false;
                    });

                // select/togle row by click
                $(document)
                    .off('click.mdmTabularInput', settings.itemSelector)
                    .on('click.mdmTabularInput', settings.itemSelector, function () {
                        var $this = $(this);
                        if ($this.is(settings.itemSelector)) {
                            $e.mdmTabularInput('toggleSelectRow', ($this));
                        }
                    });

                $(settings.itemSelector).each(function () {
                    $e.trigger(events.initRow, [$(this)]);
                });
                $e.trigger(events.init);

                $e.mdmTabularInput('rearrage');
            });
        },
        rearrage: function () {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var no = 1;
            $(settings.itemSelector).each(function () {
                $(this).find(settings.serialSelector).text(no++);
            });
            $e.trigger(events.arrange);
        },
        addRow: function () {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var $row = $(settings.template.replace(/_key_/g, settings.counter++));

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
            var id = $e.prop('id');
            var settings = listData[id].settings;
            if (!$row instanceof jQuery) {
                $row = $(settings.itemSelector).eq($row);
            }

            var event = $.Event(events.beforeDelete);
            $e.trigger(event, [$row]);
            if (event.result !== false) {
                $row.remove();
                $e.mdmTabularInput('rearrage');
            }
        },
        getSelectedRows: function () {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var rows = [];
            $(settings.itemSelector).filter('.selected').each(function () {
                rows.push($(this));
            });
            return rows;
        },
        getSelectedRow: function () {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            return $(settings.itemSelector).filter('.selected').first();
        },
        getAllRows: function () {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var rows = [];
            $(settings.itemSelector).each(function () {
                rows.push($(this));
            });
            return rows;
        },
        getValues: function () {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var values = [];
            $(settings.itemSelector).each(function () {
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
            var settings = listData[$e.prop('id')].settings;
            if (!$row instanceof jQuery) {
                $row = $(settings.itemSelector).eq($row);
            }

            var value = {};
            $row.find(':input[data-field]').each(function () {
                value[$(this).data('field')] = $(this).val();
            });
            return value;
        },
        getCount:function(){
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            return $(settings.itemSelector).length;
        },
        toggleSelectRow: function ($row) {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            if (!settings.multiSelect) {
                var has = $row.hasClass('selected');
                $(settings.itemSelector).removeClass('selected');
                if (!has) {
                    $row.addClass('selected');
                }
            } else {
                $row.toggleClass('selected');
            }
        },
        selectRow: function ($row) {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            if (!settings.multiSelect) {
                $(settings.itemSelector).removeClass('selected');
            }
            $row.addClass('selected');
        },
        destroy: function () {
            return this.each(function () {
                $(window).unbind('.mdmTabularInput');
                $(this).removeData('mdmTabularInput');
            });
        },
        data: function () {
            var id = $(this).prop('id');
            return listData[id];
        }
    };
})(window.jQuery);
