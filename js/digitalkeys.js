(function($) {
    $.product_digitalkeys = {
        /**
         * {Number}
         */
        service_id: 0,
        /**
         * {Number}
         */
        product_id: 0,
        /**
         * {Jquery object}
         */
        form: null,
        /**
         * Keep track changing of form
         * {String}
         */
        form_serialized_data: '',
        /**
         * {Jquery object}
         */
        container: null,
        button_color: null,
        /**
         * {Object}
         */
        options: {},
        init: function(options) {
            this.options = options;
            if (options.container) {
                if (typeof options.container === 'object') {
                    this.container = options.container;
                } else {
                    this.container = $(options.container);
                }
            }
            if (options.counter) {
                if (typeof options.counter === 'object') {
                    this.counter = options.counter;
                } else {
                    this.counter = $(options.counter);
                }
            }

            this.service_id = parseInt(this.options.service_id, 10) || 0;
            this.product_id = parseInt(this.options.product_id, 10) || 0;
            this.form = $('#s-product-save');

            if (this.product_id) {

                // maintain intearaction with $.product object



                $.product.editTabDigitalkeysBlur = function() {
                    var that = $.product_digitalkeys;

                    if (that.form_serialized_data != that.form.serialize()) {
                        $.product_digitalkeys.save();
                    }
                };

                $.product.editTabDigitalkeysSave = function() {
                    $.product_digitalkeys.save();
                };

                var that = this;
                var button = $('#s-product-save-button');

                // some extra initializing
                that.container.addClass('ajax');
                that.form_serialized_data = that.form.serialize();
                that.counter.text(that.options.count);

            }
            this.initButtons();
        },
        initButtons: function() {
            $('.add-digital-key').click(function() {
                var sku_id = $(this).closest('.value').find('input.sku-id').val();
                $(this).closest('.value').find('.digital-keys').append('<div class="digital-key"><input type="text" name="digital_keys[' + sku_id + '][]" value="" ><a class="delete-digital-key" href="#"><i class="icon16 delete"></i></a></div>')
                return false;
            });
            $('.digital-keys').on('click', '.delete-digital-key', function() {
                $(this).closest('.digital-key').remove();
                return false;
            });
        },
        save: function() {

            var form = this.form;
            var that = this;
            $.product.refresh('submit');

            var url = '?plugin=digitalkeys&action=save';
            return $.shop.jsonPost(
                    url,
                    form.serialize(),
                    function(r) {
                        $.product.refresh();
                        $('#s-product-save-button').removeClass('yellow green').addClass('green');

                        that.form_serialized_data = form.serialize();

                        $.products.dispatch();
                    }
            );
        },
    };
})(jQuery);