<!DOCTYPE html>
<html lang="en">
<head>

  <?php
  $title = $page->published()->toString();
  if(!empty($page->title()) && $page->title() != $page->uid())
    $title = $page->title();
  ?>

  <title><?php echo html($site->title()) . ' - ' . html(strtolower($title)) ?></title>
  <meta charset="utf-8" />
  <meta name="description" content="<?php echo html($site->description()) ?>" />
  <meta name="keywords" content="<?php echo html($site->keywords()) ?>" />
  <meta name="robots" content="index, follow" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <?php echo css('assets/styles/app.css') ?>
  
  <link rel="shortcut icon" type="image/x-icon"  href="<?php echo url('assets/images/favicon.ico') ?>">
  <link rel="apple-touch-icon" sizes="72x72" href="<?php echo url('assets/images/apple-touch-icon-72x72.png') ?>" />
  <link rel="apple-touch-icon" sizes="114x114" href="<?php echo url('assets/images/apple-touch-icon-114x114.png') ?>" />

  <link rel="alternate" type="application/rss+xml" href="<?php echo url('feed') ?>" title="Feed | <?php echo html($site->title()) ?>" />

  <? if(isset($extraHeaders)){
    foreach($extraHeaders as $header){
      echo $header."\n";
    }
  }
  ?>
</head>

<body>
  <div class="row large-space-top">
	  <header class="small-12 small-centered medium-10 medium-offset-2 columns">
	    <h1><a href="<?php echo url() ?>"> <?php echo html($site->title()) ?> </a></h1>
	  </header>
  </div>