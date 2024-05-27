<?php 
declare(strict_types=1);

namespace LRPHPT\MenuTree;

use Laminas\ServiceManager\Factory\InvokableFactory;
use Laminas\Router\Http\Literal;
use Laminas\Router\Http\Segment;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;

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
    'controllers' => [
        'factories' => [Controller\IndexController::class => ReflectionBasedAbstractFactory::class,]
    ],
    'router' => [
        'routes' => [
            'lrphpt-menu' => [
                'type'    => Literal::class,
                'options' => [
                    'route'    => '/lrphpt-menu',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'detail' => [
                        'type' => Segment::class,
                        'options' => [
                            'route' => '/:slug/:id',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'menutree',
                            ],
                        ],
                    ],
                    'update' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/update',
                            'defaults' => [
                                'controller' => Controller\IndexController::class,
                                'action'     => 'update',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
    'lmc_rbac' => [
        'guards' => [
            'LmcRbacMvc\Guard\RouteGuard' => [
                'lrphpt-menu*'         => ['guest'],
            ]
        ],
    ],
];
