/**
 * Configuration testing module for Mail/CalDAV/WebDAV
 */

/**
 * Get the CSRF token from the meta tag.
 * @returns {string}
 */
function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

/**
 * Test mail configuration.
 * @param {Object} formData - The form data
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function testMailConfig(formData) {
    const response = await fetch('/config/test-mail', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
        body: JSON.stringify(formData),
    });
    return response.json();
}

/**
 * Test CalDAV configuration.
 * @param {Object} formData - The form data
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function testCaldavConfig(formData) {
    const response = await fetch('/config/test-caldav', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
        body: JSON.stringify(formData),
    });
    return response.json();
}

/**
 * Test WebDAV configuration.
 * @param {Object} formData - The form data
 * @returns {Promise<{success: boolean, message: string}>}
 */
export async function testWebdavConfig(formData) {
    const response = await fetch('/config/test-webdav', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'Accept': 'application/json',
        },
        body: JSON.stringify(formData),
    });
    return response.json();
}

/**
 * Show configuration status in a status element.
 * @param {string} sectionId - The section ID (e.g., 'mail', 'caldav', 'webdav')
 * @param {'testing'|'success'|'error'} status - The status type
 * @param {string} message - The message to display
 */
export function showConfigStatus(sectionId, status, message) {
    const statusEl = document.getElementById(`${sectionId}-status`);
    if (!statusEl) return;

    const icons = {
        testing: '<svg class="config-status-icon animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>',
        success: '<svg class="config-status-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
        error: '<svg class="config-status-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
    };

    statusEl.className = `config-status config-status-${status}`;
    statusEl.innerHTML = `${icons[status]}<span class="config-status-message">${message}</span>`;
    statusEl.classList.remove('hidden');
}

/**
 * Hide configuration status.
 * @param {string} sectionId - The section ID
 */
export function hideConfigStatus(sectionId) {
    const statusEl = document.getElementById(`${sectionId}-status`);
    if (statusEl) {
        statusEl.classList.add('hidden');
    }
}

/**
 * Check if at least one field in a section has a value.
 * @param {string[]} fieldIds - Array of field IDs to check
 * @returns {boolean}
 */
export function hasAnyFieldValue(fieldIds) {
    return fieldIds.some(id => {
        const el = document.getElementById(id);
        return el && el.value && el.value.trim() !== '';
    });
}

/**
 * Get field value by ID.
 * @param {string} id - The field ID
 * @returns {string}
 */
export function getFieldValue(id) {
    const el = document.getElementById(id);
    return el?.value || '';
}

/**
 * Check if a checkbox is checked.
 * @param {string} id - The checkbox ID
 * @returns {boolean}
 */
export function isChecked(id) {
    const el = document.getElementById(id);
    return el?.checked || false;
}
