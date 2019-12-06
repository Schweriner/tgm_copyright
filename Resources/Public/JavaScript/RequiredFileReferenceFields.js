define(['jquery'], function($) {

    $(document).on('t3-formengine-postfieldvalidation',function() {
        validateReferenceFields();
    });

    $(document).on('change','.t3js-form-field-eval-null-placeholder-checkbox input', function() {
        validateReferenceFields();
    });

    function validateReferenceFields() {
        var $referenceFields = $('.t3js-formengine-placeholder-formfield input[type="hidden"][name$="[copyright]"]');
        $referenceFields.each(function() {
            var $parentFieldGroup = $(this).closest('.t3js-formengine-palette-field');
            if($(this).parents('.t3js-inline-record-deleted').length === 0) {
                if(false === $parentFieldGroup.find('.t3js-form-field-eval-null-placeholder-checkbox input').is(':checked')
                    && $parentFieldGroup.find('.t3js-formengine-placeholder-placeholder input').val().toString() === '') {
                    $parentFieldGroup.addClass('has-error');
                } else if(true === $parentFieldGroup.find('.t3js-form-field-eval-null-placeholder-checkbox input').is(':checked')
                    && $(this).val().toString() === '') {
                    $parentFieldGroup.addClass('has-error');
                } else {
                    $parentFieldGroup.removeClass('has-error');
                }
            } else {
                $parentFieldGroup.removeClass('has-error');
            }
        });
    }


});