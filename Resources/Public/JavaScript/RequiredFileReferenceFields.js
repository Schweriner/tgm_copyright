import DocumentService from '@typo3/core/document-service.js';

/**
 * Flags the copyright field of file references as required by toggling the
 * "has-error" class on the surrounding palette field group when no value is
 * set. This is a backend authoring aid enabled via the extension setting
 * "copyrightRequired".
 *
 * Rewritten for TYPO3 v14: the FormEngine no longer uses jQuery and dispatches
 * "t3-formengine-postfieldvalidation" as a native, bubbling CustomEvent on the
 * edit form. Both v13 and v14 behave this way.
 */
function validateReferenceFields() {
    const referenceFields = document.querySelectorAll(
        '.t3js-formengine-placeholder-formfield input[type="hidden"][name$="[copyright]"]',
    );

    referenceFields.forEach((field) => {
        if (!(field instanceof HTMLInputElement)) {
            return;
        }

        const parentFieldGroup = field.closest('.t3js-formengine-palette-field');
        if (parentFieldGroup === null) {
            return;
        }

        if (field.closest('.t3js-inline-record-deleted') !== null) {
            parentFieldGroup.classList.remove('has-error');
            return;
        }

        const nullCheckbox = parentFieldGroup.querySelector(
            '.t3js-form-field-eval-null-placeholder-checkbox input',
        );
        const nullCheckboxChecked = nullCheckbox instanceof HTMLInputElement && nullCheckbox.checked;

        const placeholderInput = parentFieldGroup.querySelector(
            '.t3js-formengine-placeholder-placeholder input',
        );
        const placeholderIsEmpty = !(placeholderInput instanceof HTMLInputElement) || placeholderInput.value === '';
        const hiddenIsEmpty = field.value === '';

        const hasError = (nullCheckboxChecked === false && placeholderIsEmpty)
            || (nullCheckboxChecked === true && hiddenIsEmpty);

        parentFieldGroup.classList.toggle('has-error', hasError);
    });
}

DocumentService.ready().then(() => {
    validateReferenceFields();

    // FormEngine dispatches this as a native bubbling CustomEvent on the form.
    document.addEventListener('t3-formengine-postfieldvalidation', () => {
        validateReferenceFields();
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (target instanceof HTMLInputElement
            && target.closest('.t3js-form-field-eval-null-placeholder-checkbox') !== null
        ) {
            validateReferenceFields();
        }
    });
});
