<?php

$options = [
    'title'       => 'Journal feed',
    'description' => 'Latest articles from the journal',
    'url' => site()->url(),
    'feedurl' => site()->url() . '/feed/',
    'link' => site()->url(),
    'urlfield' => 'url',
    'titlefield' => 'title',
    'datefield' => 'Published',
    'textfield' => 'images', 
    'modified' => time(),
    'snippet' => 'feed/rss', // 'feed/json'
    'mime' => 'text/xml',
    'expires' => 2880, //48 hours
    'sort' => true,
];

echo page('journal')->children()->listed()->flip()->limit(10)->feed($options);