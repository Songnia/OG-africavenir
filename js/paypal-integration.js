/**
 * Extension pour js/main.js
 * Gestion de l'intégration PayPal dans les formulaires
 */

// Variables globales pour PayPal
let paypalHandler = null;
let contributionIdForPayment = null;

// Initialiser PayPal quand le mode est sélectionné
document.addEventListener('DOMContentLoaded', function () {

    // ====== POUR LA PAGE D'INSCRIPTION (dashboard-register-member.php) ======
    const paymentModeRadios = document.getElementsByName('mode_paiement_contribution');
    const paypalContainer = document.getElementById('paypal-button-container');
    const paypalInfo = document.getElementById('paypal-info');
    const finishButton = document.querySelector('.wizard-finish');

    if (paymentModeRadios.length > 0 && paypalContainer) {
        // Écouter les changements de mode de paiement
        paymentModeRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'paypal') {
                    // Afficher le conteneur PayPal
                    paypalContainer.style.display = 'block';
                    if (paypalInfo) paypalInfo.style.display = 'block';

                    // Masquer le bouton Finish standard
                    if (finishButton) finishButton.style.display = 'none';

                    // Initialiser PayPal si pas encore fait
                    if (!paypalHandler && window.PayPalHandler) {
                        initializePayPalForRegistration();
                    }
                } else {
                    // Masquer le conteneur PayPal
                    paypalContainer.style.display = 'none';
                    if (paypalInfo) paypalInfo.style.display = 'none';

                    // Réafficher le bouton Finish standard
                    if (finishButton) finishButton.style.display = 'block';
                }
            });
        });
    }

    // ====== POUR LA PAGE DE CONTRIBUTION (member-make-donation.php) ======
    const donationModeSelect = document.getElementById('donationMode');
    const paymentDetailsSection = document.getElementById('paymentDetailsSection');
    const submitDonationButton = document.getElementById('submitDonationButton');

    if (donationModeSelect && paymentDetailsSection) {
        donationModeSelect.addEventListener('change', function () {
            if (this.value === 'paypal') {
                // Afficher la section PayPal
                paymentDetailsSection.style.display = 'block';

                // Masquer le bouton submit standard
                if (submitDonationButton) submitDonationButton.style.display = 'none';

                // Initialiser PayPal pour contribution
                if (!paypalHandler && window.PayPalHandler) {
                    initializePayPalForContribution();
                }
            } else {
                // Masquer la section PayPal
                paymentDetailsSection.style.display = 'none';

                // Réafficher le bouton submit standard
                if (submitDonationButton) submitDonationButton.style.display = 'block';
            }
        });
    }
});

/**
 * Initialiser PayPal pour le formulaire d'inscription
 */
function initializePayPalForRegistration() {
    const form = document.getElementById('registerMemberForm');
    if (!form) return;

    console.log('Initializing PayPal for registration...');

    // Obtenir le montant maintenant
    const montant = getMontantContribution();

    if (!montant || montant < 1000) {
        showFeedbackModal('Montant Invalide', 'Veuillez sélectionner un montant de contribution valide (minimum 1000 FCFA)', 'warning');
        return;
    }

    // Créer le handler PayPal IMMÉDIATEMENT avec un workflow spécial
    paypalHandler = new PayPalHandler({
        contributionID: null, // Sera défini après création
        memberID: null,
        amount: montant,
        description: 'Inscription AfricAvenir',
        containerID: 'paypal-button-container',
        onSuccess: function (paymentData) {
            console.log('Payment successful:', paymentData);

            // Afficher message de succès puis redirection
            showFeedbackModal('Paiement Réussi', 'Paiement confirmé! Redirection...', 'success', function () {
                if (paymentData.transaction_id) {
                    window.location.href = 'registration-success.php?transaction_id=' + paymentData.transaction_id;
                } else {
                    // Fallback
                    window.location.href = 'dashboard-list-member.php';
                }
            });
        },
        onError: function (error) {
            console.error('Payment error:', error);
            showFeedbackModal('Erreur de Paiement', 'Une erreur est survenue lors du paiement. Veuillez réessayer.', 'error');
        }
    });

    // Override createOrder pour créer d'abord le membre
    const originalCreateOrder = paypalHandler.createOrder.bind(paypalHandler);
    paypalHandler.createOrder = async function () {
        console.log('Creating member before PayPal order...');

        // 1. Soumettre le formulaire pour créer le membre d'abord
        const formData = new FormData(form);
        formData.append('ajax', '1');

        // Ajouter le montant libre si sélectionné
        const montantRadio = document.querySelector('input[name="montant_contribution"]:checked');
        if (montantRadio && montantRadio.value === 'libre') {
            const montantLibre = document.getElementById('regMontantLibreVal');
            if (montantLibre) {
                formData.set('montant_contribution', montantLibre.value);
            }
        }

        try {
            // Appel AJAX création membre
            const response = await fetch('member-create-handler.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            console.log('Member creation response:', data);

            if (data.success) {
                // 2. Inscription initiée avec succès !

                // Cas 1: Inscription temporaire (avec registrationID)
                if (data.registrationID) {
                    paypalHandler.registrationID = data.registrationID;
                    // On appelle l'API avec registrationID
                    return await createPayPalOrderViaAPI(null, montant, 'Inscription AfricAvenir', data.registrationID);
                }
                // Cas 2: Création directe (Legacy ou Admin)
                else if (data.id) {
                    paypalHandler.memberID = data.id;
                    if (data.credentials) {
                        paypalHandler.credentials = data.credentials;
                    }
                    return await createPayPalOrderViaAPI(data.id, montant, 'Inscription AfricAvenir');
                }
            } else {
                throw new Error(data.message || 'Impossible de créer le membre');
            }
        } catch (error) {
            console.error('Error creating member:', error);
            throw error;
        }
    };

    // Afficher les boutons PayPal MAINTENANT
    console.log('Rendering PayPal buttons...');
    paypalHandler.renderButtons();
}

/**
 * Helper pour appeler l'API PayPal backend
 */
async function createPayPalOrderViaAPI(memberID, amount, description, registrationID = null) {
    try {
        const payload = {
            amount: amount,
            description: description
        };

        if (registrationID) {
            payload.registrationID = registrationID;
        } else if (memberID) {
            payload.memberID = memberID;
        }

        const response = await fetch('api/payment/paypal/create.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (data.success && data.orderID) {
            // Important: On stocke l'ID de contribution retourné par l'API
            // pour l'utiliser lors de la capture
            paypalHandler.contributionID = data.contributionID;
            return data.orderID;
        } else {
            throw new Error(data.message || 'Erreur API PayPal');
        }
    } catch (error) {
        console.error('API Error:', error);
        throw error;
    }
}

/**
 * Initialiser PayPal pour le formulaire de contribution
 */
function initializePayPalForContribution() {
    const form = document.getElementById('memberDonationForm');
    const amountInput = document.getElementById('donationAmount');
    const motifInput = document.getElementById('donationMotif');

    if (!form || !amountInput) return;

    console.log('Initializing PayPal for contribution...');

    const amount = parseFloat(amountInput.value);
    const motif = motifInput ? motifInput.value : 'Contribution';

    if (!amount || amount < 1000) {
        showFeedbackModal('Montant Invalide', 'Le montant minimum est de 1000 FCFA', 'warning');
        return;
    }

    // Créer le handler PayPal IMMÉDIATEMENT
    paypalHandler = new PayPalHandler({
        contributionID: null, // Sera défini après création
        amount: amount,
        description: motif,
        containerID: 'paypal-button-container',
        onSuccess: function (paymentData) {
            console.log('Payment successful:', paymentData);
            showFeedbackModal('Paiement Réussi', 'Paiement confirmé avec succès!', 'success', function () {
                window.location.href = 'member-contribution.php';
            });
        },
        onError: function (error) {
            console.error('Payment error:', error);
            showFeedbackModal('Erreur de Paiement', 'Une erreur est survenue lors du paiement. Veuillez réessayer.', 'error');
        }
    });

    // Override createOrder pour utiliser notre API
    paypalHandler.createOrder = async function () {
        console.log('Creating PayPal order via API...');
        // Pour une contribution simple, l'utilisateur est déjà connecté (session)
        // Donc memberID est optionnel (géré par session backend)
        return await createPayPalOrderViaAPI(null, amount, motif);
    };

    // Afficher les boutons PayPal MAINTENANT
    console.log('Rendering PayPal buttons...');
    paypalHandler.renderButtons();
}

/**
 * Obtenir le montant de contribution sélectionné
 */
function getMontantContribution() {
    const montantRadio = document.querySelector('input[name="montant_contribution"]:checked');
    if (!montantRadio) return 0;

    if (montantRadio.value === 'libre') {
        const montantLibre = document.getElementById('regMontantLibreVal');
        return montantLibre ? parseFloat(montantLibre.value) : 0;
    }

    return parseFloat(montantRadio.value);
}

/**
 * Afficher les identifiants du membre après création
 */
function displayMemberCredentials(credentials) {
    const credentialsDisplay = document.getElementById('credentialsDisplay');
    const usernameInput = document.getElementById('displayUsername');
    const passwordInput = document.getElementById('displayPassword');

    if (credentialsDisplay && usernameInput && passwordInput) {
        usernameInput.value = credentials.username;
        passwordInput.value = credentials.password;
        credentialsDisplay.style.display = 'block';

        // Passer à l'étape 4 (confirmation)
        const step3 = document.getElementById('step3');
        const step4 = document.getElementById('step4');
        if (step3) step3.classList.remove('active');
        if (step4) step4.classList.add('active');
    }
}
