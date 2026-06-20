import { loadStripe } from "@stripe/stripe-js";

const initCheckout = async () => {
    const config = window.checkoutConfig || {};
    const paymentElement = document.getElementById("payment-element");
    const paymentMessage = document.getElementById("payment-message");
    const payBtn = document.getElementById("pay-btn");

    const vallidate = !paymentElement || !paymentMessage || !payBtn || !config.stripePublishableKey;
    if (vallidate) {
        return;
    }

    const details = config.orderDetails || {};

    let stripe = null;
    let elements = null;

    const ensureStripe = async () => {
        if (stripe) {
            return stripe;
        }

        stripe = await loadStripe(config.stripePublishableKey);
        if (!stripe) {
            throw new Error(
                "Unable to load Stripe. Please check your connection and disable blockers, then refresh.",
            );
        }

        return stripe;
    };

    const initializePaymentElement = async () => {
        paymentMessage.textContent = "";
        payBtn.disabled = true;
        payBtn.textContent = "Loading payment form...";

        try {
            const stripeClient = await ensureStripe();
            const stripeClientSecret = config.stripeClientSecret;

            if (!stripeClientSecret) {
                throw new Error("Missing Stripe client secret.");
            }

            elements = stripeClient.elements({
                clientSecret: stripeClientSecret,
                appearance: { theme: "stripe" },
            });

            const paymentElement = elements.create("payment", {
                fields: {
                    billingDetails: {
                        name: "never",
                        email: "never",
                        address: "never",
                    },
                },
                terms: {
                    card: "never",
                },
            });

            paymentElement.mount("#payment-element");
            payBtn.disabled = false;
            payBtn.textContent = "Pay now";
        } catch (error) {
            paymentMessage.textContent = error.message || "Unable to load payment form.";
            payBtn.disabled = true;
            payBtn.textContent = "Payment unavailable";
        }
    };

    payBtn.addEventListener("click", async () => {
        if (!elements) return;

        paymentMessage.textContent = "";
        payBtn.disabled = true;
        payBtn.textContent = "Processing...";

        console.log("Confirming payment with details:", details);

        try {
            const { error } = await stripe.confirmPayment({
                elements,
                confirmParams: {
                    return_url: config.successUrl,
                    payment_method_data: {
                        billing_details: {
                            name: details.customer_name || "",
                            email: details.customer_email || "",
                            address: {
                                line1: details.shipping_line_1 || "",
                                line2: details.shipping_line_2 || "",
                                city: details.shipping_city || "",
                                state: details.shipping_state || "",
                                postal_code: details.shipping_postal_code || "",
                                country: details.shipping_country || "GB",
                            },
                        },
                    },
                },
            });

            if (error) {
                paymentMessage.textContent =
                    error.message || "Payment failed. Please try again.";
            }
            // On success Stripe redirects to successUrl
        } catch (error) {
            paymentMessage.textContent = error.message;
        } finally {
            payBtn.disabled = false;
            payBtn.textContent = "Pay now";
        }
    });

    initializePaymentElement();
};

window.addEventListener("load", initCheckout);
