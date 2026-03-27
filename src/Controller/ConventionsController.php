<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Mailer;
use Cake\I18n\I18n;

class ConventionsController extends AppController {

    public function initialize(): void {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        // Load Files model
		 
		$this->Users = $this->fetchTable('Users'); 
		$this->Emailtemplates = $this->fetchTable('Emailtemplates');
		

        // Set the layout
        // $this->layout = 'frontend';
    } 

}

?>
