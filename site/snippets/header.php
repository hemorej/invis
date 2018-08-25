<!DOCTYPE html>
<html lang="en">
<head>

  <?php
  $image = thumb(page('projects/portfolio')->files()->first(), array('height' => 600))->url();
  $url = $site->url();
  if(isset($meta)){
    $image = $meta['image'];
    $url = $meta['url'];
  }
  $title = $page->published()->toString();
  if(!empty($page->title()) && $page->title() != $page->uid())
    $title = $page->title();
  ?>

  <title><?= html($site->title()) . ' - ' . html(strtolower($title)) ?></title>
  <meta charset="utf-8" />
  <meta name="description" content="<?= html($site->description()) ?>" />
  <meta name="keywords" content="<?= html($site->keywords()) ?>" />
  <meta name="robots" content="index, follow" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <meta itemprop="name" content="<?= $site->title() ?>">
  <meta itemprop="description" content="<?= html($site->description()) ?>">
  <meta itemprop="image" content="<?= $image ?>">

  <meta property="og:url" content="<?= $url ?>">
  <meta property="og:type" content="website">
  <meta property="og:title" content="<?= $site->title() ?>">
  <meta property="og:description" content="<?= html($site->description()) ?>">
  <meta property="og:image" content="<?= $image ?>">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= $site->title() ?>">
  <meta name="twitter:description" content="<?= html($site->description()) ?>">
  <meta name="twitter:image" content="<?= $image ?>">

  <?php if(c::get('env') == 'prod'): ?>
    <?= css('assets/css/app.min.css') ?>
  <?php else: ?>
    <?= css('assets/css/app.css') ?>
  <? endif ?>
   
  <link rel="shortcut icon" type="image/x-icon"  href="<?= url('assets/images/favicon.ico') ?>" />
  <link rel="apple-touch-icon" sizes="72x72" href="<?= url('assets/images/apple-touch-icon-72x72.png') ?>" />
  <link rel="apple-touch-icon" sizes="114x114" href="<?= url('assets/images/apple-touch-icon-114x114.png') ?>" />

  <link rel="alternate" type="application/rss+xml" href="<?= url('feed') ?>" title="Feed | <?= html($site->title()) ?>" />
</head>