<?php return array(
    'root' => array(
        'name' => 'payments/payments',
        'pretty_version' => '1.0.0',
        'version' => '1.0.0.0',
        'reference' => null,
        'type' => 'kirby-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'payments/payments' => array(
            'pretty_version' => '1.0.0',
            'version' => '1.0.0.0',
            'reference' => null,
            'type' => 'kirby-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'stripe/stripe-php' => array(
            'pretty_version' => 'v10.21.0',
            'version' => '10.21.0.0',
            'reference' => 'b4ab319731958077227fad1874a3671458c5d593',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
