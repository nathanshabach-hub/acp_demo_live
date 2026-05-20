<?php
namespace App\Controller;

use App\Controller\AppController;

class TrucksController extends AppController
{
    public function index()
    {
        $this->set('message', 'TrucksController is active.');
    }
}