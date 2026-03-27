<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class SchedulingreportsController extends AppController {

    protected array $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
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
		
		$this->Conventionseasons = $this->fetchTable('Conventionseasons');
		$this->Conventions = $this->fetchTable('Conventions');
		$this->Conventionseasonevents = $this->fetchTable('Conventionseasonevents');
		$this->Conventionrooms = $this->fetchTable('Conventionrooms');
		$this->Conventionseasonroomevents = $this->fetchTable('Conventionseasonroomevents');
		$this->Conventionregistrations = $this->fetchTable('Conventionregistrations');
		$this->Conventionregistrationstudents = $this->fetchTable('Conventionregistrationstudents');
		$this->Events = $this->fetchTable('Events');
		$this->Schedulings = $this->fetchTable('Schedulings');
		$this->Schedulingtimings = $this->fetchTable('Schedulingtimings');
    }
	
	/* By Students */
	public function bystudents($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Schools/Students');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get all the schools who participated in this convention season
		$arrSchoolList = array();
		$schoolsList = $this->Conventionregistrations->find()
			->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id,
			'Conventionregistrations.status' => 1])
			->select(['user_id'])
			->all();
		foreach($schoolsList as $school)
		{
			$arrSchoolList[] = $school->user_id;
		}
		
		if(count($arrSchoolList)>0)
		{
			// now fetch schools name and their id
			$allSchoolsImploded = implode(',',$arrSchoolList);
			$condS = array();
			$condS[] = "(Users.id IN ($allSchoolsImploded) )";
			$condS[] = "(Users.user_type = 'School' )";
				
			$schoolsDD = $this->Users->find()
				->where($condS)
				->order(['Users.first_name' => 'ASC'])
				->all()->combine('id', 'first_name')
				->toArray();
			$this->set('schoolsDD', $schoolsDD);
		}
		else
		{
			$this->Flash->error('Sorry, no school found.');
			$this->redirect(['controller' => 'schedulings', 'action' => 'reports', $convention_season_slug]);
		}
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$school_id 	= $this->request->getData('Schedulingreports.school_id');
			$student_id = $this->request->getData('Schedulingreports.student_id');
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'bystudentsshow',$convention_season_slug,$school_id,$student_id]);
		}
    }
	
	public function bystudentsshow($convention_season_slug=null,$school_id=null,$student_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Students');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
        $this->set('student_id', $student_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// now get all students of this School
		$arrStudentsCS = array();
		$condST = array();
		$condST[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
		$condST[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
		$condST[] = "(Conventionregistrationstudents.user_id = '".$school_id."')";
		
		if($student_id>0)
		{
			$condST[] = "(Conventionregistrationstudents.student_id = '".$student_id."')";
		}
		
		$studentsCS = $this->Conventionregistrationstudents->find()
			->where($condST)
			->select(['student_id'])
			->all();
		
		if($studentsCS)
		{
			foreach($studentsCS as $studentEV)
			{
				$arrStudentsCS[] = $studentEV->student_id;
			}
		}
		$arrStudentsCSImploded = implode(',',$arrStudentsCS);
		//echo $arrStudentsCSImploded;exit;
		
		
		// Now arrange students in alphabetical order
		$arrStudentNames = array();
		$arrStudentSorted 	= array();
		$condStudentSch 	= array();
		$condStudentSch[] = "(Users.id IN ($arrStudentsCSImploded) )";
		$studentsLSch  = $this->Users->find()
			->where($condStudentSch)
			->order(["Users.first_name" => "ASC", "Users.last_name" => "ASC"])
			->all();
		foreach($studentsLSch as $studentSort)
		{
			$arrStudentSorted[] = $studentSort->id;
			
			// save name of students
			$arrStudentNames[$studentSort->id] = $studentSort->first_name.' '.$studentSort->last_name;
		}
		$this->set('arrStudentSorted', $arrStudentSorted);
		$this->set('arrStudentNames', $arrStudentNames);
		
	}
	
	public function bystudentsshowprint($convention_season_slug=null,$school_id=null,$student_id=null) {
		
        $this->viewBuilder()->setLayout('print_reports');
		
		$this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
        $this->set('student_id', $student_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// now get all students of this School
		$arrStudentsCS = array();
		$condST = array();
		$condST[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND   Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
		$condST[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
		$condST[] = "(Conventionregistrationstudents.user_id = '".$school_id."')";
		
		if($student_id>0)
		{
			$condST[] = "(Conventionregistrationstudents.student_id = '".$student_id."')";
		}
		
		$studentsCS = $this->Conventionregistrationstudents->find()
			->where($condST)
			->select(['student_id'])
			->all();
		
		if($studentsCS)
		{
			foreach($studentsCS as $studentEV)
			{
				$arrStudentsCS[] = $studentEV->student_id;
			}
		}
		$arrStudentsCSImploded = implode(',',$arrStudentsCS);
		
		
		// Now arrange students in alphabetical order
		$arrStudentNames = array();
		$arrStudentSorted 	= array();
		$condStudentSch 	= array();
		$condStudentSch[] = "(Users.id IN ($arrStudentsCSImploded) )";
		$studentsLSch  = $this->Users->find()
			->where($condStudentSch)
			->order(["Users.first_name" => "ASC", "Users.last_name" => "ASC"])
			->all();
		foreach($studentsLSch as $studentSort)
		{
			$arrStudentSorted[] = $studentSort->id;
			
			// save name of students
			$arrStudentNames[$studentSort->id] = $studentSort->first_name.' '.$studentSort->last_name;
		}
		$this->set('arrStudentSorted', $arrStudentSorted);
		$this->set('arrStudentNames', $arrStudentNames);
		
	}
	
	/* By Schools */
	public function byschools($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Schools');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get all the schools who participated in this convention season
		$arrSchoolList = array();
		$schoolsList = $this->Conventionregistrations->find()
			->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id,
			'Conventionregistrations.status' => 1])
			->select(['user_id'])
			->all();
		foreach($schoolsList as $school)
		{
			$arrSchoolList[] = $school->user_id;
		}
		
		if(count($arrSchoolList)>0)
		{
			// now fetch schools name and their id
			$allSchoolsImploded = implode(',',$arrSchoolList);
			$condS = array();
			$condS[] = "(Users.id IN ($allSchoolsImploded) )";
			$condS[] = "(Users.user_type = 'School' )";
				
			$schoolsDD = $this->Users->find()
				->where($condS)
				->order(['Users.first_name' => 'ASC'])
				->all()->combine('id', 'first_name')
				->toArray();
			$this->set('schoolsDD', $schoolsDD);
		}
		else
		{
			$this->Flash->error('Sorry, no school found.');
			$this->redirect(['controller' => 'schedulings', 'action' => 'reports', $convention_season_slug]);
		}
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$school_id 	= $this->request->getData('Schedulingreports.school_id');
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'byschoolsshow',$convention_season_slug,$school_id]);
		}
    }
	
	public function byschoolsshow($convention_season_slug=null,$school_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Schools');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// Now we need to get list of students of this school so that we will show individual games as well
		$studentsList = $this->Conventionregistrationstudents->find()
		->select(['student_id'])
		->where(
			[
			"Conventionregistrationstudents.convention_id" => $conventionSD->convention_id,
			"Conventionregistrationstudents.season_id" => $conventionSD->season_id,
			"Conventionregistrationstudents.season_year" => $conventionSD->season_year,
			"Conventionregistrationstudents.user_id" => $school_id,
			]
		)
		->extract('student_id')
		->toList();
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		//$condSch[] = "(Schedulingtimings.user_id = '".$school_id."')";
		
		if(count($studentsList))
		{
			$studentsListImplode = implode(",",$studentsList);
			$condSch[] = "(Schedulingtimings.user_id = '".$school_id."' OR Schedulingtimings.user_id IN ($studentsListImplode) OR Schedulingtimings.user_id_opponent IN ($studentsListImplode))";
		}
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
			
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	public function byschoolsshowprint($convention_season_slug=null,$school_id=null) {
		
        $this->viewBuilder()->setLayout('print_reports');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$schoolD = $this->Users->find()->where(['Users.id' => $school_id])->first();
		$this->set('schoolD', $schoolD);
		
		// Now we need to get list of students of this school so that we will show individual games as well
		$studentsList = $this->Conventionregistrationstudents->find()
		->select(['student_id'])
		->where(
			[
			"Conventionregistrationstudents.convention_id" => $conventionSD->convention_id,
			"Conventionregistrationstudents.season_id" => $conventionSD->season_id,
			"Conventionregistrationstudents.season_year" => $conventionSD->season_year,
			"Conventionregistrationstudents.user_id" => $school_id,
			]
		)
		->extract('student_id')
		->toList();
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		//$condSch[] = "(Schedulingtimings.user_id = '".$school_id."')";
		
		if(count($studentsList))
		{
			$studentsListImplode = implode(",",$studentsList);
			$condSch[] = "(Schedulingtimings.user_id = '".$school_id."' OR Schedulingtimings.user_id IN ($studentsListImplode) OR Schedulingtimings.user_id_opponent IN ($studentsListImplode))";
		}
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	
	/* By Events */
	public function byevents($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Events/Sport');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// To get list of events selected in this convention season
		$eventsList = array();
		$conventionseasonevents = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(["Events"])->all();
		foreach($conventionseasonevents as $convseventrec)
		{
			$eventsList[$convseventrec->event_id] = $convseventrec->Events['event_name'].' ('.$convseventrec->Events['event_id_number'].')';
		}
		asort($eventsList);
		$this->set('eventsList', $eventsList);
		
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$event_id 	= $this->request->getData('Schedulingreports.event_id');
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'byeventsshow',$convention_season_slug,$event_id]);
		}
    }
	
	public function byeventsshow($convention_season_slug=null,$event_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Events/Sport');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('event_id', $event_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		$this->set('eventD', $eventD);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.event_id = '".$event_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	public function byeventsshowprint($convention_season_slug=null,$event_id=null) {
        
        $this->viewBuilder()->setLayout('print_reports');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('school_id', $school_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		$this->set('eventD', $eventD);
		
		$this->set('event_id', $event_id);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.event_id = '".$event_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	
	/* By Rooms/Location */
	public function byrooms($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Rooms/Location');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// To get list of rooms selected in this convention season
		$allRoomsArr = array();
		$roomsList = array();
		$conventionseasonroomevents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->contain(["Conventionrooms"])->all();
		foreach($conventionseasonroomevents as $convroomrec)
		{
			if(!in_array($convroomrec->room_id,$allRoomsArr))
			{
				$roomsList[$convroomrec->room_id] = $convroomrec->Conventionrooms['room_name'];
				
				$allRoomsArr[] = $convroomrec->room_id;
			}
			
		}
		asort($roomsList);
		$this->set('roomsList', $roomsList);
		
		
		if ($this->request->is('post')) {
			
			//$this->prx($this->request->getData());
			
			$room_id 	= $this->request->getData('Schedulingreports.room_id');
			
			$this->redirect(['controller' => 'schedulingreports', 'action' => 'byroomsshow',$convention_season_slug,$room_id]);
		}
    }
	
	public function byroomsshow($convention_season_slug=null,$room_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Rooms/Location');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('room_id', $room_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$roomD = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $room_id])->first();
		$this->set('roomD', $roomD);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.room_id = '".$room_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	public function byroomsshowprint($convention_season_slug=null,$room_id=null) {
        
        $this->viewBuilder()->setLayout('print_reports');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('room_id', $room_id);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// to get school details
		$roomD = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $room_id])->first();
		$this->set('roomD', $roomD);
		
		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condSch[] = "(Schedulingtimings.room_id = '".$room_id."')";
		
		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
	}
	
	
	
	

}

?>
