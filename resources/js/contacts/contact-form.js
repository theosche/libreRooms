// Utility functions to show/hide DOM elements
function showDOM(elem) {
    if (!elem) return;
    elem.classList.remove('hidden');
}

function hideDOM(elem) {
    if (!elem) return;
    elem.classList.add('hidden');
}

// Handle data-show-when attribute
function dataShowWhen(type) {
    // Hide all elements with data-show-when
    document.querySelectorAll('[data-show-when]').forEach((elem) => {
        hideDOM(elem);
    });

    // Show elements matching the selected type
    document.querySelectorAll(`[data-show-when="${type}"]`).forEach((elem) => {
        showDOM(elem);
    });

    // Update entity_name required attribute
    const entityNameInput = document.getElementById("entity_name");
    if (entityNameInput) {
        entityNameInput.required = (type === "organization");
    }
}

// Initialize data-show-when functionality
function initDataShowWhen() {
    const typeSelect = document.getElementById("contact_type");
    if (typeSelect) {
        typeSelect.addEventListener('change', (event) => {
            dataShowWhen(event.target.value);
        });
        // Initialize on page load
        dataShowWhen(typeSelect.value);
    }

    // Handle invoice email checkbox
    const invoiceEmailCheckbox = document.getElementById("has_invoice_email");
    if (invoiceEmailCheckbox) {
        const invoiceEmailField = document.querySelector('[data-toggle="invoice-email"]');
        invoiceEmailCheckbox.addEventListener('change', () => {
            invoiceEmailCheckbox.checked ? showDOM(invoiceEmailField) : hideDOM(invoiceEmailField);
        });
        // Initialize on page load
        invoiceEmailCheckbox.checked ? showDOM(invoiceEmailField) : hideDOM(invoiceEmailField);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    initDataShowWhen();
});