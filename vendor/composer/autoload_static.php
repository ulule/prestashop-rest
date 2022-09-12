<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf85a9d84fe548bee3e307a81a2cf1760
{
    public static $prefixesPsr0 = array (
        'H' => 
        array (
            'Httpful' => 
            array (
                0 => __DIR__ . '/..' . '/nategood/httpful/src',
            ),
        ),
    );

    public static $classMap = array (
        'WebhookCustomer' => __DIR__ . '/../..' . '/src/decorators/WebhookCustomer.php',
        'WebhookCustomerMessage' => __DIR__ . '/../..' . '/src/decorators/WebhookCustomerMessage.php',
        'WebhookDecorator' => __DIR__ . '/../..' . '/src/decorators/WebhookDecorator.php',
        'WebhookLogModel' => __DIR__ . '/../..' . '/src/classes/WebhookLogModel.php',
        'WebhookModel' => __DIR__ . '/../..' . '/src/classes/WebhookModel.php',
        'WebhookOrder' => __DIR__ . '/../..' . '/src/decorators/WebhookOrder.php',
        'WebhookProduct' => __DIR__ . '/../..' . '/src/decorators/WebhookProduct.php',
        'WebhookQueueModel' => __DIR__ . '/../..' . '/src/classes/WebhookQueueModel.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixesPsr0 = ComposerStaticInitf85a9d84fe548bee3e307a81a2cf1760::$prefixesPsr0;
            $loader->classMap = ComposerStaticInitf85a9d84fe548bee3e307a81a2cf1760::$classMap;

        }, null, ClassLoader::class);
    }
}