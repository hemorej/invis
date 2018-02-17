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

   // checkout
   var handler = StripeCheckout.configure({
      key: $("#checkout-key").val(),
      image: '../assets/images/logo.png',
      locale: 'auto',
      token: function(token, args) {
        $('.loading').show();
        var csrf = $("#input-csrf").val();
        var items = [];
        $(".input-qty:visible").each(function(i, obj) {
          var item = {"id": obj.id, "sku": obj.getAttribute('data-sku'), "quantity": obj.value, "price": obj.getAttribute('data-amount'), "name": obj.getAttribute('data-name'), "variant": obj.getAttribute('data-variant')};
          items.push(item);
        });
        var total = parseInt($("#checkout-total").val());

        $.post( "order", { token: JSON.stringify(token), args: JSON.stringify(args), csrf: csrf, items: JSON.stringify(items), total: total } )
        .done(function( data ) {
            document.location.replace('order');
        });
      }
    });

    document.getElementById('checkoutButton').addEventListener('click', function(e) {
      // Open Checkout with further options:
      handler.open({
        name: 'the Invisible Cities',
        description: $("#checkout-content").val(),
        zipCode: true,
        currency: 'CAD',
        shippingAddress: true,
        billingAddress: true,
        amount: parseInt($("#checkout-total").val()),
      });
      e.preventDefault();
    });

    // Close Checkout on page navigation:
    window.addEventListener('popstate', function() {
      handler.close();
    });

    $("#terms").click(function(e){
        e.preventDefault();
        $("#term-details").toggle();
    });

    paypal.Button.render({
        env: 'sandbox',
        client: {
            sandbox:    'ASO_SHn9QO_q19onBL4mniATSkbBCcj6IQGYCLeOaBytBPHoUmjyWAFjq6osEy41PagHqW3pBmvE2q2e',
            production: $("#checkout-pp-key").val()
        },
        commit: true,
        style: {
            color: 'silver',
            size: 'small',
            shape: 'rect',
            tagline: 'false'
        },
        payment: function(data, actions) {
            return actions.payment.create({
                payment: {
                    transactions: [
                        {
                            amount: { total: parseInt($("#checkout-total").val())/100, currency: 'CAD' }
                        }
                    ]
                }
            });
        },
        onAuthorize: function(data, actions) {
          return actions.payment.get().then(function(data) {
            
            var shipping = data.payer.payer_info.shipping_address;
            console.log(shipping);

            document.querySelector('#recipient').innerText = shipping.recipient_name;
            document.querySelector('#line1').innerText     = shipping.line1;
            document.querySelector('#city').innerText      = shipping.city;
            document.querySelector('#state').innerText     = shipping.state;
            document.querySelector('#zip').innerText       = shipping.postal_code;
            document.querySelector('#country').innerText   = shipping.country_code;

            document.querySelector('#paypal-button-container').style.display = 'none';
            document.querySelector('#confirm').style.display = 'block';

            // Listen for click on confirm button

            document.querySelector('#confirmButton').addEventListener('click', function() {

                // Disable the button and show a loading message

                document.querySelector('#confirm').innerText = 'Loading...';
                document.querySelector('#confirm').disabled = true;

                // Execute the payment

                return actions.payment.execute().then(function() {

                    // Show a thank-you note

                    document.querySelector('#thanksname').innerText = shipping.recipient_name;

                    document.querySelector('#confirm').style.display = 'none';
                    document.querySelector('#thanks').style.display = 'block';
                });
            });
          });
        }
    }, '#paypal-button-container');
});

