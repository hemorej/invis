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
            // out of stock
            return false;
        }else{
            $.post( "cart", { id: id, action: "add", quantity: qty, csrf: csrf})
            .done(function( data ) {
                location.reload(true);
            });
        }
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
});