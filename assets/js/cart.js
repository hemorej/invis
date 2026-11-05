document.addEventListener('alpine:init', function () {
    var genericError = 'Something went wrong, please try again'

    // POST a JSON body. Resolves with the response on a 2xx status; rejects with
    // an Error carrying the server's {message} (or a generic one) otherwise.
    function postJson(url, body) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(body)
        }).then(response => {
            if (response.ok) return response
            return response.json().catch(() => ({})).then(data => { throw new Error(data.message || genericError) })
        }, () => { throw new Error(genericError) })
    }

    // Kept outside the reactive data so Alpine does not proxy the Stripe client.
    var stripe = null

    Alpine.data('cart', () => ({
        inCart: true,
        inShipping: false,
        inCheckout: false,
        total: 0,
        currencies: null,
        name: null,
        email: null,
        line1: null,
        line2: null,
        city: null,
        province: null,
        postcode: null,
        country: null,
        showTerms: false,
        error: false,
        leftInStock: 1,
        step: '1. cart',
        orderWaiting: false,
        shipping: 0,
        items: null,

        init() {
            // refs are only registered once the children have been initialised
            this.$nextTick(() => {
                this.country = this.$refs.userLocation.value
                this.total = parseInt(this.$refs.checkoutTotal.value)
                this.currencies = this.$refs.currencies.value
            })
        },
        get shippingIncomplete() {
            return this.isEmpty(this.name) || this.isEmpty(this.email) || this.isEmpty(this.line1) || this.isEmpty(this.city) || this.isEmpty(this.province) || this.isEmpty(this.postcode) || this.isEmpty(this.country) || !this.validEmail(this.email)
        },
        resetToCart: function(){
            this.inCart = true;
            this.inShipping = false;
            this.inCheckout = false;
            this.step = '1. cart';
        },
        showShipping: function(){
            this.inCart = false;
            this.inShipping = true;
            this.inCheckout = false;
            this.step = '1. cart   2. shipping address';
        },
        showCheckout: function(){
            this.inCart = false;
            this.inCheckout = true;
            this.step = '1. cart   2. shipping address   3. payment';

            // init stripe button
            stripe = Stripe(this.$refs.checkoutKey.value);
            this.orderWaiting = true
            postJson('/address', {
                name: this.name,
                email: this.email,
                line1: this.line1,
                line2: this.line2,
                city: this.city,
                province: this.province,
                postcode: this.postcode,
                country: this.country,
                csrf: this.$refs.checkoutCSRF.value
              })
              .then(response => response.json())
              .then(data => {
                this.orderWaiting = false
                this.inShipping = false
                this.inCheckout = true
                this.inCart = true

                this.$refs.checkoutSessionID.value = data.checkoutSessionId
                this.currencies = data.currencies
                this.shipping = data.shipping
                this.items = data.items
                this.total += this.shipping
              })
              .catch(error => {
                this.orderWaiting = false
                alert(error.message)
              })
        },
        redirectStripe: function(){
            stripe.redirectToCheckout({
                sessionId: this.$refs.checkoutSessionID.value
            }).then(function (result) {
                alert(result.error.message);
            });
        },
        updateCart: function(event){
            var id = event.target.id
            var variant = event.target.getAttribute('data-variant')
            var qty = event.target.value
            var max = event.target.getAttribute('max')

            if(parseInt(qty) >= parseInt(max)){
                event.target.value = max
                this.leftInStock = max
                this.error = true
                return false;
            }else{
                postJson('/prints/cart', {
                    id: id,
                    action: "add",
                    quantity: qty,
                    csrf: this.$refs.inputCsrf.value
                  })
                  .then(function (response) {
                    location.reload(true);
                  })
            }
        },
        incQty: function(event){
            var row = event.target.closest('[data-qty-row]');
            var input = row.querySelector('input[type=number]');
            var max = parseInt(input.getAttribute('max'));
            if(parseInt(input.value) < max){
                input.value = parseInt(input.value) + 1;
                input.dispatchEvent(new Event('change'));
            }
        },
        decQty: function(event){
            var row = event.target.closest('[data-qty-row]');
            var input = row.querySelector('input[type=number]');
            if(parseInt(input.value) > 1){
                input.value = parseInt(input.value) - 1;
                input.dispatchEvent(new Event('change'));
            }
        },
        validEmail: function (email) {
          var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          return re.test(email);
        },
        isEmpty: function(str) {
            return (!str || /^\s*$/.test(str));
        }
    }))
})
