<?php return array(
    'root' => array(
        'pretty_version' => '1.0.0+no-version-set',
        'version' => '1.0.0.0',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'reference' => NULL,
        'name' => '__root__',
        'dev' => true,
    ),
    'versions' => array(
        '__root__' => array(
            'pretty_version' => '1.0.0+no-version-set',
            'version' => '1.0.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'reference' => NULL,
            'dev_requirement' => false,
        ),
        'flightphp/core' => array(
            'pretty_version' => 'v3.18.0',
            'version' => '3.18.0.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../flightphp/core',
            'aliases' => array(),
            'reference' => '88d7032928c09b98e65ccd87b7f8a341a40cc3d2',
            'dev_requirement' => false,
        ),
        'latte/latte' => array(
            'pretty_version' => 'v3.0.25',
            'version' => '3.0.25.0',
            'type' => 'library',
            'install_path' => __DIR__ . '/../latte/latte',
            'aliases' => array(),
            'reference' => 'bab49c360354f5e7e57c07ec1b1c9b2870a9bda4',
            'dev_requirement' => false,
        ),
        'mikecao/flight' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '2.0.2',
            ),
        ),
    ),
);
