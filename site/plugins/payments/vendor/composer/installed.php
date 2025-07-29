<?php return array(
    'root' => array(
        'name' => 'payments/payments',
        'pretty_version' => '1.0.0',
        'version' => '1.0.0.0',
        'reference' => NULL,
        'type' => 'kirby-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'payments/payments' => array(
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'reference' => NULL,
            'type' => 'kirby-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'stripe/stripe-php' => array(
            'pretty_version' => 'v17.4.0',
            'version' => '17.4.0.0',
            'reference' => '893946057e43b145826b0dfd7f398673e381e2ae',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
