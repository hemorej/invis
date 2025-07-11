<!DOCTYPE html>
<html lang="en">
<head>

  @php
    $image = page('projects/portfolio')->images()->first()->resize(600)->url();
    $url = $site->url();
    if(isset($meta)){
      $image = $meta['image'];
      $url = $meta['url'];
    }

    if(!empty($page->title())){
      if($page->title() == 'journal'){
        // journal is the title and uid, the only exception to the below case
        $title = $page->title();
      }elseif($page->title() != $page->uid()){
        $title = $page->title();
      }elseif($site->page()->title() == 'cart'){
        $title = $site->page()->title();
      }
    }else{
      $title = $page->published()->toString();
    }
  @endphp

<title>{{ $site->title() }} - {{ strtolower($title) }}</title>
  <meta charset="utf-8" />
  <meta name="description" content="{{ $site->description() }}" />
  <meta name="keywords" content="{{ $site->keywords() }}" />
  <meta name="robots" content="index, follow" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />

  <meta itemprop="name" content="{{ $site->title() }}">
  <meta itemprop="description" content="{{ $site->description() }}">
  <meta itemprop="image" content="{{ $image }}">

  <meta property="og:url" content="{{ $url }}">
  <meta property="og:type" content="website">
  <meta property="og:title" content="{{ $site->title() }}">
  <meta property="og:description" content="{{ $site->description() }}">
  <meta property="og:image" content="{{ $image }}">
  <meta property="og:locale" content="en_CA">

  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="{{ $site->title() }}">
  <meta name="twitter:description" content="{{ $site->description() }}">
  <meta name="twitter:image" content="{{ $image }}">
  <meta name="twitter:image:alt" content="{{ $site->description() }}">

  @if(option('env') == 'prod')
    @css('assets/dist/app.min.css')
  @else
    @css('assets/css/app.css')
    @css('assets/css/vendor/tachyons.css')
  @endif
   
  <link rel="shortcut icon" type="image/x-icon"  href="@url('assets/images/favicon.ico')" />
  <link rel="apple-touch-icon" sizes="72x72" href="@url('assets/images/apple-touch-icon-72x72.png')" />
  <link rel="apple-touch-icon" sizes="114x114" href="@url('assets/images/apple-touch-icon-114x114.png')" />
</head>
