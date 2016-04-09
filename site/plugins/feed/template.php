<!-- generator="<?php echo $generator ?>" -->
<rss version="2.0" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom">

  <channel>
    <title>The Invisible Cities</title>
    <link><?php echo xml($link) ?></link>
    <generator><?php echo c::get('feed.generator', 'Kirby') ?></generator>
    <lastBuildDate><?php echo date('r', $modified) ?></lastBuildDate>
    <atom:link href="<?php echo xml($url) ?>/feed" rel="self" type="application/rss+xml" />

    <?php if(!empty($description)): ?>
    <description><?php echo xml($description) ?></description>
    <?php endif ?>

    <?php foreach($items as $item): ?>
    <item>
      <title><?php echo xml($item->published()) ?></title>
      <link><?php echo xml($item->url()) ?></link>
      <guid><?php echo xml($item->url()) ?></guid>
<?php $d = new DateTime($item->published()) ; ?>
      <pubDate><?php echo $d->format("D, d M Y H:i:s O") ?></pubDate>

<?php
        $image = $item->images()->first() ;
        $destination = $image->dir() . '/thumbs' ;
        $url = $page->url() . '/thumbs' ;
        dir::make($destination);
        $options = array('root' => $destination, 'url' => $item->url() . '/thumbs');
?>
       <description>&lt;img src="<?php echo thumb($image, a::merge(array('width' => 800), $options))->url() ?>"&gt;</description>
    </item>
    <?php endforeach ?>

  </channel>
</rss>