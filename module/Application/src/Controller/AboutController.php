<?php
namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class AboutController extends AbstractActionController
{
    public function indexAction()
    {
        return new ViewModel();
    }

    public function contactAction()
    {
        return new ViewModel();
    }

    public function manualAction()
    {
        return new ViewModel();
    }

    public function impressumAction()
    {
        return new ViewModel();
    }
}
