(
    function ($) {
        "use strict";

        function loadCf7SendPulseAdditionalVariables()
        {
            var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php';
            var $variablesContainer = $('[data-ui-component="cf7-sendpulse-additional-variables"]');
            var $variablesLoader = $('[data-ui-component="cf7-sendpulse-additional-variables-loader"]');

            $variablesLoader.show();
            $variablesContainer.html('');

            $.post(ajaxUrl, {
                action: 'cf7SendPulseGetFields',
                bookID: $('[data-ui-component="cf7-sendpulse-mailing-list"]').val(),
                formID: $variablesContainer.data('form-id')
            })
                .success(function (response) {
                    $variablesLoader.hide();
                    $variablesContainer.html(response);
                });
        }

        loadCf7SendPulseAdditionalVariables();

        $(document).on('change', '[data-ui-component="cf7-sendpulse-mailing-list"]', function () {
            loadCf7SendPulseAdditionalVariables();
        });
    }
)(jQuery);
