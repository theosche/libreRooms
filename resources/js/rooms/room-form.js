// Handle conditional field display and requirements

document.addEventListener('DOMContentLoaded', () => {
    // ================================
    // Image ordering and management
    // ================================
    const imagesSortable = document.getElementById('images-sortable');
    const imagesInput = document.getElementById('images-input');
    const imagesOrdered = document.getElementById('images-ordered');
    const removeImagesContainer = document.getElementById('remove-images-container');

    // Store new files with their preview data
    let newFiles = [];
    let newFileCounter = 0;

    if (imagesSortable && imagesInput) {
        // Handle new file selection
        imagesInput.addEventListener('change', (e) => {
            const files = Array.from(e.target.files);

            files.forEach(file => {
                if (!file.type.startsWith('image/')) return;

                const fileId = `new-${newFileCounter++}`;
                const reader = new FileReader();

                reader.onload = (event) => {
                    // Store file reference
                    newFiles.push({ id: fileId, file: file });

                    // Create preview element
                    const div = document.createElement('div');
                    div.className = 'image-item relative group cursor-move border-2 border-transparent hover:border-blue-400 rounded-lg transition-colors';
                    div.dataset.type = 'new';
                    div.dataset.fileId = fileId;
                    div.id = `image-container-${fileId}`;
                    div.draggable = true;

                    const orderBadge = imagesSortable.querySelectorAll('.image-item:not(.removed)').length + 1;

                    div.innerHTML = `
                        <img src="${event.target.result}" alt="${file.name}" class="w-full h-32 object-cover rounded-lg pointer-events-none">
                        <div class="absolute top-2 left-2 bg-green-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold image-order-badge">
                            ${orderBadge}
                        </div>
                        <button
                            type="button"
                            class="image-remove-btn absolute top-2 right-2 bg-red-600 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity ignore-styled-form"
                            title="Supprimer cette image"
                        >
                            <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <input type="hidden" name="image_order[]" value="new:${fileId}">
                    `;

                    imagesSortable.appendChild(div);
                    updateOrderBadges();
                    updateOrderedFilesInput();
                };

                reader.readAsDataURL(file);
            });

            // Clear the input so same file can be selected again if removed
            imagesInput.value = '';
        });

        // Drag and drop functionality
        let draggedElement = null;

        imagesSortable.addEventListener('dragstart', (e) => {
            if (!e.target.classList.contains('image-item')) return;
            if (e.target.classList.contains('removed')) {
                e.preventDefault();
                return;
            }
            draggedElement = e.target;
            e.target.classList.add('opacity-50');
            e.dataTransfer.effectAllowed = 'move';
        });

        imagesSortable.addEventListener('dragend', (e) => {
            if (!e.target.classList.contains('image-item')) return;
            e.target.classList.remove('opacity-50');
            draggedElement = null;
        });

        imagesSortable.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';

            const target = e.target.closest('.image-item');
            if (!target || target === draggedElement || target.classList.contains('removed')) return;

            const rect = target.getBoundingClientRect();
            const midpoint = rect.left + rect.width / 2;

            if (e.clientX < midpoint) {
                target.parentNode.insertBefore(draggedElement, target);
            } else {
                target.parentNode.insertBefore(draggedElement, target.nextSibling);
            }
        });

        imagesSortable.addEventListener('drop', (e) => {
            e.preventDefault();
            updateOrderBadges();
            updateOrderedFilesInput();
        });

        // Handle remove/restore button clicks using event delegation
        imagesSortable.addEventListener('click', (e) => {
            const button = e.target.closest('.image-remove-btn');
            if (!button) return;

            const container = button.closest('.image-item');
            if (!container) return;

            const isRemoved = container.classList.contains('removed');
            const imageType = container.dataset.type;

            if (isRemoved) {
                // Restore
                container.classList.remove('removed', 'opacity-50');
                container.draggable = true;
                button.classList.remove('bg-green-600');
                button.classList.add('bg-red-600');
                button.title = 'Supprimer cette image';
                button.innerHTML = `
                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                `;

                // Re-enable order input
                const orderInput = container.querySelector('input[name="image_order[]"]');
                if (orderInput) orderInput.disabled = false;

                // Remove from remove_images if existing
                if (imageType === 'existing') {
                    const imageId = container.dataset.imageId;
                    const removeInput = document.getElementById(`remove-image-input-${imageId}`);
                    if (removeInput) removeInput.remove();
                }

                // Restore file to newFiles if new
                if (imageType === 'new') {
                    const fileId = container.dataset.fileId;
                    const fileData = container._removedFileData;
                    if (fileData) {
                        newFiles.push(fileData);
                        delete container._removedFileData;
                    }
                }
            } else {
                // Remove
                container.classList.add('removed', 'opacity-50');
                container.draggable = false;
                button.classList.remove('bg-red-600');
                button.classList.add('bg-green-600');
                button.title = 'Restaurer cette image';
                button.innerHTML = `
                    <svg class="w-4 h-4 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                `;

                // Disable order input
                const orderInput = container.querySelector('input[name="image_order[]"]');
                if (orderInput) orderInput.disabled = true;

                // Add to remove_images if existing
                if (imageType === 'existing') {
                    const imageId = container.dataset.imageId;
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_images[]';
                    input.value = imageId;
                    input.id = `remove-image-input-${imageId}`;
                    removeImagesContainer.appendChild(input);
                }

                // Remove file from newFiles if new
                if (imageType === 'new') {
                    const fileId = container.dataset.fileId;
                    const fileIndex = newFiles.findIndex(f => f.id === fileId);
                    if (fileIndex > -1) {
                        container._removedFileData = newFiles[fileIndex];
                        newFiles.splice(fileIndex, 1);
                    }
                }
            }

            updateOrderBadges();
            updateOrderedFilesInput();
        });

        function updateOrderBadges() {
            const items = imagesSortable.querySelectorAll('.image-item:not(.removed)');
            items.forEach((item, index) => {
                const badge = item.querySelector('.image-order-badge');
                if (badge) badge.textContent = index + 1;
            });
        }

        function updateOrderedFilesInput() {
            // Build DataTransfer with files in correct order
            const dt = new DataTransfer();
            const items = imagesSortable.querySelectorAll('.image-item[data-type="new"]:not(.removed)');

            items.forEach(item => {
                const fileId = item.dataset.fileId;
                const fileData = newFiles.find(f => f.id === fileId);
                if (fileData) {
                    dt.items.add(fileData.file);
                }
            });

            imagesOrdered.files = dt.files;
        }
    }

    // ================================
    // Other form functionality
    // ================================

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
