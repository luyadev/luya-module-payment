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

// Create an instance of the card Element.
var card = elements.create('card', {style: style});

// Add an instance of the card Element into the `card-element` <div>.
card.mount('#payment-stripe');

// Handle real-time validation errors from the card Element.
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('payment-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

// Handle form submission.
var form = document.getElementById('payment-form');
form.addEventListener('submit', function(event) {
  event.preventDefault();

  stripe.createSource(card).then(function(result) {
    if (result.error) {
      // Inform the user if there was an error.
      var errorElement = document.getElementById('payment-errors');
      errorElement.textContent = result.error.message;
    } else {
      stripeSourceHandler(result);
    }
  });
});

function stripeSourceHandler(token) {
    var source = token.source;
    var threeDSecure = false;
    if (source.card.three_d_secure == 'required') {
        var threeDSecure = true;
    }

    var form = document.getElementById('payment-form');

    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'sourceToken');
    hiddenInput.setAttribute('value', token.source.id);

    form.appendChild(hiddenInput);
    if (threeDSecure) {
        var hiddenThreeD = document.createElement('input');
        hiddenThreeD.setAttribute('type', 'hidden');
        hiddenThreeD.setAttribute('name', 'threeDSecure');
        hiddenThreeD.setAttribute('value', 1);
        form.appendChild(hiddenThreeD);
    }

    // Submit the form
    form.submit();
}
