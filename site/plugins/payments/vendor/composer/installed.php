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
            'pretty_version' => 'v20.3.0',
            'version' => '20.3.0.0',
            'reference' => '266f0b05890172184cca66a0688abaf1a96b08d8',
            'type' => 'library',
            'install_path' => __DIR__ . '/../stripe/stripe-php',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
