import { loadStripe } from "@stripe/stripe-js";

const initCheckout = async () => {
    const config = window.checkoutConfig || {};

    const paymentElement = document.getElementById("payment-element");
    const paymentMessage = document.getElementById("payment-message");
    const payBtn = document.getElementById("pay-btn");

    if (
        !paymentElement ||
        !paymentMessage ||
        !payBtn ||
        !config.paymentIntentUrl ||
        !config.stripePublishableKey
    ) {
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

            const response = await fetch(config.paymentIntentUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                body: JSON.stringify(details),
            });

            const contentType = response.headers.get("content-type") || "";
            const result = contentType.includes("application/json")
                ? await response.json()
                : { message: await response.text() };

            if (!response.ok || !result.client_secret) {
                const errors = result?.errors
                    ? Object.values(result.errors).flat().join(" ")
                    : result?.message ||
                      "Unable to initialise payment. Please check your details.";
                throw new Error(errors);
            }

            elements = stripeClient.elements({
                clientSecret: result.client_secret,
                appearance: { theme: "stripe" },
            });

            elements
                .create("payment", {
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
                })
                .mount("#payment-element");

            payBtn.disabled = false;
            payBtn.textContent = "Pay now";
        } catch (error) {
            paymentMessage.textContent = error.message;
            payBtn.disabled = true;
            payBtn.textContent = "Payment unavailable";
        }
    };

    payBtn.addEventListener("click", async () => {
        if (!elements) return;

        paymentMessage.textContent = "";
        payBtn.disabled = true;
        payBtn.textContent = "Processing...";

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
