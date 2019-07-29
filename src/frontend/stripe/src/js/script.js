/* ****** HELPERS ********* */

var helper = {
  addClass : function(el, className) {
    if (el.classList) {
      el.classList.add(className)
    } else if (!hasClass(el, className)) {
        el.className += " " + className;
    }
  },
  removeClass: function (el, className) {
    if (el.classList) {
      el.classList.remove(className)
    } else if (hasClass(el, className)) {
        var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
        el.className = el.className.replace(reg, ' ');
    }
  },
  csrfToken : function() {
    return document.head.querySelector("[name=csrf-token]").content;
  }
};

/* ****** Payment ********* */

var payment = {
  createPaymentMethodHandler : function(result) {
    fetch(confirmUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': helper.csrfToken()
      },
      body: JSON.stringify({
        payment_method_id: result.paymentMethod.id
      })
    }).then(function(result) {
      result.json().then(function(json) {
        payment.serverResponseHandler(json);
      })
    });
  },
  serverResponseHandler : function(response) {
    if (response.error) {
      // Show error from server on payment form
      payment.invokeError(response);
    } else if (response.requires_action) {
      // Use Stripe.js to handle required card action
      payment.takeActionHandler(response);
    } else {
      // Show success message
      payment.invokeSuccess(response);
    }
  },
  takeActionHandler : function(response) {
    stripe.handleCardAction(
      response.payment_intent_client_secret
    ).then(function(result) {
      if (result.error) {
        // Show error in payment form
        payment.invokeError(result);
      } else {
        // The card action has been handled
        // The PaymentIntent can be confirmed again on the server
        fetch(confirmUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': helper.csrfToken()
          },
          body: JSON.stringify({
            payment_intent_id: result.paymentIntent.id
          })
        }).then(function(confirmResult) {
          return confirmResult.json();
        }).then(payment.serverResponseHandler);
      }
    });
  },
  invokeError : function(result) {
    // Inform the user if there was an error.
    var errorElement = document.getElementById('payment-errors');
    errorElement.textContent = result.error.message;
    helper.removeClass(submit, 'submit-is-loading');
    submit.disabled = false;
  },
  invokeSuccess : function(token) {
    var form = document.getElementById('payment-form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'intentId');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);
    form.submit();
  }
};

/* ****** STRIPE ********* */

var stripe = Stripe('<?= $publishableKey; ?>');
var elements = stripe.elements();
var submit = document.getElementById('payment-button-submit');
var form = document.getElementById('payment-form');
var card = elements.create('card', {
  style: {
    base: {
      color: '#3e4e59',
      lineHeight: '18px',
      fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
      fontSmoothing: 'antialiased',
      fontSize: '16px',
      '::placeholder': {
          color: '#aab7c4'
      }
    },
    invalid: {
      color: '#fa755a',
      iconColor: '#fa755a'
    }
  }
});

// mount card elements
card.mount('#payment-stripe');
card.addEventListener('change', function (event) {
  var displayError = document.getElementById('payment-errors');
  if (event.error) {
      displayError.textContent = event.error.message;
  } else {
      displayError.textContent = '';
  }
  if (event.complete) {
      submit.disabled = false;
  } else {
      submit.disabled = true;
  }
});

// add form event listener
form.addEventListener('submit', function (event) {
  event.preventDefault();
  helper.addClass(submit, 'submit-is-loading');
  submit.disabled = true;
  stripe.createPaymentMethod('card', card).then(function(result) {
    if (result.error) {
      payment.invokeError(result);
    } else {
      payment.createPaymentMethodHandler(result);
    }
  });
});