function addClass(e,t){e.classList?e.classList.add(t):hasClass(e,t)||(e.className+=" "+t)}function removeClass(e,t){if(e.classList)e.classList.remove(t);else if(hasClass(e,t)){var n=new RegExp("(\\s|^)"+t+"(\\s|$)");e.className=e.className.replace(n," ")}}function getCsrfToken(){return document.head.querySelector("[name=csrf-token]").content}var stripe=Stripe("<?= $publishableKey; ?>"),elements=stripe.elements(),style={base:{color:"#3e4e59",lineHeight:"18px",fontFamily:'"Helvetica Neue", Helvetica, sans-serif',fontSmoothing:"antialiased",fontSize:"16px","::placeholder":{color:"#aab7c4"}},invalid:{color:"#fa755a",iconColor:"#fa755a"}},submit=document.getElementById("payment-button-submit"),card=elements.create("card",{style:style});card.mount("#payment-stripe"),card.addEventListener("change",function(e){var t=document.getElementById("payment-errors");e.error?t.textContent=e.error.message:t.textContent="",e.complete?submit.disabled=!1:submit.disabled=!0});var form=document.getElementById("payment-form");function slaPaymentMethodHandler(e){fetch(confirmUrl,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-Token":getCsrfToken()},body:JSON.stringify({payment_method_id:e.paymentMethod.id})}).then(function(e){e.json().then(function(e){handleServerResponse(e)})})}function handleServerResponse(e){e.error?handleError(e):e.requires_action?handleAction(e):submitFormAndSuccess(e)}function handleAction(e){stripe.handleCardAction(e.payment_intent_client_secret).then(function(e){e.error?handleError(e):fetch(confirmUrl,{method:"POST",headers:{"Content-Type":"application/json","X-CSRF-Token":getCsrfToken()},body:JSON.stringify({payment_intent_id:e.paymentIntent.id})}).then(function(e){return e.json()}).then(handleServerResponse)})}function handleError(e){document.getElementById("payment-errors").textContent=e.error.message,removeClass(submit,"submit-is-loading"),submit.disabled=!0}function submitFormAndSuccess(e){var t=document.getElementById("payment-form"),n=document.createElement("input");n.setAttribute("type","hidden"),n.setAttribute("name","intentId"),n.setAttribute("value",e.id),t.appendChild(n),t.submit()}form.addEventListener("submit",function(e){e.preventDefault(),addClass(submit,"submit-is-loading"),submit.disabled=!0,stripe.createPaymentMethod("card",card).then(function(e){e.error?handleError(e):slaPaymentMethodHandler(e)})});