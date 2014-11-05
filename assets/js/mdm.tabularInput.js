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
(function($) {
    $.fn.mdmTabularInput = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.mdmTabularInput');
            return false;
        }
    };

    var defaults = {
        template: undefined,
        multiSelect: false,
        itemTag: 'div',
        counter: 0,
        initRow: undefined,
        afterAddRow: undefined,
        afterInit: undefined,
        beforeDelete: undefined,
        btnDelSelector: '[data-action=\'delete\']',
        serialSelector: '.serial',
        btnAddSelector: undefined,
    };

    var listData = {
    };

    var methods = {
        init: function(options) {
            return this.each(function() {
                var $e = $(this);
                var id = $e.prop('id');
                var settings = $.extend({}, defaults, options || {});
                listData[id] = {settings: settings};

                var btnDelSel = "#" + id + ' ' + settings.btnDelSelector;
                var rowSelector = "#" + id + " > " + settings.itemTag;

                // add button
                if (settings.btnAddSelector) {
                    $(settings.btnAddSelector)
                        .off('click.mdmTabularInput')
                        .on('click.mdmTabularInput', function(event) {
                            $e.mdmTabularInput('addRow');
                            event.preventDefault();
                            return false;
                        });
                }
                // delete button
                $(document)
                    .off('click.mdmTabularInput', btnDelSel)
                    .on('click.mdmTabularInput', btnDelSel, function(event) {
                        $e.mdmTabularInput('deleteRow',$(this).closest(rowSelector));
                        event.preventDefault();
                        return false;
                    });

                // select/togle row by click
                $(document)
                    .off('click.mdmTabularInput', rowSelector)
                    .on('click.mdmTabularInput', rowSelector, function() {
                        var $this = $(this);
                        if ($this.is(rowSelector)) {
                            $e.mdmTabularInput('toggleSelectRow', ($this));
                        }
                    });
                var elem = this;
                $(rowSelector).each(function() {
                    if (settings.initRow !== undefined) {
                        settings.initRow.call(elem, $(this));
                    }
                });

                if (settings.afterInit !== undefined) {
                    settings.afterInit.call(elem);
                }

                $e.mdmTabularInput('rearrage');
            });
        },
        rearrage: function() {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var no = 1;
            $e.children(settings.itemTag).each(function() {
                $(this).find(settings.serialSelector).text(no++);
            });
        },
        addRow: function() {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var $row = $(settings.template.replace(/_key_/g, settings.counter++));
            $e.append($row);
            if (settings.afterAddRow !== undefined) {
                settings.afterAddRow.call(this, $row);
            }
            $e.mdmTabularInput('rearrage');
            return $row;
        },
        deleteRow: function($row) {
            var $e = $(this);
            var id = $e.prop('id');
            var settings = listData[id].settings;
            if (!$row instanceof jQuery) {
                var rowSelector = "#" + id + " > " + settings.itemTag;
                $row = $(rowSelector).eq($row);
            }
            if (settings.beforeDelete === undefined || settings.beforeDelete.call(this, $row) !== false) {
                $row.remove();
                $e.mdmTabularInput('rearrage');
            }
        },
        getSelectedRows: function() {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var rows = [];
            $e.children(settings.itemTag + '.selected').each(function() {
                rows.push($(this));
            });
            return rows;
        },
        getSelectedRow: function() {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            return $e.children(settings.itemTag + '.selected').first();
        },
        getAllRows: function() {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var rows = [];
            $e.children(settings.itemTag).each(function() {
                rows.push($(this));
            });
            return rows;
        },
        toggleSelectRow: function($row) {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            if (!settings.multiSelect) {
                $e.children(settings.itemTag).removeClass('selected');
            }
            $row.toggleClass('selected');
        },
        selectRow: function($row) {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            if (!settings.multiSelect) {
                $e.children(settings.itemTag).removeClass('selected');
            }
            $row.addClass('selected');
        },
        destroy: function() {
            return this.each(function() {
                $(window).unbind('.mdmTabularInput');
                $(this).removeData('mdmTabularInput');
            });
        },
        data: function() {
            var id = $(this).prop('id');
            return listData[id];
        }
    };
})(window.jQuery);
