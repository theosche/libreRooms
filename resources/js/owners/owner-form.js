import {
    testMailConfig,
    testCaldavConfig,
    testWebdavConfig,
    showConfigStatus,
    hideConfigStatus,
    getFieldValue,
    isChecked,
} from '../config-test.js';

// Toggle configuration sections based on switch state
function toggleConfigSection(switchElement, sectionId) {
    const isChecked = switchElement.checked;
    const defaultsSection = document.getElementById(`${sectionId}-defaults`);
    const inputsSection = document.getElementById(`${sectionId}-inputs`);

    if (isChecked) {
        // Use default config: show defaults, hide inputs
        defaultsSection?.classList.remove('hidden');
        inputsSection?.classList.add('hidden');
        // Disable all inputs in this section so they are not submitted
        inputsSection?.querySelectorAll('input').forEach(input => {
            input.disabled = true;
        });
    } else {
        // Use custom config: hide defaults, show inputs
        defaultsSection?.classList.add('hidden');
        inputsSection?.classList.remove('hidden');
        // Enable all inputs in this section
        inputsSection?.querySelectorAll('input').forEach(input => {
            input.disabled = false;
        });
    }
}

// Toggle payment instruction fields based on payment type
function togglePaymentFields(paymentType) {
    const paymentFields = document.getElementById('payment-fields');
    const bicSection = document.getElementById('payment-bic-section');
    const bankNameSection = document.getElementById('payment-bank-name-section');
    const addressSection = document.getElementById('payment-address-section');
    const besrSection = document.getElementById('payment-besr-section');
    const bicOptional = document.getElementById('payment-bic-optional');
    const bicHint = document.getElementById('payment-bic-hint');
    const addressOptional = document.getElementById('payment-address-optional');
    const addressRequired = document.getElementById('payment-address-required');

    if (!paymentType) {
        // No payment type selected - hide all payment fields
        paymentFields?.classList.add('hidden');
        paymentFields?.querySelectorAll('input').forEach(input => input.disabled = true);
        return;
    }

    // Show all payment fields
    paymentFields?.classList.remove('hidden');
    paymentFields?.querySelectorAll('input').forEach(input => input.disabled = false);

    // Hide BESR by default (only for swiss)
    besrSection?.classList.add('hidden');
    besrSection?.querySelectorAll('input').forEach(input => input.disabled = true);

    // Configure based on payment type
    if (paymentType === 'international') {
        // Show BIC (optional), bank name, and address (optional)
        bicSection?.classList.remove('hidden');
        bankNameSection?.classList.remove('hidden');
        addressSection?.classList.remove('hidden');
        bicOptional?.classList.remove('hidden');
        bicHint?.classList.add('hidden');
        addressOptional?.classList.remove('hidden');
        addressRequired?.classList.add('hidden');
    } else if (paymentType === 'sepa') {
        // Show BIC (required), hide bank name and address
        bicSection?.classList.remove('hidden');
        bankNameSection?.classList.add('hidden');
        addressSection?.classList.add('hidden');
        bicOptional?.classList.add('hidden');
        bicHint?.classList.remove('hidden');
        // Disable inputs in hidden sections
        bankNameSection?.querySelectorAll('input').forEach(input => input.disabled = true);
        addressSection?.querySelectorAll('input').forEach(input => input.disabled = true);
    } else if (paymentType === 'swiss') {
        // Hide BIC and bank name, show address (required) and BESR-ID
        bicSection?.classList.add('hidden');
        bankNameSection?.classList.add('hidden');
        addressSection?.classList.remove('hidden');
        besrSection?.classList.remove('hidden');
        besrSection?.querySelectorAll('input').forEach(input => input.disabled = false);
        addressOptional?.classList.add('hidden');
        addressRequired?.classList.remove('hidden');
        // Disable inputs in hidden sections
        bicSection?.querySelectorAll('input').forEach(input => input.disabled = true);
        bankNameSection?.querySelectorAll('input').forEach(input => input.disabled = true);
    }
}

// Toggle entire DAV config visibility
function toggleDavConfig(switchElement, configId) {
    const isChecked = switchElement.checked;
    const configSection = document.getElementById(configId);

    if (isChecked) {
        configSection?.classList.remove('hidden');
    } else {
        configSection?.classList.add('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    // Mail configuration
    const mailSwitch = document.getElementById('use_default_mail');
    if (mailSwitch) {
        toggleConfigSection(mailSwitch, 'mail');
        mailSwitch.addEventListener('change', () => toggleConfigSection(mailSwitch, 'mail'));
    }

    // CalDAV enable/disable
    const useCaldavSwitch = document.getElementById('use_caldav');
    if (useCaldavSwitch) {
        toggleDavConfig(useCaldavSwitch, 'caldav-config');
        useCaldavSwitch.addEventListener('change', () => toggleDavConfig(useCaldavSwitch, 'caldav-config'));
    }

    // CalDAV default/custom configuration
    const caldavSwitch = document.getElementById('use_default_caldav');
    if (caldavSwitch) {
        toggleConfigSection(caldavSwitch, 'caldav');
        caldavSwitch.addEventListener('change', () => toggleConfigSection(caldavSwitch, 'caldav'));
    }

    // WebDAV enable/disable
    const useWebdavSwitch = document.getElementById('use_webdav');
    if (useWebdavSwitch) {
        toggleDavConfig(useWebdavSwitch, 'webdav-config');
        useWebdavSwitch.addEventListener('change', () => toggleDavConfig(useWebdavSwitch, 'webdav-config'));
    }

    // WebDAV default/custom configuration
    const webdavSwitch = document.getElementById('use_default_webdav');
    if (webdavSwitch) {
        toggleConfigSection(webdavSwitch, 'webdav');
        webdavSwitch.addEventListener('change', () => toggleConfigSection(webdavSwitch, 'webdav'));
    }

    // Payment type selection
    const paymentTypeSelect = document.getElementById('payment_type');
    if (paymentTypeSelect) {
        togglePaymentFields(paymentTypeSelect.value);
        paymentTypeSelect.addEventListener('change', () => togglePaymentFields(paymentTypeSelect.value));
    }

    // Configuration testing on form submit
    setupConfigTesting();
});

/**
 * Get mail configuration data from the form.
 */
function getMailFormData() {
    return {
        entity_type: 'owner',
        entity_id: window.ownerId || null,
        mail_host: getFieldValue('mail_host'),
        mail_port: getFieldValue('mail_port'),
        mail: getFieldValue('mail'),
        mail_pass: getFieldValue('mail_pass'),
    };
}

/**
 * Get CalDAV configuration data from the form.
 */
function getCaldavFormData() {
    return {
        entity_type: 'owner',
        entity_id: window.ownerId || null,
        dav_url: getFieldValue('dav_url'),
        dav_user: getFieldValue('dav_user'),
        dav_pass: getFieldValue('dav_pass'),
    };
}

/**
 * Get WebDAV configuration data from the form.
 */
function getWebdavFormData() {
    return {
        entity_type: 'owner',
        entity_id: window.ownerId || null,
        webdav_endpoint: getFieldValue('webdav_endpoint'),
        webdav_user: getFieldValue('webdav_user'),
        webdav_pass: getFieldValue('webdav_pass'),
        webdav_save_path: getFieldValue('webdav_save_path'),
    };
}

/**
 * Determine if mail config should be tested.
 * Test if NOT using default config.
 */
function shouldTestMail() {
    const useDefault = document.getElementById('use_default_mail');
    // If checkbox doesn't exist or is disabled (no default available), always test custom
    if (!useDefault || useDefault.disabled) {
        return true;
    }
    return !useDefault.checked;
}

/**
 * Determine if CalDAV config should be tested.
 * Test if use_caldav is checked AND NOT using default config.
 */
function shouldTestCaldav() {
    if (!isChecked('use_caldav')) {
        return false;
    }
    const useDefault = document.getElementById('use_default_caldav');
    // If checkbox doesn't exist or is disabled (no default available), test custom
    if (!useDefault || useDefault.disabled) {
        return true;
    }
    return !useDefault.checked;
}

/**
 * Determine if WebDAV config should be tested.
 * Test if use_webdav is checked AND NOT using default config.
 */
function shouldTestWebdav() {
    if (!isChecked('use_webdav')) {
        return false;
    }
    const useDefault = document.getElementById('use_default_webdav');
    // If checkbox doesn't exist or is disabled (no default available), test custom
    if (!useDefault || useDefault.disabled) {
        return true;
    }
    return !useDefault.checked;
}

/**
 * Run all required configuration tests.
 */
async function runTests() {
    const results = {
        mail: null,
        caldav: null,
        webdav: null,
    };

    const tests = [];

    // Mail test
    if (shouldTestMail()) {
        showConfigStatus('mail', 'testing', 'Test en cours...');
        tests.push(
            testMailConfig(getMailFormData()).then(result => {
                results.mail = result;
                showConfigStatus('mail', result.success ? 'success' : 'error', result.message);
            })
        );
    } else {
        hideConfigStatus('mail');
    }

    // CalDAV test
    if (shouldTestCaldav()) {
        showConfigStatus('caldav', 'testing', 'Test en cours...');
        tests.push(
            testCaldavConfig(getCaldavFormData()).then(result => {
                results.caldav = result;
                showConfigStatus('caldav', result.success ? 'success' : 'error', result.message);
            })
        );
    } else {
        hideConfigStatus('caldav');
    }

    // WebDAV test
    if (shouldTestWebdav()) {
        showConfigStatus('webdav', 'testing', 'Test en cours...');
        tests.push(
            testWebdavConfig(getWebdavFormData()).then(result => {
                results.webdav = result;
                showConfigStatus('webdav', result.success ? 'success' : 'error', result.message);
            })
        );
    } else {
        hideConfigStatus('webdav');
    }

    await Promise.all(tests);
    return results;
}

/**
 * Check if all required tests passed.
 */
function allTestsPassed(results) {
    // Mail must pass if tested
    if (results.mail !== null && !results.mail.success) return false;
    // CalDAV must pass if tested
    if (results.caldav !== null && !results.caldav.success) return false;
    // WebDAV must pass if tested
    if (results.webdav !== null && !results.webdav.success) return false;
    return true;
}

/**
 * Setup configuration testing on form submit.
 */
function setupConfigTesting() {
    const form = document.querySelector('form.styled-form');
    if (!form) return;

    const submitButton = form.querySelector('button[type="submit"]');
    const originalButtonText = submitButton?.textContent || 'Enregistrer';
    let isSubmitting = false;

    form.addEventListener('submit', async (e) => {
        // If already validated and submitting, let it through
        if (isSubmitting) {
            return;
        }
        e.preventDefault();

        // Disable submit button and show loading state
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.textContent = 'Vérification...';
        }

        try {
            const results = await runTests();

            if (allTestsPassed(results)) {
                // All tests passed, submit the form
                isSubmitting = true;
                form.submit();
            } else {
                // Tests failed, re-enable submit button
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.textContent = originalButtonText;
                }
            }
        } catch (error) {
            console.error('Configuration test error:', error);
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = originalButtonText;
            }
        }
    });
}
