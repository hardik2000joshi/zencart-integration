<?php
return
[
'type' => 'plugin',
'name' => 'Custom Payment Gateway',
'description' => 'A custom payment gateway for processing transactions.',
'version' => '1.0.0',
'author' => 'Hardik',
'code' => 'custom_payment_gateway',
'compatibility' => ['1.5.8'],
'install' => [
    'sql' => ['install.sql'],
    // we use 'from' and 'to' to tell Zen Cart where to copy plugin files during installation
    // 'from' is the source path relative to plugin root
    // 'to' is destination path inside zen cart application: C:\xampp\htdocs\zencart\
    'copy' => [
        // Payment module core file
        [
        'from' => 'includes/modules/payment/custompay.php', 
        'to' => 'includes/modules/payment/custompay.php',
    ],
    // Payment module language file
    [
        'from' => 'includes/languages/english/modules/payment/custompay.php',
        'to' => 'includes/languages/english/modules/payment/custompay.php',
    ], 
],
'installer_class' => 'ScriptedInstaller', // Custom installer class for scripted installation
],

// PSR-4 autoload mapping:
'autoload' => [
    'psr-4' => [
        'MyPlugin\\' => 'Installer/'
    ]
    ],
'uninstall' => [
    'sql' => ['uninstall.sql']
]
];