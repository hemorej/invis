var app = new Vue({
    el: '#cart',
    data: {
        inCart: true,
        inShipping: false,
        inCheckout: false,
        discount: null,
        total: 0,
        currencies: null,
        disableDiscount: false,
        stripe: null,
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
        items: null
    },
    mounted() {
        this.country = this.$refs.userLocation.value
        this.total = parseInt(this.$refs.total.value)
        this.currencies = this.$refs.currencies.value
    },
    computed: {
        shippingIncomplete: function(){
            return this.isEmpty(this.name) || this.isEmpty(this.email) || this.isEmpty(this.line1) || this.isEmpty(this.city) || this.isEmpty(this.province) || this.isEmpty(this.postcode) || this.isEmpty(this.country) || !this.validEmail(this.email)
        }
    },
    methods: {
        applyDiscount: function(){
            this.orderWaiting = true
            axios.post('/discount', {
                discount: this.discount,
                csrf: this.$refs.discountCSRF.value
              }).then(response => {
                if(parseInt(response.data.total) != 0){
                    this.total = response.data.total
                    this.currencies = response.data.currencies
                    this.discount = response.data.discountAmount
                    this.disableDiscount = true
                    this.$refs.checkoutSessionID.value = response.data.checkoutSessionId
                    this.$refs.checkoutTotal.value = response.data.total
                }
                this.orderWaiting = false
              })
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
            this.stripe = Stripe(this.$refs.checkoutKey.value);
            this.orderWaiting = true
            axios.post('/address', {
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
              .then(response => {
                this.orderWaiting = false
                this.inShipping = false
                this.inCheckout = true
                this.inCart = true

                this.$refs.checkoutSessionID.value = response.data.checkoutSessionId
                this.currencies = response.data.currencies
                this.shipping = response.data.shipping
                this.discount = response.data.discount
                this.items = response.data.items
                this.total += this.shipping
              })
        },
        redirectStripe: function(){
            this.stripe.redirectToCheckout({
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
                axios.post('/prints/cart', {
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
        validEmail: function (email) {
          var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
          return re.test(email);
        },
        isEmpty: function(str) {
            return (!str || /^\s*$/.test(str));
        }
    }
});