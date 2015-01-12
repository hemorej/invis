<?php if (!isset($noCopyright)): ?>
    <div class="row large-space-top">
      <footer class="small-12 small-centered medium-10 medium-offset-2 medium-centered columns low-contrast">
        <?php echo kirbytext($site->copyright()) ?>
      </footer>
    </div>
<?php endif; ?>

<body <?php if (isset($error)) echo 'class="error"' ?>>

<?php echo js('assets/js/vendor/jquery.js') ?>
<?php //echo js('assets/js/foundation/foundation.min.js') ?>
<?php //echo js('assets/js/foundation/foundation.interchange.js') ?>
<?php //echo js('assets/js/foundation/foundation.topbar.js') ?>
<?php //echo js('assets/js/vendor/unveil.js') ?>
<?php echo js('assets/js/vendor/min.js') ?>
<?php echo js('assets/js/vendor/kudos.js') ?>
<script>
$( document ).ready(function() {

	$(document).foundation();
    function reflow(){
        $(this).removeAttr('data-original');
        $(document).foundation('interchange', 'reflow');
    }
    $(document).ready(function() {
        $("img.lazy").unveil(500, reflow) ;
    });


    var kudos = $.ajax({url: "?kudos",async: false});
    var parts = window.location.pathname.split('/');
    uid = parts[parts.length-1];

    $(".num").html(kudos.responseText);
    $("figure.kudoable").kudoable();

    if(localStorage.getItem(uid) == 'true') {
        $("figure.kudoable").addClass("complete");
    }
    $("figure.kudo").bind("kudo:added", function(e)
    {
        localStorage.setItem(uid, 'true');
        var kudos = $.ajax({url: "?kudos=plus"});
    });

    $("figure.kudo").bind("kudo:removed", function(e)
    {
        var kudos = $.ajax({url: "?kudos=minus"});
        localStorage.removeItem(uid);
    });
});

</script>
</body>

</html>