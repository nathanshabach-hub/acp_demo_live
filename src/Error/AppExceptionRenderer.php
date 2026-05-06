<?php
namespace App\Error;

use Cake\Error\ExceptionRenderer;

class AppExceptionRenderer extends ExceptionRenderer
{
    protected function _getController()
    {
        $controller = parent::_getController();
        $controller->viewBuilder()->setClassName('App\View\AppView');
        return $controller;
    }
}
