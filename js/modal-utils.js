/**
 * Modal Utilities for AfricAvenir
 * Provides a reusable way to show feedback modals (Success, Error, Info)
 * Replaces native alert() calls.
 */

// Ensure the modal HTML exists in the DOM
function ensureModalExists() {
    if (document.getElementById('feedbackModal')) {
        return;
    }

    const modalHTML = `
    <div id="feedbackModal" class="modal" aria-hidden="true" role="dialog" style="z-index: 1050;">
        <div class="modal-dialog" style="max-width: 400px; margin-top: 10vh;">
            <div class="modal-content" style="border-radius: 8px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.2);">
                <header class="modal-header" style="padding: 15px 20px; display: flex; align-items: center; justify-content: space-between;">
                    <h3 class="modal-title" id="feedbackModalTitle" style="margin: 0; font-size: 1.2em; color: white;">Notification</h3>
                    <button type="button" class="btn-close-modal" onclick="closeFeedbackModal()" style="background: none; border: none; color: white; font-size: 1.5em; cursor: pointer;">&times;</button>
                </header>
                <div class="modal-body" style="padding: 25px 20px; text-align: center;">
                    <div id="feedbackIcon" style="font-size: 3em; margin-bottom: 15px;"></div>
                    <p id="feedbackMessage" style="font-size: 1.1em; color: #333; margin: 0; line-height: 1.5;"></p>
                </div>
                <footer class="modal-footer" style="padding: 15px 20px; background-color: #f8f9fa; text-align: center; border-top: 1px solid #eee;">
                    <button type="button" class="btn btn-primary" onclick="closeFeedbackModal()" id="feedbackCloseBtn" style="min-width: 100px;">OK</button>
                </footer>
            </div>
        </div>
    </div>
    <div id="feedbackBackdrop" class="modal-backdrop" style="display: none; z-index: 1040;"></div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

/**
 * Show a feedback modal
 * @param {string} title - Title of the modal
 * @param {string} message - Message content
 * @param {string} type - 'success', 'error', 'info'
 * @param {function} onClose - Optional callback when closed
 */
function showFeedbackModal(title, message, type = 'info', onClose = null) {
    ensureModalExists();

    const modal = document.getElementById('feedbackModal');
    const backdrop = document.getElementById('feedbackBackdrop') || document.querySelector('.modal-backdrop');
    const titleEl = document.getElementById('feedbackModalTitle');
    const messageEl = document.getElementById('feedbackMessage');
    const iconEl = document.getElementById('feedbackIcon');
    const headerEl = modal.querySelector('.modal-header');
    const closeBtn = document.getElementById('feedbackCloseBtn');

    // Configure styles based on type
    let headerColor = '#0dcaf0'; // Info default
    let icon = 'ℹ️';
    let btnClass = 'btn-primary';

    switch (type) {
        case 'success':
            headerColor = '#198754'; // Success Green
            icon = '✅';
            btnClass = 'btn-success'; // Ensure this class exists or use inline style
            closeBtn.style.backgroundColor = '#198754';
            closeBtn.style.color = 'white';
            break;
        case 'error':
            headerColor = '#dc3545'; // Danger Red
            icon = '❌';
            closeBtn.style.backgroundColor = '#dc3545';
            closeBtn.style.color = 'white';
            break;
        case 'warning':
            headerColor = '#ffc107'; // Warning Yellow
            icon = '⚠️';
            closeBtn.style.backgroundColor = '#ffc107';
            closeBtn.style.color = '#000';
            break;
        default:
            closeBtn.style.backgroundColor = '#0dcaf0';
            closeBtn.style.color = 'white';
    }

    // Apply content and styles
    titleEl.textContent = title;
    messageEl.innerHTML = message; // Allow HTML in message
    iconEl.textContent = icon;
    headerEl.style.backgroundColor = headerColor;

    // Show Modal
    modal.classList.add('open');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    if (backdrop) {
        backdrop.style.display = 'block';
    }

    // Handle Close Callback
    modal.onCloseCallback = onClose;
}

function closeFeedbackModal() {
    const modal = document.getElementById('feedbackModal');
    const backdrop = document.getElementById('feedbackBackdrop') || document.querySelector('.modal-backdrop');

    if (modal) {
        modal.classList.remove('open');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        if (backdrop) {
            backdrop.style.display = 'none';
        }

        // Execute callback if exists
        if (typeof modal.onCloseCallback === 'function') {
            modal.onCloseCallback();
            modal.onCloseCallback = null; // Reset
        }
    }
}

// Expose globally
window.showFeedbackModal = showFeedbackModal;
window.closeFeedbackModal = closeFeedbackModal;
