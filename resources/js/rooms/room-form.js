// Handle conditional field display and requirements

document.addEventListener('DOMContentLoaded', () => {
    // Max hours short - required if price_short is filled
    const priceShortInput = document.getElementById('price_short');
    const maxHoursShortInput = document.getElementById('max_hours_short');

    if (priceShortInput && maxHoursShortInput) {
        function toggleMaxHoursShort() {
            const hasPriceShort = priceShortInput.value.trim() !== '';
            if (hasPriceShort) {
                maxHoursShortInput.required = true;
                maxHoursShortInput.parentElement.querySelector('label').innerHTML =
                    'Heures max pour réservation courte <span>*</span>';
            } else {
                maxHoursShortInput.required = false;
                maxHoursShortInput.parentElement.querySelector('label').innerHTML =
                    'Heures max pour réservation courte';
            }
        }

        priceShortInput.addEventListener('input', toggleMaxHoursShort);
        toggleMaxHoursShort();
    }

    // Charter string - required unless charter_mode is NONE
    const charterModeSelect = document.getElementById('charter_mode');
    const charterStrField = document.getElementById('charter_str_field');
    const charterStrTextarea = document.getElementById('charter_str');
    const charterStrLabel = document.getElementById('charter_str_label');

    if (charterModeSelect && charterStrField && charterStrTextarea) {
        function toggleCharterStr() {
            const mode = charterModeSelect.value;

            if (mode === 'none') {
                charterStrField.classList.add('hidden');
                charterStrTextarea.required = false;
            } else {
                charterStrField.classList.remove('hidden');
                charterStrTextarea.required = true;

                // Update label based on mode
                if (mode === 'text') {
                    charterStrLabel.textContent = 'Contenu de la charte';
                } else if (mode === 'link') {
                    charterStrLabel.textContent = 'Lien vers la charte';
                }

                // Add required asterisk
                const asterisk = ' <span class="text-red-500">*</span>';
                charterStrLabel.innerHTML = charterStrLabel.textContent + asterisk;
            }
        }

        charterModeSelect.addEventListener('change', toggleCharterStr);
        toggleCharterStr();
    }

    // Secret message - required if use_secret is checked
    const useSecretCheckbox = document.getElementById('use_secret');
    const secretMessageField = document.getElementById('secret_message_field');
    const secretMessageTextarea = document.getElementById('secret_message');

    if (useSecretCheckbox && secretMessageField && secretMessageTextarea) {
        function toggleSecretMessage() {
            const isChecked = useSecretCheckbox.checked;

            if (isChecked) {
                secretMessageField.classList.remove('hidden');
                secretMessageTextarea.required = true;
                secretMessageField.querySelector('label').innerHTML =
                    'Message secret <span class="text-red-500">*</span>';
            } else {
                secretMessageField.classList.add('hidden');
                secretMessageTextarea.required = false;
            }
        }

        useSecretCheckbox.addEventListener('change', toggleSecretMessage);
        toggleSecretMessage();
    }

    // Update timezone placeholder when owner changes
    const ownerSelect = document.getElementById('owner_id');
    const timezoneSelect = document.getElementById('timezone');

    // External slot provider - enable/disable CalDAV option based on owner
    const externalSlotProviderSelect = document.getElementById('external_slot_provider');
    const caldavOption = document.getElementById('caldav-option');
    const davCalendarField = document.getElementById('dav_calendar_field');

    if (ownerSelect && externalSlotProviderSelect && caldavOption && window.ownersCaldavValid) {
        function updateCaldavAvailability() {
            const ownerId = ownerSelect.value;
            const hasCaldav = ownerId && window.ownersCaldavValid[ownerId];

            if (hasCaldav) {
                caldavOption.disabled = false;
            } else {
                caldavOption.disabled = true;
                // If caldav was selected and owner doesn't have it, reset to "Aucun"
                if (externalSlotProviderSelect.value === 'caldav') {
                    externalSlotProviderSelect.value = '';
                    toggleDavCalendarField(); // Trigger hide of dav_calendar field
                }
            }
        }

        function toggleDavCalendarField() {
            const provider = externalSlotProviderSelect.value;
            if (provider === 'caldav') {
                davCalendarField.classList.remove('hidden');
            } else {
                davCalendarField.classList.add('hidden');
            }
        }

        ownerSelect.addEventListener('change', updateCaldavAvailability);
        externalSlotProviderSelect.addEventListener('change', toggleDavCalendarField);

        // Initialize on page load
        updateCaldavAvailability();
        toggleDavCalendarField();
    }

    if (ownerSelect && timezoneSelect && window.ownerTimezones) {
        function updateTimezoneDefaultText() {
            const ownerId = ownerSelect.value;
            const defaultOption = timezoneSelect.querySelector('option[value=""]');

            if (defaultOption && ownerId && window.ownerTimezones[ownerId]) {
                const ownerTimezone = window.ownerTimezones[ownerId];
                defaultOption.textContent = `Paramètres par défaut (${ownerTimezone})`;
            } else if (defaultOption) {
                defaultOption.textContent = 'Paramètres par défaut';
            }
        }

        ownerSelect.addEventListener('change', updateTimezoneDefaultText);
    }
});
