/**
 * Stripe Integration Logic
 * Handles the interaction between the form and StripeHandler
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize Stripe Handler
    // Note: In a real app, fetch the key from a config endpoint or meta tag
    const stripeKey = 'pk_test_51ScUR60L3SDTXvtbTx8h3Y82kZ0YDlioes3a229kggKzCgHOL1bX67HNgisYlv0lT1TlQXGuoxCw6Hn5jQwVScT700mPd2f7wz'; // Should match config/stripe.php
    const stripeHandler = new StripeHandler({ publishableKey: stripeKey });

    const donationForm = document.getElementById('memberDonationForm');
    const donationModeSelect = document.getElementById('donationMode');
    const submitButton = document.getElementById('submitDonationButton');

    if (donationForm && donationModeSelect) {
        // Handle Form Submission
        donationForm.addEventListener('submit', async function (e) {
            const selectedMode = donationModeSelect.value;

            if (selectedMode === 'card') {
                e.preventDefault(); // Stop standard PHP submission

                const amount = document.getElementById('donationAmount').value;
                const motif = document.getElementById('donationMotif').value;

                if (!amount || amount < 100) {
                    alert('Montant invalide');
                    return;
                }

                // Show loading state
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Chargement Stripe...';
                submitButton.disabled = true;

                try {
                    // Initiate Stripe Payment via Handler
                    await stripeHandler.initiatePayment({
                        userId: null, // Will be handled by session in backend
                        amount: amount,
                        description: motif
                    });
                } catch (error) {
                    console.error('Stripe Integration Error:', error);
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            }
            // For other modes, let the form submit normally (PHP) or PayPal handler (JS)
        });
    }
    // Handle Registration Form
    const registerForm = document.getElementById('registerMemberForm');
    if (registerForm) {
        // Find the finish button or submit button
        const finishButton = registerForm.querySelector('.wizard-finish');

        if (finishButton) {
            finishButton.addEventListener('click', async function (e) {
                // Check if payment mode is card
                const paymentMode = document.querySelector('input[name="mode_paiement_contribution"]:checked');

                if (paymentMode && (paymentMode.value === 'card' || paymentMode.value === 'carte')) {
                    e.preventDefault();

                    // Validate amount
                    let amount = 0;
                    const amountRadio = document.querySelector('input[name="montant_contribution"]:checked');
                    if (amountRadio) {
                        if (amountRadio.value === 'libre') {
                            const libreVal = document.getElementById('regMontantLibreVal');
                            amount = libreVal ? parseFloat(libreVal.value) : 0;
                        } else {
                            amount = parseFloat(amountRadio.value);
                        }
                    }

                    if (!amount || amount < 100) {
                        alert('Montant invalide');
                        return;
                    }

                    // Show loading
                    const originalText = finishButton.textContent;
                    finishButton.textContent = 'Chargement Stripe...';
                    finishButton.disabled = true;

                    try {
                        // 1. Create Member via AJAX
                        const formData = new FormData(registerForm);
                        formData.append('ajax', '1');
                        // Ensure amount is passed correctly if needed by backend, though backend usually reads from POST
                        if (amountRadio && amountRadio.value === 'libre') {
                            formData.set('montant_contribution', amount);
                        }

                        const response = await fetch('member-create-handler.php', {
                            method: 'POST',
                            body: formData
                        });

                        const data = await response.json();

                        if (data.success) {
                            // 2. Initiate Stripe Payment
                            // Check if we have registrationID (new flow) or id (legacy/admin)
                            const paymentData = {
                                amount: amount,
                                description: 'Inscription Membre',
                                email: document.getElementById('regEmail').value
                            };

                            if (data.registrationID) {
                                paymentData.registrationID = data.registrationID;
                            } else if (data.id) {
                                paymentData.userId = data.id;
                            } else {
                                throw new Error('ID manquant dans la réponse');
                            }

                            await stripeHandler.initiatePayment(paymentData);
                        } else {
                            throw new Error(data.message || 'Erreur création membre');
                        }

                    } catch (error) {
                        console.error('Registration Stripe Error:', error);
                        // Show actual error message from backend if available
                        alert(error.message || 'Une erreur est survenue lors de l\'inscription.');
                        finishButton.textContent = originalText;
                        finishButton.disabled = false;
                        // Ensure we don't proceed (though we are in catch block so execution flow stops here for this event handler)
                    }
                }
            });
        }
    }
});
