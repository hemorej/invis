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
});