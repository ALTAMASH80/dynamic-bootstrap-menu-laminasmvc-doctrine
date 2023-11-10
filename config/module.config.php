<?php 
declare(strict_types=1);

namespace LRPHPT\MenuTree;

return [
    'doctrine' => [
        'driver' => [
            __NAMESPACE__.'_driver' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [ __DIR__ . '/../src/Entity', ]
            ],
            'orm_default' => [
                'drivers' => [
                    __NAMESPACE__ . '\Entity' =>  __NAMESPACE__ . '_driver'
                ],
            ],
        ],
    ],
    'service_manager' => [
        'factories' => [
            'lrphpt_navigation' => Factory\MenuFactory::class,
        ],
    ],
    
];
