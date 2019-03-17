<?php
namespace Application;

use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\ModuleManager\Feature\ConfigProviderInterface;

class Module implements ConfigProviderInterface
{
    const VERSION = '3.0.3-dev';

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Model\EnterpriseTable::class => function ($container) {
                    $tableGateway = $container->get(Model\EnterpriseTableGateway::class);
                    return new Model\EnterpriseTable($tableGateway);
                },
                Model\EnterpriseTableGateway::class => function ($container) {
                    $adapater = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Enterprise);
                    return new TableGateway('Enterprise', $adapater, null, $resultSetPrototype);
                },
                Model\UserTable::class => function ($container) {
                    $tableGateway = $container->get(Model\UserTableGateway::class);
                    return new Model\UserTable($tableGateway);
                },
                Model\UserTableGateway::class => function ($container) {
                    $adapater = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\User);
                    return new TableGateway('User', $adapater, null, $resultSetPrototype);
                },
                Model\TicketTable::class => function ($container) {
                    $tableGateway = $container->get(Model\TicketTableGateway::class);
                    return new Model\TicketTable($tableGateway);
                },
                Model\TicketTableGateway::class => function ($container) {
                    $adapater = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Ticket);
                    return new TableGateway('Ticket', $adapater, null, $resultSetPrototype);
                },
                Model\CommentTable::class => function ($container) {
                    $tableGateway = $container->get(Model\CommentTableGateway::class);
                    return new Model\CommentTable($tableGateway);
                },
                Model\CommentTableGateway::class => function ($container) {
                    $adapater = $container->get(AdapterInterface::class);
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Model\Comment);
                    return new TableGateway('Comment', $adapater, null, $resultSetPrototype);
                }
            ]
        ];
    }

    public function getControllerConfig()
    {
          return [
              'factories' => [
                  Controller\IndexController::class => function ($container) {
                      return new Controller\IndexController(
                      );
                  },
                  Controller\AccountController::class => function ($container) {
                      return new Controller\AccountController(
                          $container->get(Model\UserTable::class),
                          $container->get(Model\EnterpriseTable::class)
                      );
                  },
                  Controller\TicketController::class => function ($container) {
                      return new Controller\TicketController(
                          $container->get(Model\UserTable::class),
                          $container->get(Model\EnterpriseTable::class),
                          $container->get(Model\TicketTable::class),
                          $container->get(Model\CommentTable::class)
                      );
                  },
              ]
          ];
    }
}
