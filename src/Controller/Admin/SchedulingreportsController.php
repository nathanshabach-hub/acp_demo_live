<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class SchedulingreportsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];

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
		
		$this->Conventionseasons = $this->loadModel('Conventionseasons');
		$this->Conventions = $this->loadModel('Conventions');
		$this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
		$this->Conventionrooms = $this->loadModel('Conventionrooms');
		$this->Conventionseasonroomevents = $this->loadModel('Conventionseasonroomevents');
		$this->Conventionregistrations = $this->loadModel('Conventionregistrations');
		$this->Conventionregistrationstudents = $this->loadModel('Conventionregistrationstudents');
		$this->Schedulingprogramnotes = $this->loadModel('Schedulingprogramnotes');
		$this->Crstudentevents = $this->loadModel('Crstudentevents');
		$this->Events = $this->loadModel('Events');
		$this->Schedulings = $this->loadModel('Schedulings');
		$this->Schedulingtimings = $this->loadModel('Schedulingtimings');
		$this->Users = $this->loadModel('Users');
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
		if (empty($arrStudentsCS)) {
			$this->set('arrStudentSorted', []);
			$this->set('arrStudentNames', []);
			return;
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
		if (empty($arrStudentsCS)) {
			$this->set('arrStudentSorted', []);
			$this->set('arrStudentNames', []);
			return;
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
	
	/* By Sponsors */
	public function bysponsors($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Sponsors');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);

		$sponsorRows = $this->Conventionregistrationteachers->find()
			->select(['teacher_id'])
			->where([
				'Conventionregistrationteachers.convention_id' => $conventionSD->convention_id,
				'Conventionregistrationteachers.season_id' => $conventionSD->season_id,
				'Conventionregistrationteachers.season_year' => $conventionSD->season_year,
				'Conventionregistrationteachers.status' => 1,
			])
			->all();
		$sponsorIds = [];
		foreach ($sponsorRows as $sp) {
			if ((int)$sp->teacher_id > 0) {
				$sponsorIds[] = (int)$sp->teacher_id;
			}
		}
		$sponsorIds = array_values(array_unique($sponsorIds));

		$sponsorsDD = [];
		if (count($sponsorIds)) {
			$users = $this->Users->find()->where(['Users.id IN' => $sponsorIds])->order(['Users.first_name' => 'ASC', 'Users.last_name' => 'ASC'])->all();
			foreach ($users as $u) {
				$sponsorsDD[(int)$u->id] = trim((string)$u->first_name.' '.(string)$u->last_name);
			}
		}
		$this->set('sponsorsDD', $sponsorsDD);

		if (empty($sponsorsDD)) {
			$this->Flash->error('Sorry, no sponsors found for this convention season.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'reports', $convention_season_slug]);
		}

		if ($this->request->is('post')) {
			$sponsor_id = (int)$this->request->getData('Schedulingreports.sponsor_id');
			return $this->redirect(['controller' => 'schedulingreports', 'action' => 'bysponsorsshow', $convention_season_slug, $sponsor_id]);
		}
    }

	public function bysponsorsshow($convention_season_slug=null,$sponsor_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Reports By Sponsors');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('sponsor_id', $sponsor_id);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);

		$sponsorD = $this->Users->find()->where(['Users.id' => $sponsor_id])->first();
		$this->set('sponsorD', $sponsorD);

		$linkRows = $this->Conventionregistrationteachers->find()->where([
			'Conventionregistrationteachers.convention_id' => $conventionSD->convention_id,
			'Conventionregistrationteachers.season_id' => $conventionSD->season_id,
			'Conventionregistrationteachers.season_year' => $conventionSD->season_year,
			'Conventionregistrationteachers.teacher_id' => $sponsor_id,
			'Conventionregistrationteachers.status' => 1,
		])->all();

		$schoolIds = [];
		foreach ($linkRows as $lr) {
			if ((int)$lr->user_id > 0) {
				$schoolIds[] = (int)$lr->user_id;
			}
		}
		$schoolIds = array_values(array_unique($schoolIds));

		$participantIds = $schoolIds;
		if (count($schoolIds)) {
			$studentsRows = $this->Conventionregistrationstudents->find()
				->select(['student_id'])
				->where([
					'Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,
					'Conventionregistrationstudents.season_id' => $conventionSD->season_id,
					'Conventionregistrationstudents.season_year' => $conventionSD->season_year,
					'Conventionregistrationstudents.user_id IN' => $schoolIds,
				])
				->all();
			foreach ($studentsRows as $sr) {
				if ((int)$sr->student_id > 0) {
					$participantIds[] = (int)$sr->student_id;
				}
			}
		}
		$participantIds = array_values(array_unique($participantIds));

		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		if (count($participantIds)) {
			$ids = implode(',', $participantIds);
			$condSch[] = "(Schedulingtimings.user_id IN ($ids) OR Schedulingtimings.user_id_opponent IN ($ids))";
		} else {
			$condSch[] = "(Schedulingtimings.id = 0)";
		}

		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order(["Schedulingtimings.sch_date_time" => "ASC"])
			->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);

		$schoolNamesMap = [];
		if (count($schoolIds)) {
			$schoolRows = $this->Users->find()->select(['id','first_name'])->where(['Users.id IN' => $schoolIds])->all();
			foreach ($schoolRows as $row) {
				$schoolNamesMap[(int)$row->id] = (string)$row->first_name;
			}
		}
		$this->set('schoolNamesMap', $schoolNamesMap);

		$studentSchoolMap = [];
		if (count($schoolIds)) {
			$studentSchoolRows = $this->Conventionregistrationstudents->find()
				->select(['student_id','user_id'])
				->where([
					'Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,
					'Conventionregistrationstudents.season_id' => $conventionSD->season_id,
					'Conventionregistrationstudents.season_year' => $conventionSD->season_year,
					'Conventionregistrationstudents.user_id IN' => $schoolIds,
				])
				->all();
			foreach ($studentSchoolRows as $ssr) {
				$schoolIdForStudent = (int)$ssr->user_id;
				$studentSchoolMap[(int)$ssr->student_id] = $schoolNamesMap[$schoolIdForStudent] ?? '';
			}
		}
		$this->set('studentSchoolMap', $studentSchoolMap);
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

		$studentSchoolMap = [];
		$studentIds = [];
		foreach ($schedulingTimingsList as $row) {
			if ((int)$row->schedule_category === 4 && (int)$row->user_id > 0) {
				$studentIds[] = (int)$row->user_id;
			}
		}
		$studentIds = array_values(array_unique($studentIds));
		if (count($studentIds)) {
			$studentSchoolRows = $this->Conventionregistrationstudents->find()
				->select(['student_id','user_id'])
				->where([
					'Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,
					'Conventionregistrationstudents.season_id' => $conventionSD->season_id,
					'Conventionregistrationstudents.season_year' => $conventionSD->season_year,
					'Conventionregistrationstudents.student_id IN' => $studentIds,
				])
				->all();
			$schoolIds = [];
			$studentSchoolIds = [];
			foreach ($studentSchoolRows as $ssr) {
				$studentSchoolIds[(int)$ssr->student_id] = (int)$ssr->user_id;
				$schoolIds[] = (int)$ssr->user_id;
			}
			$schoolIds = array_values(array_unique(array_filter($schoolIds)));
			$schoolNames = [];
			if (count($schoolIds)) {
				$schoolRows = $this->Users->find()->select(['id','first_name'])->where(['Users.id IN' => $schoolIds])->all();
				foreach ($schoolRows as $sr) {
					$schoolNames[(int)$sr->id] = (string)$sr->first_name;
				}
			}
			foreach ($studentSchoolIds as $stuId => $schId) {
				$studentSchoolMap[$stuId] = $schoolNames[$schId] ?? '';
			}
		}
		$this->set('studentSchoolMap', $studentSchoolMap);
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

		$studentSchoolMap = [];
		$studentIds = [];
		foreach ($schedulingTimingsList as $row) {
			if ((int)$row->schedule_category === 4 && (int)$row->user_id > 0) {
				$studentIds[] = (int)$row->user_id;
			}
		}
		$studentIds = array_values(array_unique($studentIds));
		if (count($studentIds)) {
			$studentSchoolRows = $this->Conventionregistrationstudents->find()
				->select(['student_id','user_id'])
				->where([
					'Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,
					'Conventionregistrationstudents.season_id' => $conventionSD->season_id,
					'Conventionregistrationstudents.season_year' => $conventionSD->season_year,
					'Conventionregistrationstudents.student_id IN' => $studentIds,
				])
				->all();
			$schoolIds = [];
			$studentSchoolIds = [];
			foreach ($studentSchoolRows as $ssr) {
				$studentSchoolIds[(int)$ssr->student_id] = (int)$ssr->user_id;
				$schoolIds[] = (int)$ssr->user_id;
			}
			$schoolIds = array_values(array_unique(array_filter($schoolIds)));
			$schoolNames = [];
			if (count($schoolIds)) {
				$schoolRows = $this->Users->find()->select(['id','first_name'])->where(['Users.id IN' => $schoolIds])->all();
				foreach ($schoolRows as $sr) {
					$schoolNames[(int)$sr->id] = (string)$sr->first_name;
				}
			}
			foreach ($studentSchoolIds as $stuId => $schId) {
				$studentSchoolMap[$stuId] = $schoolNames[$schId] ?? '';
			}
		}
		$this->set('studentSchoolMap', $studentSchoolMap);
	}

	/* Small Program */
	public function smallprogram($convention_season_slug=null) {
		$this->set('title', ADMIN_TITLE . 'Small Program');
		$this->viewBuilder()->setLayout('admin');

		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		if (!$conventionSD) {
			$this->Flash->error('Sorry, convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);

		if ($this->request->is(['post', 'put'])) {
			$postedNotes = (array)$this->request->getData('Smallprogramnotes');
			$this->saveSmallProgramNotes($conventionSD->id, $postedNotes);
			$this->Flash->success('Small Program notes saved.');
			return $this->redirect(['controller' => 'schedulingreports', 'action' => 'smallprogram', $convention_season_slug]);
		}

		$smallProgramNotes = $this->getSmallProgramNotes($conventionSD, $schedulingD);
		$this->set('smallProgramNotes', $smallProgramNotes);

		$programData = $this->buildSmallProgramData($conventionSD, $schedulingD);
		$this->set('programDays', $programData['programDays']);
		$this->set('programDateRangeLabel', $programData['programDateRangeLabel']);
	}

	public function smallprogramprint($convention_season_slug=null) {
		$this->viewBuilder()->setLayout('print_reports');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		if (!$conventionSD) {
			$this->Flash->error('Sorry, convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}

		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		$this->set('smallProgramNotes', $this->getSmallProgramNotes($conventionSD, $schedulingD));

		$programData = $this->buildSmallProgramData($conventionSD, $schedulingD);
		$this->set('programDays', $programData['programDays']);
		$this->set('programDateRangeLabel', $programData['programDateRangeLabel']);
	}

	private function buildSmallProgramData($conventionSD, $schedulingD = null) {
		$lunchStart = '';
		$lunchEnd = '';
		if ($schedulingD && !empty($schedulingD->lunch_time_start)) {
			$lunchStart = date('H:i:s', strtotime((string)$schedulingD->lunch_time_start));
		}
		if ($schedulingD && !empty($schedulingD->lunch_time_end)) {
			$lunchEnd = date('H:i:s', strtotime((string)$schedulingD->lunch_time_end));
		}

		$condSch = array();
		$condSch[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
		Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
		Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
		Schedulingtimings.season_year = '".$conventionSD->season_year."')";

		$schedulingTimingsList = $this->Schedulingtimings->find()
			->where($condSch)
			->contain(["Events","Conventionrooms","Users","Opponentuser"])
			->order([
				"Schedulingtimings.sch_date_time" => "ASC",
				"Schedulingtimings.start_time" => "ASC",
				"Schedulingtimings.finish_time" => "ASC",
				"Schedulingtimings.room_id" => "ASC",
				"Schedulingtimings.id" => "ASC",
			])
			->all();

		$programDays = array();
		$minDate = null;
		$maxDate = null;

		foreach ($schedulingTimingsList as $row) {
			$startRaw = '';
			if (!empty($row->start_time)) {
				$startRaw = date('H:i:s', strtotime((string)$row->start_time));
			}
			if ($startRaw === '') {
				continue;
			}

			$dayLabel = trim((string)$row->day);
			$schDateRaw = (string)$row->sch_date_time;
			$sortDate = '';
			$dateLabel = '';

			if ($schDateRaw !== '' && $schDateRaw !== '0000-00-00 00:00:00') {
				$ts = strtotime($schDateRaw);
				if ($ts) {
					$sortDate = date('Y-m-d', $ts);
					$dateLabel = date('l jS F, Y', $ts);
					if ($dayLabel === '') {
						$dayLabel = date('l', $ts);
					}
					if ($minDate === null || $sortDate < $minDate) {
						$minDate = $sortDate;
					}
					if ($maxDate === null || $sortDate > $maxDate) {
						$maxDate = $sortDate;
					}
				}
			}

			if ($dayLabel === '') {
				$dayLabel = 'Schedule';
			}

			$dayKey = ($sortDate !== '' ? $sortDate : strtolower($dayLabel));
			if (!isset($programDays[$dayKey])) {
				$programDays[$dayKey] = array(
					'dayLabel' => $dayLabel,
					'dateLabel' => $dateLabel,
					'sortDate' => $sortDate,
					'sessions' => array(),
				);
			}

			$finishRaw = '';
			if (!empty($row->finish_time)) {
				$finishRaw = date('H:i:s', strtotime((string)$row->finish_time));
			}

			$sessionKey = 'day';
			$sessionTitle = 'Convention Events';
			$sessionSort = 2;
			if ($lunchStart !== '' && $lunchEnd !== '' && $startRaw < $lunchStart) {
				$sessionKey = 'morning';
				$sessionTitle = 'Convention Events';
				$sessionSort = 1;
			} elseif ($startRaw >= '18:00:00') {
				$sessionKey = 'evening';
				$sessionTitle = 'Evening Program';
				$sessionSort = 3;
			} elseif ($lunchEnd !== '' && $startRaw >= $lunchEnd) {
				$sessionKey = 'afternoon';
				$sessionTitle = 'Convention Events';
				$sessionSort = 2;
			}

			if (!isset($programDays[$dayKey]['sessions'][$sessionKey])) {
				$programDays[$dayKey]['sessions'][$sessionKey] = array(
					'key' => $sessionKey,
					'title' => $sessionTitle,
					'sortOrder' => $sessionSort,
					'startRaw' => $startRaw,
					'finishRaw' => $finishRaw,
					'rooms' => array(),
				);
			}
			if ($startRaw < $programDays[$dayKey]['sessions'][$sessionKey]['startRaw']) {
				$programDays[$dayKey]['sessions'][$sessionKey]['startRaw'] = $startRaw;
			}
			if ($finishRaw !== '' && ($programDays[$dayKey]['sessions'][$sessionKey]['finishRaw'] === '' || $finishRaw > $programDays[$dayKey]['sessions'][$sessionKey]['finishRaw'])) {
				$programDays[$dayKey]['sessions'][$sessionKey]['finishRaw'] = $finishRaw;
			}

			$roomName = $this->normalizeSmallProgramRoomName((string)($row->Conventionrooms['room_name'] ?? ''));
			if (!isset($programDays[$dayKey]['sessions'][$sessionKey]['rooms'][$roomName])) {
				$programDays[$dayKey]['sessions'][$sessionKey]['rooms'][$roomName] = array();
			}

			$eventName = trim((string)($row->Events['event_name'] ?? 'Untitled Event'));
			if ($eventName === '') {
				$eventName = 'Untitled Event';
			}

			$eventLine = $this->normalizeSmallProgramEventLine($eventName);

			$programDays[$dayKey]['sessions'][$sessionKey]['rooms'][$roomName][$eventLine] = 1;
		}

		if (count($programDays)) {
			ksort($programDays);
			foreach ($programDays as $dayKey => $dayData) {
				$sessionList = array_values($dayData['sessions']);
				usort($sessionList, function($a, $b) {
					if ((int)$a['sortOrder'] === (int)$b['sortOrder']) {
						return strcmp((string)$a['startRaw'], (string)$b['startRaw']);
					}
					return ((int)$a['sortOrder'] < (int)$b['sortOrder']) ? -1 : 1;
				});

				$normalizedSessions = array();
				foreach ($sessionList as $sessionData) {
					$roomNames = array_keys($sessionData['rooms']);
					usort($roomNames, function($a, $b) {
						return $this->getSmallProgramRoomSortOrder($a) <=> $this->getSmallProgramRoomSortOrder($b);
					});
					$normalizedRooms = array();
					foreach ($roomNames as $rn) {
						$eventNames = array_keys($sessionData['rooms'][$rn]);
						natcasesort($eventNames);
						$normalizedRooms[$rn] = array_values($eventNames);
					}
					$sessionData['rooms'] = $normalizedRooms;
					$normalizedSessions[] = $sessionData;
				}

				$programDays[$dayKey]['sessions'] = $normalizedSessions;
			}
		}

		$dateRangeFromSchedule = '';
		if ($minDate !== null && $maxDate !== null) {
			$dateRangeFromSchedule = date('j F', strtotime($minDate)).' - '.date('j F Y', strtotime($maxDate));
		}

		$programDateRangeLabel = $dateRangeFromSchedule;
		if ($schedulingD && !empty($schedulingD->start_date) && (int)$schedulingD->number_of_days > 0) {
			$startTs = strtotime((string)$schedulingD->start_date);
			if ($startTs) {
				$endTs = strtotime('+'.((int)$schedulingD->number_of_days - 1).' day', $startTs);
				$programDateRangeLabel = date('j F', $startTs).' - '.date('j F Y', $endTs);
			}
		}

		return array(
			'programDays' => $programDays,
			'programDateRangeLabel' => $programDateRangeLabel,
		);
	}

	private function normalizeSmallProgramRoomName($roomName) {
		$roomName = trim((string)$roomName);
		if ($roomName === '') {
			return 'Unassigned Location';
		}
		if (stripos($roomName, 'Music Room') === 0 || stripos($roomName, 'Tambourine Music Room') === 0) {
			return 'Music Room 1';
		}
		if (stripos($roomName, 'Platform Room 1') === 0) {
			return 'Platform Room 1';
		}
		if (stripos($roomName, 'Platform Room 2') === 0) {
			return 'Platform Room 2';
		}
		if (stripos($roomName, 'Platform Room 3') === 0 || stripos($roomName, 'Preaching') === 0) {
			return 'Platform Room 3';
		}
		if (stripos($roomName, 'Academics Room') === 0 || stripos($roomName, 'Checkers Board') === 0 || stripos($roomName, 'Chess Board') === 0) {
			return 'Academics';
		}
		if (stripos($roomName, 'Basketball') === 0 || stripos($roomName, 'Futsal') === 0 || stripos($roomName, 'Table Tennis') === 0 || stripos($roomName, 'Tennis Court') === 0 || stripos($roomName, 'Volley Ball') === 0) {
			return 'Sports';
		}
		if (stripos($roomName, 'Main Hall') === 0) {
			return 'Main Hall';
		}
		if (stripos($roomName, 'Master Control') === 0) {
			return 'Master Control';
		}
		return $roomName;
	}

	private function normalizeSmallProgramEventLine($eventLine) {
		$eventLine = preg_replace('/\s*\-\s*Match\s+\d+$/i', '', (string)$eventLine);
		$eventLine = preg_replace('/\s+\(Combined\)|\s+\(Instrumental\)|\s+\(Vocal\)/i', '', (string)$eventLine);
		return trim((string)$eventLine);
	}

	private function getSmallProgramRoomSortOrder($roomName) {
		$map = array(
			'Music Room 1' => 10,
			'Platform Room 1' => 20,
			'Platform Room 2' => 30,
			'Platform Room 3' => 40,
			'Academics' => 50,
			'Main Hall' => 60,
			'Master Control' => 70,
			'Sports' => 80,
		);
		if (isset($map[$roomName])) {
			return $map[$roomName];
		}
		return 1000;
	}

	private function getSmallProgramNotes($conventionSD, $schedulingD = null) {
		$defaults = $this->getDefaultSmallProgramNotes($conventionSD, $schedulingD);
		$existing = $this->Schedulingprogramnotes->find()
			->where(['Schedulingprogramnotes.conventionseasons_id' => $conventionSD->id])
			->first();

		if (!$existing || empty($existing->notes_json)) {
			return $defaults;
		}

		$decoded = json_decode((string)$existing->notes_json, true);
		if (!is_array($decoded)) {
			return $defaults;
		}

		return array_merge($defaults, $decoded);
	}

	private function saveSmallProgramNotes($conventionSeasonId, array $postedNotes) {
		$record = $this->Schedulingprogramnotes->find()
			->where(['Schedulingprogramnotes.conventionseasons_id' => $conventionSeasonId])
			->first();

		if (!$record) {
			$record = $this->Schedulingprogramnotes->newEntity([]);
			$record->conventionseasons_id = $conventionSeasonId;
		}

		// Rebuild intro_entries from parallel arrays intro_time[] and intro_text[]
		$introTimes = isset($postedNotes['intro_time']) && is_array($postedNotes['intro_time']) ? $postedNotes['intro_time'] : array();
		$introTexts = isset($postedNotes['intro_text']) && is_array($postedNotes['intro_text']) ? $postedNotes['intro_text'] : array();
		$introLines = array();
		foreach ($introTimes as $i => $t) {
			$t = trim((string)$t);
			$txt = trim((string)($introTexts[$i] ?? ''));
			if ($t !== '' || $txt !== '') {
				$introLines[] = $t . '|' . $txt;
			}
		}
		$introEntriesStr = implode("\n", $introLines);

		$payload = array(
			'intro_day_label' => trim((string)($postedNotes['intro_day_label'] ?? '')),
			'intro_entries' => $introEntriesStr,
			'dinner_banner' => trim((string)($postedNotes['dinner_banner'] ?? '')),

			'evening_rally_time' => trim((string)($postedNotes['evening_rally_time'] ?? '')),
			'evening_rally_label' => trim((string)($postedNotes['evening_rally_label'] ?? '')),
			'offsite_note' => trim((string)($postedNotes['offsite_note'] ?? '')),
			'footer_note' => trim((string)($postedNotes['footer_note'] ?? '')),
		);

		$record->notes_json = json_encode($payload);
		$this->Schedulingprogramnotes->save($record);
	}

	private function getDefaultSmallProgramNotes($conventionSD, $schedulingD = null) {
		$introDayLabel = 'Sunday';
		if ($schedulingD && !empty($schedulingD->start_date)) {
			$introTs = strtotime('-1 day', strtotime((string)$schedulingD->start_date));
			if ($introTs) {
				$introDayLabel = date('l jS F, Y', $introTs);
			}
		}

		return array(
			'intro_day_label' => $introDayLabel,
			'intro_entries' => "4:00 pm - 5:00 pm|Check in (Master Control)\n5:00 pm - 5:30 pm|Judges & Sponsors meeting (VIP Lounge)",
			'dinner_banner' => 'DINNER 5:15 pm - 6:15 pm',
			'evening_rally_time' => '6:30 pm - 8:30 pm',
			'evening_rally_label' => 'Evening Rally',
			'offsite_note' => '*Tennis offsite',
			'footer_note' => '',
		);
	}

	/* ================================================================
	 * SPORTS / ELIMINATION DRAW
	 * Covers schedule_category 2 (individual elimination) and
	 * schedule_category 3 (team sports elimination).
	 * ================================================================ */

	public function sportsdraw($convention_season_slug = null) {
		$this->set('title', ADMIN_TITLE . 'Sports & Elimination Draw');
		$this->viewBuilder()->setLayout('admin');
		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(['Conventions'])->first();
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);

		// Get distinct event_ids that have elimination scheduling (cat 2 or 3)
		$timingEventRows = $this->Schedulingtimings->find()
			->select(['event_id', 'schedule_category'])
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.schedule_category IN' => [2, 3],
			])
			->distinct(['event_id', 'schedule_category'])
			->all();

		$eventIds = [];
		$eventCategoryMap = [];
		foreach ($timingEventRows as $r) {
			$eid = (int)$r->event_id;
			if ($eid > 0) {
				$eventIds[] = $eid;
				$eventCategoryMap[$eid] = (int)$r->schedule_category;
			}
		}
		$eventIds = array_values(array_unique($eventIds));

		$eventsDD = [];
		if (count($eventIds)) {
			$evRows = $this->Events->find()
				->select(['id', 'event_name'])
				->where(['Events.id IN' => $eventIds])
				->order(['Events.event_name' => 'ASC'])
				->all();
			foreach ($evRows as $r) {
				$cat = $eventCategoryMap[(int)$r->id] ?? 0;
				$label = (string)$r->event_name . ($cat === 3 ? ' (Team)' : ' (Individual)');
				$eventsDD[(int)$r->id] = $label;
			}
		}
		$this->set('eventsDD', $eventsDD);

		if ($this->request->is('post')) {
			$eventId = (int)$this->request->getData('Schedulingreports.event_id');
			return $this->redirect(['controller' => 'schedulingreports', 'action' => 'sportsdrawshow', $convention_season_slug, $eventId]);
		}
	}

	public function sportsdrawshow($convention_season_slug = null, $event_id = null) {
		$this->set('title', ADMIN_TITLE . 'Sports & Elimination Draw');
		$this->viewBuilder()->setLayout('admin');
		$this->set('manageConventions', '1');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);
		$this->set('event_id', $event_id);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(['Conventions'])->first();
		if (!$conventionSD) {
			$this->Flash->error('Convention season not found.');
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);

		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		$this->set('eventD', $eventD);

		// Load all timing rows for this event
		$timingRows = $this->Schedulingtimings->find()
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.event_id' => $event_id,
			])
			->contain(['Conventionrooms'])
			->order(['Schedulingtimings.round_number' => 'ASC', 'Schedulingtimings.match_number' => 'ASC'])
			->all();

		$schedCat = 0;
		$bracketData = []; // [round_number => [match rows]]
		$userIds = [];
		foreach ($timingRows as $row) {
			$round = (int)$row->round_number;
			$schedCat = (int)$row->schedule_category;
			if (!isset($bracketData[$round])) {
				$bracketData[$round] = [];
			}
			$bracketData[$round][] = $row;
			if ($schedCat === 2) {
				if ((int)$row->user_id > 0) $userIds[] = (int)$row->user_id;
				if ((int)$row->user_id_opponent > 0) $userIds[] = (int)$row->user_id_opponent;
			}
		}
		$this->set('bracketData', $bracketData);
		$this->set('schedCat', $schedCat);

		// Build name maps
		$teamNameMap = [];
		if ($schedCat === 3) {
			// Map group_name → school name via crstudentevents
			$cseRows = $this->Crstudentevents->find()
				->select(['group_name', 'user_id'])
				->where([
					'Crstudentevents.conventionseason_id' => $conventionSD->id,
					'Crstudentevents.event_id' => $event_id,
				])
				->all();
			$groupUserMap = [];
			$schoolUserIds = [];
			foreach ($cseRows as $r) {
				$grp = trim((string)$r->group_name);
				$uid = (int)$r->user_id;
				if ($grp !== '' && $uid > 0 && !isset($groupUserMap[$grp])) {
					$groupUserMap[$grp] = $uid;
					$schoolUserIds[] = $uid;
				}
			}
			$schoolUserIds = array_values(array_unique($schoolUserIds));
			if (count($schoolUserIds)) {
				$schoolRows = $this->Users->find()->select(['id','first_name'])->where(['Users.id IN' => $schoolUserIds])->all();
				$schoolNameById = [];
				foreach ($schoolRows as $u) {
					$schoolNameById[(int)$u->id] = (string)$u->first_name;
				}
				foreach ($groupUserMap as $grp => $uid) {
					$teamNameMap[(string)$grp] = $schoolNameById[$uid] ?? 'Team '.$grp;
				}
			}
		} elseif ($schedCat === 2) {
			$userIds = array_values(array_unique($userIds));
			if (count($userIds)) {
				$playerRows = $this->Users->find()->select(['id','first_name','last_name'])->where(['Users.id IN' => $userIds])->all();
				foreach ($playerRows as $u) {
					$teamNameMap[(string)(int)$u->id] = trim((string)$u->first_name.' '.(string)$u->last_name);
				}
			}
		}
		$this->set('teamNameMap', $teamNameMap);
	}

	public function sportsdrawprint($convention_season_slug = null, $event_id = null) {
		$this->viewBuilder()->setLayout('print_reports');
		$this->set('conventionList', '1');
		$this->set('convention_season_slug', $convention_season_slug);
		$this->set('event_id', $event_id);

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(['Conventions'])->first();
		if (!$conventionSD) {
			return $this->redirect(['controller' => 'conventions', 'action' => 'index']);
		}
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);

		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);

		$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
		$this->set('eventD', $eventD);

		$timingRows = $this->Schedulingtimings->find()
			->where([
				'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
				'Schedulingtimings.event_id' => $event_id,
			])
			->contain(['Conventionrooms'])
			->order(['Schedulingtimings.round_number' => 'ASC', 'Schedulingtimings.match_number' => 'ASC'])
			->all();

		$schedCat = 0;
		$bracketData = [];
		$userIds = [];
		foreach ($timingRows as $row) {
			$round = (int)$row->round_number;
			$schedCat = (int)$row->schedule_category;
			if (!isset($bracketData[$round])) {
				$bracketData[$round] = [];
			}
			$bracketData[$round][] = $row;
			if ($schedCat === 2) {
				if ((int)$row->user_id > 0) $userIds[] = (int)$row->user_id;
				if ((int)$row->user_id_opponent > 0) $userIds[] = (int)$row->user_id_opponent;
			}
		}
		$this->set('bracketData', $bracketData);
		$this->set('schedCat', $schedCat);

		$teamNameMap = [];
		if ($schedCat === 3) {
			$cseRows = $this->Crstudentevents->find()
				->select(['group_name', 'user_id'])
				->where([
					'Crstudentevents.conventionseason_id' => $conventionSD->id,
					'Crstudentevents.event_id' => $event_id,
				])
				->all();
			$groupUserMap = [];
			$schoolUserIds = [];
			foreach ($cseRows as $r) {
				$grp = trim((string)$r->group_name);
				$uid = (int)$r->user_id;
				if ($grp !== '' && $uid > 0 && !isset($groupUserMap[$grp])) {
					$groupUserMap[$grp] = $uid;
					$schoolUserIds[] = $uid;
				}
			}
			$schoolUserIds = array_values(array_unique($schoolUserIds));
			if (count($schoolUserIds)) {
				$schoolRows = $this->Users->find()->select(['id','first_name'])->where(['Users.id IN' => $schoolUserIds])->all();
				$schoolNameById = [];
				foreach ($schoolRows as $u) {
					$schoolNameById[(int)$u->id] = (string)$u->first_name;
				}
				foreach ($groupUserMap as $grp => $uid) {
					$teamNameMap[(string)$grp] = $schoolNameById[$uid] ?? 'Team '.$grp;
				}
			}
		} elseif ($schedCat === 2) {
			$userIds = array_values(array_unique($userIds));
			if (count($userIds)) {
				$playerRows = $this->Users->find()->select(['id','first_name','last_name'])->where(['Users.id IN' => $userIds])->all();
				foreach ($playerRows as $u) {
					$teamNameMap[(string)(int)$u->id] = trim((string)$u->first_name.' '.(string)$u->last_name);
				}
			}
		}
		$this->set('teamNameMap', $teamNameMap);
	}



}

?>
