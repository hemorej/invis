<?php
if( isset( $meta ) ) {
    $image = $meta['image'];
    $url = $meta['url'];
} else {
    $image = page( 'projects/portfolio' )->images()->first()->resize( 600 )->url();
    $url = site()->url();
}

$metaDesc = isset($pageDescription) ? $pageDescription : site()->description()->toString();

$title = '';
if( page()->parent() && page()->parent()->title() == 'journal' ) {
    $published = page()->published()->toString();
    $title = !empty( $published ) ? $published : page()->title();
} else if( !empty( page()->title() ) ) {
    $title = page()->title();
} else {
    $title = page()->published()->toString();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?= html( site()->title() ) ?> — <?= html( strtolower( $title ) ) ?></title>
    <meta charset="utf-8"/>
    <meta name="description" content="<?= html( $metaDesc ) ?>"/>
    <meta name="robots" content="index, follow"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0"/>

    <meta itemprop="name" content="<?= html( site()->title() ) ?>">
    <meta itemprop="description" content="<?= html( site()->description() ) ?>">
    <meta itemprop="image" content="<?= html( $image ) ?>">

    <meta property="og:locale" content="en_CA">
    <meta property="og:site_name" content="<?= html( site()->title() ) ?>">

    <link rel="preload" as="font" type="font/woff2" href="<?= url( 'assets/font/Spectral-Regular.woff2' ) ?>" crossorigin>

    <?php if( $slot = $slots->preload() ): ?>
        <?= $slot ?>
    <?php endif ?>

    <?php if( $slot = $slots->meta() ): ?>
        <?= $slot ?>
    <?php else: ?>
        <meta property="og:url" content="<?= html( $url ) ?>">
        <meta property="og:type" content="website">
        <meta property="og:title" content="<?= html( site()->title() ) ?>">
        <meta property="og:description" content="<?= html( $metaDesc ) ?>">
        <meta property="og:image" content="<?= html( $image ) ?>">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@jerome_a_">
        <meta name="twitter:title" content="<?= html( site()->title() ) ?>">
        <meta name="twitter:description" content="<?= html( $metaDesc ) ?>">
        <meta name="twitter:image" content="<?= html( $image ) ?>">
    <?php endif ?>

    <?php if( option( 'env' ) == 'prod' ): ?>
        <?= css( 'assets/dist/app.min.css' ) ?>
    <?php else: ?>
        <?= css( 'assets/css/vendor/tachyons.css' ) ?>
        <?= css( 'assets/css/app.css' ) ?>
    <?php endif ?>

    <link rel="shortcut icon" type="image/x-icon" href="<?= url( 'assets/images/favicon.ico' ) ?>"/>
    <link rel="apple-touch-icon" href="<?= url( 'assets/images/apple-touch-icon.png' ) ?>"/>
    <link rel="manifest" href="/site.webmanifest">
    <meta name="theme-color" content="#fdfdfd">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="The Invisible Cities">
    <link rel="canonical" href="<?= html( $url ) ?>">
</head>
