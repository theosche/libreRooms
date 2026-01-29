import {
    testMailConfig,
    testCaldavConfig,
    testWebdavConfig,
    showConfigStatus,
    hideConfigStatus,
    hasAnyFieldValue,
    getFieldValue,
} from '../config-test.js';

/**
 * Get mail configuration data from the form.
 */
function getMailFormData() {
    return {
        entity_type: 'system',
        entity_id: window.systemSettingsId || null,
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
        entity_type: 'system',
        entity_id: window.systemSettingsId || null,
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
        entity_type: 'system',
        entity_id: window.systemSettingsId || null,
        webdav_endpoint: getFieldValue('webdav_endpoint'),
        webdav_user: getFieldValue('webdav_user'),
        webdav_pass: getFieldValue('webdav_pass'),
        webdav_save_path: getFieldValue('webdav_save_path'),
    };
}

/**
 * Check if CalDAV section has any fields filled.
 */
function shouldTestCaldav() {
    return hasAnyFieldValue(['dav_url', 'dav_user', 'dav_pass']);
}

/**
 * Check if WebDAV section has any fields filled.
 */
function shouldTestWebdav() {
    return hasAnyFieldValue(['webdav_endpoint', 'webdav_user', 'webdav_pass', 'webdav_save_path']);
}

/**
 * Run all configuration tests.
 * @returns {Promise<{mail: Object|null, caldav: Object|null, webdav: Object|null}>}
 */
async function runTests() {
    const results = {
        mail: null,
        caldav: null,
        webdav: null,
    };

    const tests = [];

    // Mail is always required for system settings
    showConfigStatus('mail', 'testing', 'Test en cours...');
    tests.push(
        testMailConfig(getMailFormData()).then(result => {
            results.mail = result;
            showConfigStatus('mail', result.success ? 'success' : 'error', result.message);
        }).catch(err => {
            results.mail = { success: false, message: 'Erreur réseau' };
            showConfigStatus('mail', 'error', 'Erreur réseau');
        })
    );

    // CalDAV only if at least one field is filled
    if (shouldTestCaldav()) {
        showConfigStatus('caldav', 'testing', 'Test en cours...');
        tests.push(
            testCaldavConfig(getCaldavFormData()).then(result => {
                results.caldav = result;
                showConfigStatus('caldav', result.success ? 'success' : 'error', result.message);
            }).catch(err => {
                results.caldav = { success: false, message: 'Erreur réseau' };
                showConfigStatus('caldav', 'error', 'Erreur réseau');
            })
        );
    } else {
        hideConfigStatus('caldav');
    }

    // WebDAV only if at least one field is filled
    if (shouldTestWebdav()) {
        showConfigStatus('webdav', 'testing', 'Test en cours...');
        tests.push(
            testWebdavConfig(getWebdavFormData()).then(result => {
                results.webdav = result;
                showConfigStatus('webdav', result.success ? 'success' : 'error', result.message);
            }).catch(err => {
                results.webdav = { success: false, message: 'Erreur réseau' };
                showConfigStatus('webdav', 'error', 'Erreur réseau');
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
    // Mail must pass
    if (!results.mail?.success) return false;
    // CalDAV must pass if tested
    if (results.caldav !== null && !results.caldav.success) return false;
    // WebDAV must pass if tested
    if (results.webdav !== null && !results.webdav.success) return false;
    return true;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
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
});
