<?php if (!isset($noCopyright)): ?>
    <div class="row large-space-top">
      <footer class="small-12 small-centered medium-12 medium-centered columns low-contrast">
        <p><?php echo html::decode($site->copyright()->kirbytext()) ?></p>
      </footer>
    </div>
<?php endif; ?>

<?= js('assets/js/vendor/min.js') ?>
<?php $loc = location() ?>
<?php if($loc !== false): ?>
	<?= css('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.css') ?>
	<?= js('//cdnjs.cloudflare.com/ajax/libs/cookieconsent2/3.1.0/cookieconsent.min.js') ?>
	<?= js('assets/js/vendor/consent.js', ['id' => 'consent', 'data-loc' => $loc, 'data-ga' => c::get('ga_code')]) ?>
<? else: ?>
	<?php snippet('ga') ?>
<? endif ?>
</body>

</html>

<?php
  use \Gilbitron\Util\SimpleCache;
  function location(){
  	
	$cache = new SimpleCache();
	$cache->cache_path = __DIR__ . '/../cache/';
	$cache->cache_time = 86400; //24h

	$remote = filter_var($_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP);
	if($remote == false)
		return 'CA';

	if($data = $cache->get_cache($remote)){
		$loc = json_decode($data);
	}else{
		$access_key = c::get('ipstack_key');

		$data = $cache->do_curl('http://api.ipstack.com/' . $remote . '?access_key=' . $access_key . '&fields=country_code,location.is_eu&language=en&output=json');
		$cache->set_cache($remote, $data);
		$loc = json_decode($data);
	}

	if($loc->location->is_eu == true)
    	return $loc->country_code;

    return false;
  }
?>