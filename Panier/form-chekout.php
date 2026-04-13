<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_checkout_form', $checkout );
?>
<style>
.checkout.woocommerce-checkout.checkout-maquette {
  --checkout-accent: #ff3f22;
  --checkout-accent-dark: #d7331a;
  --checkout-border: #d7d7d7;
  --checkout-muted: #767676;
  --checkout-text: #1b1b1b;
  --checkout-bg: #f7f7f7;
  --checkout-card: #ffffff;
  --checkout-shadow: 0 8px 24px rgba(17, 24, 39, 0.08);
  max-width: 1160px;
  margin: 0 auto;
  padding: 24px 20px 40px;
  color: var(--checkout-text);
}

.checkout-maquette__grid {
  display: grid;
  grid-template-columns: minmax(0, 1.65fr) minmax(320px, 0.95fr);
  gap: 36px;
  align-items: start;
}

.checkout-maquette__title {
  margin: 0 0 18px;
  font-size: clamp(28px, 2.8vw, 40px);
  line-height: 1.1;
  letter-spacing: -0.02em;
  color: var(--checkout-accent);
}

.checkout-maquette__main {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.checkout-maquette__section {
  position: relative;
  border-top: 1px solid var(--checkout-border);
  padding: 18px 0 6px;
}

.checkout-maquette__section::before {
  content: "";
  position: absolute;
  top: 22px;
  left: -26px;
  width: 10px;
  height: 10px;
  border-radius: 999px;
  background: #d6d6d6;
}

.checkout-maquette__section:first-of-type::before {
  background: var(--checkout-accent);
}

.checkout-maquette__section-title {
  margin: 0 0 12px;
  font-size: 22px;
  line-height: 1.2;
  font-weight: 700;
}

.checkout-maquette__section .woocommerce-shipping-fields > h3,
.checkout-maquette__section .woocommerce-billing-fields > h3,
.checkout-maquette__section .woocommerce-additional-fields > h3,
.checkout-maquette__section .woocommerce-shipping-fields__field-wrapper > h3,
.checkout-maquette__section .shipping_address > h3 {
  display: none;
}

.checkout-maquette .woocommerce-shipping-fields {
  margin-bottom: 0;
}

.checkout-maquette #ship-to-different-address {
  margin: 0 0 14px;
  font-size: 15px;
}

.checkout-maquette .woocommerce form .form-row,
.checkout-maquette .form-row {
  margin: 0 0 10px;
  padding: 0;
}

.checkout-maquette .form-row-first,
.checkout-maquette .form-row-last {
  width: calc(50% - 4px);
}

.checkout-maquette .form-row-first {
  float: left;
}

.checkout-maquette .form-row-last {
  float: right;
}

.checkout-maquette .form-row-wide,
.checkout-maquette .woocommerce-input-wrapper,
.checkout-maquette .select2-container,
.checkout-maquette .select2-selection,
.checkout-maquette input.input-text,
.checkout-maquette textarea,
.checkout-maquette select {
  width: 100%;
}

.checkout-maquette label {
  display: block;
  margin: 0 0 4px;
  color: var(--checkout-muted);
  font-size: 11px;
  text-transform: uppercase;
  letter-spacing: 0.03em;
}

.checkout-maquette .select2-container--default .select2-selection--single,
.checkout-maquette input.input-text,
.checkout-maquette textarea,
.checkout-maquette select {
  border: 1px solid #a9a9a9;
  border-radius: 5px;
  min-height: 45px;
  padding: 11px 12px;
  font-size: 14px;
  color: var(--checkout-text);
  background: #fff;
}

.checkout-maquette .select2-container--default .select2-selection--single {
  padding: 0 10px;
  display: flex;
  align-items: center;
}

.checkout-maquette .select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: 1;
  padding-left: 0;
}

.checkout-maquette .select2-container--default .select2-selection--single .select2-selection__arrow {
  top: 10px;
}

.checkout-maquette .input-text:focus,
.checkout-maquette textarea:focus,
.checkout-maquette select:focus,
.checkout-maquette .select2-container--default.select2-container--focus .select2-selection--single {
  outline: none;
  border-color: var(--checkout-accent);
  box-shadow: 0 0 0 2px rgba(255, 63, 34, 0.1);
}

.checkout-maquette__summary {
  position: sticky;
  top: 24px;
  border: 1px solid var(--checkout-border);
  border-radius: 10px;
  padding: 14px 16px;
  background: var(--checkout-card);
  box-shadow: var(--checkout-shadow);
}

.checkout-maquette__summary-title {
  margin: 0 0 10px;
  font-size: 18px;
  line-height: 1.2;
}

.checkout-maquette__coupon {
  margin: 0 0 12px;
  padding: 0 0 10px;
  border-bottom: 1px solid #efefef;
}

.checkout-maquette__coupon .woocommerce-info {
  margin: 0;
  padding: 10px;
  font-size: 13px;
  border-top-color: var(--checkout-accent);
}

.checkout-maquette__coupon .woocommerce-info::before {
  display: none;
}

.checkout-maquette__coupon .checkout_coupon {
  margin-top: 8px;
}

.checkout-maquette__coupon .form-row {
  margin-bottom: 8px;
}

.checkout-maquette__coupon .button {
  width: 100%;
  white-space: normal;
  line-height: 1.3;
  min-height: 44px;
  height: auto;
  text-align: center;
}

.checkout-maquette__review .shop_table {
  margin: 0;
  border: 0;
  font-size: 14px;
}

.checkout-maquette__review .shop_table th,
.checkout-maquette__review .shop_table td {
  border-color: #efefef;
  padding: 8px 0;
}

.checkout-maquette__review .checkout-review-item {
  display: inline-flex;
  align-items: center;
  gap: 10px;
}

.checkout-maquette__review .checkout-review-item__image {
  width: 48px;
  height: 48px;
  border-radius: 6px;
  object-fit: cover;
  flex-shrink: 0;
}

.checkout-maquette__review .checkout-review-item__title {
  line-height: 1.25;
}

.checkout-maquette__review .order-total th,
.checkout-maquette__review .order-total td {
  padding-top: 14px;
  font-size: 18px;
}

.checkout-maquette__review .woocommerce-shipping-totals {
  display: none;
}

.checkout-maquette__review .woocommerce-checkout-payment {
  margin-top: 14px;
  border: 0;
  background: transparent;
}

.checkout-maquette__review .place-order {
  padding: 8px 0 0;
}

.checkout-maquette__back-to-cart {
  margin-top: 12px;
}

.checkout-maquette__back-to-cart .button {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-height: 42px;
  border: 1px solid #cfcfcf;
  border-radius: 8px;
  background: #fff;
  color: var(--checkout-text);
  text-decoration: none;
  font-size: 14px;
  line-height: 1.2;
  text-align: center;
}

.checkout-maquette__back-to-cart .button:hover {
  border-color: #bdbdbd;
  background: var(--checkout-accent);
  color: var(--checkout-card);
}

.checkout-maquette #payment ul.payment_methods {
  margin: 0;
  border: 0;
  padding: 0;
}

.checkout-maquette #payment ul.payment_methods > li {
  border: 1px solid #b5b5b5;
  border-radius: 6px;
  margin-bottom: 8px;
  padding: 10px;
  background: #fff;
}

.checkout-maquette #payment ul.payment_methods > li img {
  max-height: 18px;
  width: auto;
}

.checkout-maquette #payment ul.payment_methods > li label {
  margin: 0;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 14px;
  text-transform: none;
  letter-spacing: 0;
  color: var(--checkout-text);
}

.checkout-maquette #payment .payment_box {
  margin-top: 8px;
  border-radius: 6px;
  font-size: 13px;
  background: #f8f8f8;
}

.checkout-maquette #payment #place_order,
.checkout-maquette #payment .button.alt {
  width: 100%;
  border: 0;
  border-radius: 8px;
  background: var(--checkout-accent);
  color: #fff;
  font-size: 15px;
  min-height: 44px;
  transition: background 0.2s ease;
}

.checkout-maquette #payment #place_order:hover,
.checkout-maquette #payment .button.alt:hover {
  background: var(--checkout-accent-dark);
}

#checkout-shipping-options-target ul {
  list-style: none;
  margin: 0;
  padding: 0;
}

#checkout-shipping-options-target li {
  display: flex;
  align-items: center;
  gap: 10px;
  border: 1px solid #b7b7b7;
  border-radius: 6px;
  padding: 11px 12px;
  margin-bottom: 8px;
  font-size: 13px;
  background: #fff;
}

#checkout-shipping-options-target label {
  margin: 0;
  text-transform: none;
  font-size: 13px;
  letter-spacing: 0;
  color: var(--checkout-text);
}

#checkout-shipping-options-target .amount {
  font-weight: 600;
  white-space: nowrap;
}

#checkout-shipping-options-target .woocommerce-shipping-destination,
#checkout-shipping-options-target .shipping-calculator-button {
  display: block;
  margin-top: 8px;
  font-size: 12px;
  color: var(--checkout-muted);
}

.checkout-maquette__section #checkout-payment-options-target {
  margin-top: 6px;
}

.checkout-maquette .woocommerce-additional-fields {
  margin-top: 14px;
}

.checkout-maquette .woocommerce-additional-fields textarea {
  min-height: 120px;
  resize: vertical;
}
.checkout-maquette__section-body .woocommerce-checkout-payment{
    background: transparent !important;
}

@media (max-width: 1080px) {
  .checkout-maquette__grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .checkout-maquette__summary {
    position: static;
  }

  .checkout-maquette__section::before {
    left: -14px;
  }
}

@media (max-width: 700px) {
  .checkout.woocommerce-checkout.checkout-maquette {
    padding: 16px 12px 28px;
  }

  .checkout-maquette__title {
    font-size: 28px;
    margin-bottom: 8px;
  }

  .checkout-maquette__section-title {
    font-size: 19px;
  }

  .checkout-maquette .form-row-first,
  .checkout-maquette .form-row-last {
    width: 100%;
    float: none;
  }
}
</style>
<?php

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout checkout-maquette" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php echo esc_attr__( 'Checkout', 'woocommerce' ); ?>">

	<?php if ( $checkout->get_checkout_fields() ) : ?>
		<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

		<div class="checkout-maquette__grid">
			<div class="checkout-maquette__main">
				<h1 class="checkout-maquette__title"><?php esc_html_e( 'Finaliser ma commande', 'woocommerce' ); ?></h1>

				<div class="checkout-maquette__section checkout-maquette__section--shipping" data-section="shipping-address">
					<h3 class="checkout-maquette__section-title"><?php esc_html_e( 'Adresse de livraison', 'woocommerce' ); ?></h3>
					<div class="checkout-maquette__section-body">
						<?php do_action( 'woocommerce_checkout_shipping' ); ?>
					</div>
				</div>

				<div class="checkout-maquette__section checkout-maquette__section--billing" data-section="billing-address">
					<h3 class="checkout-maquette__section-title"><?php esc_html_e( 'Adresse de facturation', 'woocommerce' ); ?></h3>
					<div class="checkout-maquette__section-body">
						<?php do_action( 'woocommerce_checkout_billing' ); ?>
					</div>
				</div>

				<div class="checkout-maquette__section checkout-maquette__section--shipping-methods" data-section="shipping-methods">
					<h3 class="checkout-maquette__section-title"><?php esc_html_e( 'Options de livraison', 'woocommerce' ); ?></h3>
					<div id="checkout-shipping-options-target" class="checkout-maquette__section-body"></div>
				</div>

				<div class="checkout-maquette__section checkout-maquette__section--payment-methods" data-section="payment-methods">
					<h3 class="checkout-maquette__section-title"><?php esc_html_e( 'Options de paiement', 'woocommerce' ); ?></h3>
					<div id="checkout-payment-options-target" class="checkout-maquette__section-body"></div>
				</div>
			</div>

			<aside class="checkout-maquette__summary">
        <div id="checkout-coupon-target" class="checkout-maquette__coupon"></div>
				<?php do_action( 'woocommerce_checkout_before_order_review_heading' ); ?>
				<h3 id="order_review_heading" class="checkout-maquette__summary-title"><?php esc_html_e( 'Resume de la commande', 'woocommerce' ); ?></h3>
				<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

				<div id="order_review" class="woocommerce-checkout-review-order checkout-maquette__review">
					<?php do_action( 'woocommerce_checkout_order_review' ); ?>
				</div>

        <div class="checkout-maquette__back-to-cart">
          <a class="button" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'Retour au panier', 'woocommerce' ); ?></a>
        </div>
			</aside>
		</div>

		<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
	<?php endif; ?>

	<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

<script>
(function () {
  "use strict";

  function setFieldLabels() {
    var labelMap = {
      shipping_first_name: "Prenom",
      shipping_last_name: "Nom",
      shipping_company: "Raison sociale (facultatif)",
      shipping_address_1: "Adresse",
      shipping_address_2: "Ajouter appartement, suite, etc.",
      shipping_postcode: "Code postal",
      shipping_city: "Ville",
      shipping_phone: "Numero de telephone (facultatif)",
      billing_first_name: "Prenom",
      billing_last_name: "Nom",
      billing_company: "Raison sociale (facultatif)",
      billing_address_1: "Adresse",
      billing_address_2: "Ajouter appartement, suite, etc.",
      billing_postcode: "Code postal",
      billing_city: "Ville",
      billing_phone: "Numero de telephone (facultatif)",
      billing_email: "Adresse email"
    };

    Object.keys(labelMap).forEach(function (fieldId) {
      var label = document.querySelector('label[for="' + fieldId + '"]');
      if (label) {
        label.textContent = labelMap[fieldId];
      }
    });
  }

  function forceShippingAddressOpen() {
    var checkbox = document.getElementById("ship-to-different-address-checkbox");
    if (checkbox && !checkbox.checked) {
      checkbox.checked = true;
      checkbox.dispatchEvent(new Event("change", { bubbles: true }));
    }

    var wrap = document.getElementById("ship-to-different-address");
    if (wrap) {
      wrap.style.display = "none";
    }
  }

  function moveShippingMethods() {
    var target = document.getElementById("checkout-shipping-options-target");
    if (!target) {
      return;
    }

    // Always reset the custom container so WooCommerce fragment refreshes do not stack duplicates.
    target.innerHTML = "";

    var shippingRow = document.querySelector("#order_review .woocommerce-shipping-totals");
    if (!shippingRow) {
      return;
    }

    var shippingList = shippingRow.querySelector("ul#shipping_method, ul.woocommerce-shipping-methods");
    if (!shippingList) {
      return;
    }

    if (shippingList.parentElement !== target) {
      target.appendChild(shippingList);
    }

    var destination = shippingRow.querySelector(".woocommerce-shipping-destination");
    if (destination && destination.parentElement !== target) {
      target.appendChild(destination);
    }

    var calculatorButton = shippingRow.querySelector(".shipping-calculator-button");
    if (calculatorButton && calculatorButton.parentElement !== target) {
      target.appendChild(calculatorButton);
    }

    shippingRow.remove();
  }

  function movePaymentMethods() {
    var target = document.getElementById("checkout-payment-options-target");
    if (!target) {
      return;
    }

    // Prefer the fresh fragment inside #order_review, fallback to existing moved block.
    var payment = document.querySelector("#order_review #payment") || target.querySelector("#payment");
    if (!payment) {
      return;
    }

    // Keep only one payment block in the custom area.
    var existing = target.querySelector("#payment");
    if (existing && existing !== payment) {
      existing.remove();
    }

    if (payment.parentElement !== target) {
      target.appendChild(payment);
    }
  }

  function moveCouponIntoSummary() {
    var target = document.getElementById("checkout-coupon-target");
    if (!target) {
      return;
    }

    var couponToggle = document.querySelector(".woocommerce-form-coupon-toggle");
    if (couponToggle && couponToggle.parentElement !== target) {
      target.appendChild(couponToggle);
    }

    var couponForm = document.querySelector("form.checkout_coupon.woocommerce-form-coupon");
    if (couponForm && couponForm.parentElement !== target) {
      target.appendChild(couponForm);
    }
  }

  function updatePlaceOrderButtonText() {
    var placeOrderButton = document.getElementById("place_order");
    if (!placeOrderButton) {
      return;
    }

    if (placeOrderButton.tagName === "INPUT") {
      placeOrderButton.value = "Payer";
      return;
    }

    placeOrderButton.textContent = "Payer";
  }

  function prepareAddressFields() {
    var address2Fields = ["shipping_address_2", "billing_address_2"];

    address2Fields.forEach(function (fieldId) {
      var field = document.getElementById(fieldId);
      if (!field) {
        return;
      }

      field.placeholder = "Ajouter appartement, suite, etc.";
      var wrapper = field.closest(".form-row");
      if (wrapper) {
        wrapper.classList.remove("form-row-wide");
        wrapper.classList.add("form-row-first");
      }
    });
  }

  function renderCheckoutLayout() {
    setFieldLabels();
    forceShippingAddressOpen();
    prepareAddressFields();
    moveShippingMethods();
    movePaymentMethods();
    moveCouponIntoSummary();
    updatePlaceOrderButtonText();
  }

  document.addEventListener("DOMContentLoaded", function () {
    renderCheckoutLayout();

    if (window.jQuery && window.jQuery(document.body).on) {
      window.jQuery(document.body).on("updated_checkout", function () {
        // Let WooCommerce finish replacing fragments before moving nodes.
        window.setTimeout(renderCheckoutLayout, 80);
      });
    }
  });
})();
</script>
