/* ****** HELPERS ********* */

function addClass(el, className) {
    if (el.classList)
        el.classList.add(className)
    else if (!hasClass(el, className))
        el.className += " " + className;
}

function removeClass(el, className) {
    if (el.classList)
        el.classList.remove(className)
    else if (hasClass(el, className)) {
        var reg = new RegExp('(\\s|^)' + className + '(\\s|$)');
        el.className = el.className.replace(reg, ' ');
    }
}

/* ****** STRIPE ********* */

// Create a Stripe client.
var stripe = Stripe('<?= $publishableKey; ?>');

// Create an instance of Elements.
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
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
};

// element vars
var submit = document.getElementById('payment-button-submit');

// Create an instance of the card Element.
var card = elements.create('card', {
    style: style
});

// Add an instance of the card Element into the `card-element` <div>.
card.mount('#payment-stripe');

// Handle real-time validation errors from the card Element.
card.addEventListener('change', function (event) {
    var displayError = document.getElementById('payment-errors');
    if (event.error) {
        displayError.textContent = event.error.message;
    } else {
        displayError.textContent = '';
    }

    if(event.complete) {
        submit.disabled = false;
    } else {
        submit.disabled = true;
    }
});

// Handle form submission.
var form = document.getElementById('payment-form');
form.addEventListener('submit', function (event) {
    event.preventDefault();

    addClass(submit, 'submit-is-loading');
    submit.disabled = true;

    stripe.createPaymentMethod('card', card).then(function(result) {
        if (result.error) {
            // Inform the user if there was an error.
            var errorElement = document.getElementById('payment-errors');
            errorElement.textContent = result.error.message;
            removeClass(submit, 'submit-is-loading');
            submit.disabled = true;
        } else {
            slaPaymentMethodHandler(result);
        }
    });
});

function getCsrfToken() {
    return document.head.querySelector("[name=csrf-token]").content;
}

function slaPaymentMethodHandler(result) {

    

    // Send paymentMethod.id to server
    fetch(confirmUrl, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': getCsrfToken()
      },
      body: JSON.stringify({
        payment_method_id: result.paymentMethod.id
      })
    }).then(function(result) {
      // Handle server response (see Step 3)
      result.json().then(function(json) {
        handleServerResponse(json);
      })
    });
}

function handleServerResponse(response) {
    if (response.error) {
      // Show error from server on payment form
      handleError(response);
    } else if (response.requires_action) {
      // Use Stripe.js to handle required card action
      handleAction(response);
    } else {
      // Show success message
      submitFormAndSuccess(response);
    }
}
  
 function handleAction(response) {
    stripe.handleCardAction(
      response.payment_intent_client_secret
    ).then(function(result) {
      if (result.error) {
        // Show error in payment form
        handleError(result);
      } else {
        // The card action has been handled
        // The PaymentIntent can be confirmed again on the server
        fetch(confirmUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': getCsrfToken()
          },
          body: JSON.stringify({
            payment_intent_id: result.paymentIntent.id
          })
        }).then(function(confirmResult) {
          return confirmResult.json();
        }).then(handleServerResponse);
      }
    });
}

function handleError(result)
{
    // Inform the user if there was an error.
    var errorElement = document.getElementById('payment-errors');
    errorElement.textContent = result.error.message;
    removeClass(submit, 'submit-is-loading');
    submit.disabled = true;
}

function submitFormAndSuccess(token)
{
    var form = document.getElementById('payment-form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'intentId');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);
    form.submit();
}