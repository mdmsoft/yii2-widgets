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
        rowSelector: undefined,
        template: undefined,
        multiSelect: false,
        itemTag: 'div',
        counter: 0,
        initRow: undefined,
        afterAddRow: undefined,
        afterInit: undefined,
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

                var btnDelSel = "#" + id + " [data-action='delete']";
                var rowSelector = "#" + id + " > " + settings.itemTag;

                // delete button
                $(document)
                    .off('click.mdmTabularInput', btnDelSel)
                    .on('click.mdmTabularInput', btnDelSel, function(event) {
                        $(this).closest(rowSelector).remove();
                        $e.mdmTabularInput('rearrage');
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
                $(this).find('.serial').text(no++);
            });
        },
        addRow: function() {
            var $e = $(this);
            var settings = listData[$e.prop('id')].settings;
            var $row = $(settings.template.replace(/_key_/g, settings.counter++));
            if (settings.afterAddRow !== undefined) {
                settings.afterAddRow.call(this, $row);
            }
            $e.append($row);
            $e.mdmTabularInput('rearrage');
            return $row;
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

