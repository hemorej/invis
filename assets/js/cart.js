$( document ).ready(function(){

    var prevQty = 0;

   $( ".variant" ).click(function(e) {
        e.preventDefault();
        $(this).addClass('active').siblings().removeClass('active');
        
        var variant = $(this).children('a').attr('data-option-variant');
        var price = $(this).children('a').attr('data-option-price');
        $("input[name=variant]").val(variant);
        $("input[name=price]").val(price);
   });

   $( ".input-qty" ).focus(function() {
        prevQty = $(this).val();
   });

   $( ".input-qty" ).change(function() {
        var id = $(this).attr("id");
        var variant = $(this).attr("data-variant");
        var qty = $(this).val();
        var max = $(this).attr("max");
        var csrf = $("#input-csrf").val();

        if(parseInt(qty) > parseInt(max)){
            $('[data-variant="'+variant+'"]').val(prevQty);
            $('#stock-error').html(parseInt(max));
            $('.alert-box').show();
            return false;
        }else{
            $.post( "cart", { id: id, action: "add", quantity: qty, csrf: csrf})
            .done(function( data ) {
                location.reload(true);
            });
        }
    });

   $(".close").click(function(e){
        e.preventDefault();
        $('.alert-box').hide();
    });

   $("#add-cart").click(function(e){
        e.preventDefault();
        $(this).html('adding...');
        $(this).attr('disabled','disabled');
        sessionStorage.setItem('cart', 'true');

        var uri = $("[name=uri]").val();
        var variant = $("[name=variant]").val();
        var csrf = $("[name=csrf]").val();

        $.post( "cart", { uri: uri, variant: variant, action: "add", csrf: csrf})
        .done(function( data ) {
            document.location.replace('cart');
        });
   });

    $("#terms").click(function(e){
        e.preventDefault();
        $("#term-details").toggle();
    });
});

var app = new Vue({
    el: '#cart',
    data: {
        inCart: true,
        inShipping: false,
        inCheckout: false,
        stripe: null,
        name: null,
        email: null,
        line1: null,
        line2: null,
        city: null,
        province: null,
        postcode: null,
        country: null
    },
    mounted() {
        this.country = this.$refs.userLocation.value
    },
    computed: {
        shippingIncomplete: function(){
            return this.isEmpty(this.name) || this.isEmpty(this.email) || this.isEmpty(this.line1) || this.isEmpty(this.city) || this.isEmpty(this.province) || this.isEmpty(this.postcode) || this.isEmpty(this.country) || !this.validEmail(this.email)
        }
    },
    methods: {
        showShipping: function(){
            this.inCart = false;
            this.inShipping = true;
            this.inCheckout = false;
        },
        showCheckout: function(){
            this.inCart = false;
            this.inCheckout = true;
            this.inShipping = false;

            // init stripe button
            this.stripe = Stripe($("#checkout-key").val());
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
              .then(function (response) {})
        },
        initPaypal: function(){
            if(document.getElementById('paypal-button-container') === null ){
                return;
            }

            paypal.Buttons({
                env: $("#pp-env").val(),
                client: {
                    sandbox:    $("#checkout-pp-key").val(),
                    production: $("#checkout-pp-key").val()
                },
                commit: true,
                style: {
                    layout: 'horizontal',
                    color: 'silver',
                    size: 'responsive',
                    shape: 'rect',
                    tagline: 'false'
                },
                createOrder: function(data, actions) {
                    return actions.order.create({
                        purchase_units: [{
                          amount: {
                            value: parseInt($("#checkout-total").val())
                          }
                        }]
                    });
                },
                onApprove: function(data, actions) {
                    return actions.order.capture().then(function(details) {

                    var csrf = $("#pp-csrf").val();
                    var token = data.orderID;

                    axios.post( "/order/success/paypal", { token: token, csrf: csrf } )
                      .then(function( data ) {
                          document.location.replace('order');
                      });
                    });
                }
            }).render('#paypal-button-container');
        },
        redirectStripe: function(){
            this.stripe.redirectToCheckout({
                sessionId: $("#checkout-session-id").val()
            }).then(function (result) {
                alert(result.error.message);
            });
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