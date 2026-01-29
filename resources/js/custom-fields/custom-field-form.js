// Handle conditional field display and requirements based on type

document.addEventListener('DOMContentLoaded', () => {
    const typeSelect = document.getElementById('type');
    const requiredCheckbox = document.getElementById('required');
    const optionsField = document.getElementById('options_field');
    const optionsTextarea = document.getElementById('options');

    if (!typeSelect || !requiredCheckbox || !optionsField || !optionsTextarea) {
        return;
    }

    function updateFieldsBasedOnType() {
        const type = typeSelect.value;

        // Types that support "required" checkbox: text and textarea
        const supportsRequired = ['text', 'textarea'].includes(type);

        // Types that need options: select, checkbox, radio
        const needsOptions = ['select', 'checkbox', 'radio'].includes(type);

        if (supportsRequired) {
            // Enable required checkbox
            requiredCheckbox.disabled = false;
            requiredCheckbox.parentElement.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            // Disable and uncheck required checkbox
            requiredCheckbox.disabled = true;
            requiredCheckbox.checked = false;
            requiredCheckbox.parentElement.classList.add('opacity-50', 'cursor-not-allowed');
        }

        if (needsOptions) {
            // Show options field
            optionsField.classList.remove('hidden');
            optionsTextarea.required = true;
        } else {
            // Hide options field
            optionsField.classList.add('hidden');
            optionsTextarea.required = false;
        }
    }

    // Initialize on page load
    updateFieldsBasedOnType();

    // Update when type changes
    typeSelect.addEventListener('change', updateFieldsBasedOnType);
});