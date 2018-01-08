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

   $( ".input-qty.right" ).focus(function() {
        prevQty = $(this).val();
   });

   $( ".input-qty.right" ).change(function() {
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
      token: function(token) {
        var csrf = $("#input-csrf").val();
        $.post( "order", { token: JSON.stringify(token), csrf: csrf})
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
        amount: parseInt($("#checkout-total").val()),
      });
      e.preventDefault();
    });

    // Close Checkout on page navigation:
    window.addEventListener('popstate', function() {
      handler.close();
    });
});