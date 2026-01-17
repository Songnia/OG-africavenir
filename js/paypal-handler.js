/**
 * PayPal Payment Handler
 * Gère l'intégration du SDK PayPal pour les paiements
 */

class PayPalHandler {
    constructor(options = {}) {
        this.contributionID = options.contributionID || null;
        this.memberID = options.memberID || null;
        this.amount = options.amount || 0;
        this.description = options.description || 'Contribution AfricAvenir';
        this.onSuccess = options.onSuccess || this.defaultSuccessHandler;
        this.onError = options.onError || this.defaultErrorHandler;
        this.onCancel = options.onCancel || this.defaultCancelHandler;
        this.containerID = options.containerID || 'paypal-button-container';
    }

    /**
     * Initialiser et afficher les boutons PayPal
     */
    renderButtons() {
        if (!window.paypal) {
            console.error('PayPal SDK non chargé');
            this.onError('PayPal SDK non disponible');
            return;
        }

        const self = this;

        paypal.Buttons({
            // Style des boutons
            style: {
                layout: 'vertical',
                color: 'gold',
                shape: 'rect',
                label: 'paypal',
                height: 45
            },

            // Créer la commande
            createOrder: function (data, actions) {
                return self.createOrder();
            },

            // Gérer l'approbation du paiement
            onApprove: function (data, actions) {
                return self.capturePayment(data.orderID);
            },

            // Gérer les erreurs
            onError: function (err) {
                console.error('Erreur PayPal:', err);
                self.onError('Une erreur est survenue lors du paiement');
            },

            // Gérer l'annulation
            onCancel: function (data) {
                console.log('Paiement annulé par l\'utilisateur');
                self.onCancel();
            }
        }).render('#' + this.containerID);
    }

    /**
     * Créer une commande PayPal via l'API backend
     */
    async createOrder() {
        try {
            const response = await fetch('/OG-afrcavenir/api/paypal-create-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    amount: this.amount,
                    contributionID: this.contributionID,
                    description: this.description
                })
            });

            const data = await response.json();

            if (data.success && data.orderID) {
                return data.orderID;
            } else {
                throw new Error(data.message || 'Erreur lors de la création de la commande');
            }
        } catch (error) {
            console.error('Erreur createOrder:', error);
            this.onError('Impossible de créer la commande PayPal');
            throw error;
        }
    }

    /**
     * Capturer le paiement et vérifier auprès du serveur
     */
    async capturePayment(orderID) {
        try {
            // Afficher un indicateur de chargement
            // 3. Capturer le paiement via notre API backend
            const response = await fetch('api/payment/paypal/capture.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    orderID: orderID,
                    contributionID: this.contributionID,
                    memberID: this.memberID,
                    registrationID: this.registrationID // Add registrationID
                })
            });

            const data = await response.json();

            this.hideLoading();

            if (data.success) {
                this.onSuccess(data);
            } else {
                this.onError(data.message || 'Erreur lors de la vérification du paiement');
            }
        } catch (error) {
            this.hideLoading();
            console.error('Erreur capturePayment:', error);
            this.onError('Impossible de vérifier le paiement');
        }
    }

    /**
     * Gestionnaires par défaut
     */
    defaultSuccessHandler(data) {
        console.log('Paiement réussi:', data);
        showFeedbackModal('Paiement Réussi', 'Paiement confirmé avec succès!', 'success', function () {
            window.location.reload();
        });
    }

    defaultErrorHandler(message) {
        console.error('Erreur de paiement:', message);
        showFeedbackModal('Erreur de Paiement', 'Une erreur est survenue: ' + message, 'error');
    }

    async defaultCancelHandler() {
        console.log('Paiement annulé');

        if (this.contributionID) {
            try {
                await fetch('api/payment/paypal/cancel.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        contributionID: this.contributionID
                    })
                });
                console.log('Contribution marked as cancelled');
            } catch (e) {
                console.error('Error cancelling contribution:', e);
            }
        }

        showFeedbackModal('Paiement Annulé', 'Vous avez annulé le paiement.', 'warning');
    }

    /**
     * Afficher un indicateur de chargement
     */
    showLoading() {
        const container = document.getElementById(this.containerID);
        if (container) {
            const loader = document.createElement('div');
            loader.id = 'paypal-loader';
            loader.style.cssText = 'text-align: center; padding: 20px;';
            loader.innerHTML = '<p>⏳ Vérification du paiement en cours...</p>';
            container.appendChild(loader);
        }
    }

    /**
     * Masquer l'indicateur de chargement
     */
    hideLoading() {
        const loader = document.getElementById('paypal-loader');
        if (loader) {
            loader.remove();
        }
    }

    /**
     * Mettre à jour le montant dynamiquement
     */
    setAmount(newAmount) {
        this.amount = newAmount;
    }

    /**
     * Masquer le conteneur PayPal
     */
    hide() {
        const container = document.getElementById(this.containerID);
        if (container) {
            container.style.display = 'none';
        }
    }

    /**
     * Afficher le conteneur PayPal
     */
    show() {
        const container = document.getElementById(this.containerID);
        if (container) {
            container.style.display = 'block';
        }
    }
}

// Rendre la classe disponible globalement
window.PayPalHandler = PayPalHandler;
