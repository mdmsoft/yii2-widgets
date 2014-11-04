/**
 * DropdownLink
 * 
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
(function($) {
    $.fn.mdmDropdownLink = function(method) {
        if (methods[method]) {
            return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {
            return methods.init.apply(this, arguments);
        } else {
            $.error('Method ' + method + ' does not exist on jQuery.mdmDropdownLink');
            return false;
        }
    };

    var defaults = {
        links: {},
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

                $e
                    .off('change.mdmDropdownLink')
                    .on('change.mdmDropdownLink', function(event) {
                        window.location.href = settings.links[this.value];
                        event.preventDefault();
                        return false;
                    });
            });
        },
        destroy: function() {
            return this.each(function() {
                $(window).unbind('.mdmDropdownLink');
                $(this).removeData('mdmDropdownLink');
            });
        },
        data: function() {
            var id = $(this).prop('id');
            return listData[id];
        }
    };
})(window.jQuery);

