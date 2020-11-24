<template>
    <div>
        <div class="row middle-xs">
            <div class="col col-xs-6">
                <div class="form-group m-xs-b-4">
                    <label class="form-label"> Price </label>
                    <span class="form-control-static">
                        ${{ priceInDollars }}
                    </span>
                </div>
            </div>
            <div class="col col-xs-6">
                <div class="form-group m-xs-b-4">
                    <label class="form-label"> Qty </label>
                    <input v-model="quantity" class="form-control" />
                </div>
            </div>
        </div>
        <div class="text-right">
            <button
                class="btn btn-primary btn-block"
                @click="openStripe"
                :class="{ 'btn-loading': processing }"
                :disabled="processing"
            >
                Buy Tickets
            </button>
        </div>
        <transition name="fade" appear>
            <div
                class="modal-overlay"
                v-if="showModal"
                @click="showModal = false"
            ></div>
        </transition>
        <transition name="slide" appear>
            <div class="modal" v-if="showModal">
                <!-- CHECKOUT FORM AREA -->
                <!-- TODO: Refactor form to a different Vue Component -->
                <div class="container">
                    <div class="card bg-soft">
                        <form
                            ref="form"
                            action="//httpbin.org/post"
                            method="POST"
                            v-on:submit.prevent="createStripeToken"
                        >
                            <input type="hidden" name="token" />
                            <div class="group bg-soft">
                                <label>
                                    <span>Card</span>
                                    <div id="card-element" class="field"></div>
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <span>First name</span>
                                    <input
                                        id="first-name"
                                        name="first-name"
                                        class="field"
                                        placeholder="Manoj"
                                    />
                                </label>
                                <label>
                                    <span>Last name</span>
                                    <input
                                        id="last-name"
                                        name="last-name"
                                        class="field"
                                        placeholder="Halugona"
                                    />
                                </label>
                                <label>
                                    <span>Email</span>
                                    <input
                                        id="email-address"
                                        name="email-address"
                                        class="field"
                                        placeholder="john@example.com"
                                    />
                                </label>
                            </div>
                            <div class="group">
                                <label>
                                    <span>Address</span>
                                    <input
                                        id="address-line1"
                                        name="address_line1"
                                        class="field"
                                        placeholder="77 Winchester Lane"
                                    />
                                </label>
                                <label>
                                    <span>Address (cont.)</span>
                                    <input
                                        id="address-line2"
                                        name="address_line2"
                                        class="field"
                                        placeholder=""
                                    />
                                </label>
                                <label>
                                    <span>City</span>
                                    <input
                                        id="address-city"
                                        name="address_city"
                                        class="field"
                                        placeholder="Coachella"
                                    />
                                </label>
                                <label>
                                    <span>State</span>
                                    <input
                                        id="address-state"
                                        name="address_state"
                                        class="field"
                                        placeholder="SA"
                                    />
                                </label>
                                <label>
                                    <span>ZIP</span>
                                    <input
                                        id="address-zip"
                                        name="address_zip"
                                        class="field"
                                        placeholder="92236"
                                    />
                                </label>
                                <label>
                                    <span>Country</span>
                                    <select
                                        name="address_country"
                                        id="address-country"
                                        class="field"
                                    >
                                        <option value="IN">India</option>
                                        <option value="SG" selected
                                            >Singapore</option
                                        >
                                    </select>
                                </label>
                            </div>
                            <button class="btn btn-block" type="submit">
                                Pay ${{ totalPriceInDollars }}
                            </button>
                        </form>

                        <button
                            class="btn btn-danger-fill btn-block"
                            @click="showModal = false"
                            :class="{ 'btn-loading': processing }"
                            :disabled="processing"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <div class="outcome" ref="outcome">
                    <div class="error" ref="error"></div>
                    <div class="success" ref="success">
                        Success! Your Stripe token is
                        <span class="token" ref="token"></span>
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

<script>
export default {
    props: ["price", "concertTitle", "concertId"],
    data() {
        return {
            quantity: 1,
            stripeHandler: null,
            processing: false,
            showModal: false,
            cardElement: null,
            email: null
        };
    },
    computed: {
        description() {
            if (this.quantity > 1) {
                return `${this.quantity} tickets to ${this.concertTitle}`;
            }

            return `One ticket to ${this.concertTitle}`;
        },
        totalPrice() {
            return this.quantity * this.price;
        },
        priceInDollars() {
            return (this.price / 100).toFixed(2);
        },
        totalPriceInDollars() {
            return (this.totalPrice / 100).toFixed(2);
        }
    },
    methods: {
        initStripe() {
            const handler = Stripe(App.stripePublicKey);

            window.addEventListener("popstate", () => {
                handler.close();
            });

            return handler;
        },
        openStripe(callback) {
            this.showModal = true;
            this.$nextTick(function() {
                this.createCardElement();
            });
        },
        createCardElement() {
            var elements = this.stripeHandler.elements();

            var card = elements.create("card", {
                hidePostalCode: true,
                style: {
                    base: {
                        iconColor: "#666EE8",
                        color: "#31325F",
                        lineHeight: "40px",
                        fontWeight: 300,
                        fontFamily: "Helvetica Neue",
                        fontSize: "15px",

                        "::placeholder": {
                            color: "#CFD7E0"
                        }
                    }
                }
            });
            card.mount("#card-element");
            this.card = card;
        },
        createStripeToken(event) {
            let card = this.card;
            this.email = document.getElementById("email-address").value;
            let options = {
                        name:
                            document.getElementById("first-name").value +
                            " " +
                            document.getElementById("last-name").value,
                        address_line1: document.getElementById("address-line1")
                            .value,
                        address_line2: document.getElementById("address-line2")
                            .value,
                        address_city: document.getElementById("address-city")
                            .value,
                        address_state: document.getElementById("address-state")
                            .value,
                        address_zip: document.getElementById("address-zip")
                            .value,
                        address_country: document.getElementById(
                            "address-country"
                        ).value
                    };
            this.stripeHandler.createToken(card, options).then(
                    response => {
                        this.setStripeCheckoutOutcome(response);
                    }
                );
        },
        setStripeCheckoutOutcome(result) {
            var successElement = this.$refs.success;
            var errorElement = this.$refs.error;
            successElement.classList.remove("visible");
            errorElement.classList.remove("visible");

            if (result.token) {
                // Create order
                this.purchaseTickets(result.token);

                // Display the token
                this.$refs.token.textContent = result.token.id;
                successElement.classList.add("visible");
            } else if (result.error) {
                errorElement.textContent = result.error.message;
                errorElement.classList.add('visible');
            }
        },
        purchaseTickets(token) {
            this.processing = true;

            axios
                .post(`/concerts/${this.concertId}/orders`, {
                    email: this.email,
                    ticket_quantity: this.quantity,
                    payment_token: token.id
                })
                .then(response => {
                    window.location = `/orders/${response.data.confirmation_number}`;
                })
                .catch(response => {
                    this.processing = false;
                });
        }
    },
    created() {
        this.stripeHandler = this.initStripe();
    }
};
</script>
