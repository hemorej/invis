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
            'pretty_version' => 'v22.1.0',
            'version' => '22.1.0.0',
            'reference' => '24ffd4c5708bdd490362baedb1df8efe4bf76484',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
