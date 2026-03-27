<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Mailer;

class CrstudenteventsController extends AppController {

    protected array $paginate = ['limit' => 50, 'order' => ['Conventionregistrations.name' => 'asc']];
    public $components = array('PImage', 'PImageTest');

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize(): void {
        parent::initialize();
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }
		
		$this->Conventions = $this->fetchTable('Conventions');
		$this->Events = $this->fetchTable('Events');
		$this->Settings = $this->fetchTable('Settings');
		$this->Seasons = $this->fetchTable('Seasons');
		$this->Emailtemplates = $this->fetchTable('Emailtemplates');
		$this->Conventionregistrations = $this->fetchTable('Conventionregistrations');
    }
	
	public function groups($slug = null) {
		
		$this->set('title', ADMIN_TITLE . 'Groups');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageRegistrations', '1');
        $this->set('registrationsList', '1');
		
		$CRDetails = $this->Conventionregistrations->find()->where(['Conventionregistrations.slug' => $slug])->contain(['Conventions'])->first();
		$this->set('CRDetails', $CRDetails);
			
		$this->set('slug', $slug);
		//$this->prx($convRedG);
		if($CRDetails->id >0)
		{
			// now check groups
			$arrConvGroups 	= array();
			$convGroups 	= $this->Crstudentevents->find()->where(['Crstudentevents.conventionregistration_id' => $CRDetails->id])->order(["Crstudentevents.event_id" => "ASC"])->all();
			
			foreach($convGroups as $convg)
			{	
				if(!empty($convg->group_name))
				{
					$arrConvGroups[$convg->event_id][$convg->group_name][] = $convg->student_id;
				}
			}
			$this->set('arrConvGroups',$arrConvGroups);
			//$this->prx($arrConvGroups);
		}
		else
		{
			$this->Flash->error('Invalid registration.');
		}
		
		//$this->redirect(['controller' => 'conventionregistrations', 'action' => 'index']);
    }
	

}

?>
