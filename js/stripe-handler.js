/**
 * Stripe Payment Handler
 */
class StripeHandler {
    constructor(options = {}) {
        this.publishableKey = options.publishableKey || null;
        this.stripe = null;

        if (this.publishableKey) {
            this.stripe = Stripe(this.publishableKey);
        } else {
            console.error('Stripe Publishable Key is missing');
        }
    }

    /**
     * Initiate Payment Flow
     */
    async initiatePayment(data) {
        try {
            // 1. Create Session via API
            const response = await fetch('api/stripe-create-session.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    userId: data.userId,
                    amount: data.amount,
                    description: data.description,
                    email: data.email,
                    registrationID: data.registrationID
                })
            });

            const result = await response.json();

            if (result.success && result.sessionId) {
                // 2. Redirect to Stripe Checkout
                const { error } = await this.stripe.redirectToCheckout({
                    sessionId: result.sessionId
                });

                if (error) {
                    throw new Error(error.message);
                }
            } else {
                throw new Error(result.message || 'Failed to create Stripe session');
            }

        } catch (error) {
            console.error('Stripe Payment Error:', error);
            alert('Une erreur est survenue lors de l\'initialisation du paiement. Veuillez réessayer.');
        }
    }
}

window.StripeHandler = StripeHandler;
