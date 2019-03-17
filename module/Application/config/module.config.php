<?php
namespace Application;

use Zend\Router\Http\Literal;
use Zend\Router\Http\Segment;
use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'router' => [
        'routes' => [
            'home' => [
                'type' => Literal::class,
                'options' => [
                    'route'    => '/',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'account' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/account[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\AccountController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'ticket' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/ticket[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\TicketController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'about' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/about[/:action[/:id]]',
                    'defaults' => [
                        'controller' => Controller\AboutController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
            'application' => [
                'type'    => Segment::class,
                'options' => [
                    'route'    => '/application[/:action]',
                    'defaults' => [
                        'controller' => Controller\IndexController::class,
                        'action'     => 'index',
                    ],
                ],
            ],
        ],
    ],
    'controllers' => [
        'factories' => [
            Controller\AboutController::class => InvokableFactory::class
        ],
    ],
    'view_manager' => [
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => [
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'application/account/index' => __DIR__ . '/../view/application/account/index.phtml',
            'application/account/forgotpassword' => __DIR__ . '/../view/application/account/forgotpassword.phtml',
            'application/account/login' => __DIR__ . '/../view/application/account/login.phtml',
            'application/account/logout' => __DIR__ . '/../view/application/account/logout.phtml',
            'application/about/index' => __DIR__ . '/../view/application/about/index.phtml',
            'application/about/contact' => __DIR__ . '/../view/application/about/contact.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ],
        'template_path_stack' => [
            __DIR__ . '/../view',
        ],
    ],
];
