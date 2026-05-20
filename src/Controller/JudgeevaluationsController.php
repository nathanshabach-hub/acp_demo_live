<?php

namespace App\Controller;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use App\Mailer\AppMailer as Mailer;
use Cake\I18n\I18n;

class JudgeevaluationsController extends AppController {

    public $paginate = ['limit' => 50];
    public $components = array('PImage');
	
	public function initialize(): void {
        parent::initialize();

        // Include the FlashComponent
        $this->loadComponent('Flash');

        $this->Users = $this->loadModel('Users');
		$this->Emailtemplates = $this->loadModel('Emailtemplates');
		$this->Users = $this->loadModel('Users');
		$this->Conventionregistrations = $this->loadModel('Conventionregistrations');
		$this->Eventsubmissions = $this->loadModel('Eventsubmissions');
		$this->Events = $this->loadModel('Events');
		$this->Evaluationforms = $this->loadModel('Evaluationforms');
		$this->Judgeevaluations = $this->loadModel('Judgeevaluations');
		$this->Judgeevaluationmarks = $this->loadModel('Judgeevaluationmarks');
		$this->Settings = $this->loadModel('Settings');
		$this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
		$this->Crstudentevents = $this->loadModel('Crstudentevents');
		$this->Conventionregistrationstudents = $this->loadModel('Conventionregistrationstudents');
		$this->Resultpositions = $this->loadModel('Resultpositions');
    }
	
	public function lowscoresave($conv_reg_slug=null,$event_submission_slug=null) {
		//echo 'ddd';exit;
		$this->userLoginCheck();
		
		$user_id 	= $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewBuilder()->setLayout("home");		
		$this->set('title_for_layout', 'Low Score '.TITLE_FOR_PAGES);
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$this->set('active_cr_judgeevents','active');
		}
		else
		{
			$this->set('active_convention_registrations','active');
		}
		
		$this->set('conv_reg_slug',$conv_reg_slug);
		$this->set('event_submission_slug',$event_submission_slug);
		
		// To get data from session
		$judges_evaluation_low_score_form_data 	= $this->request->getSession()->read("judges_evaluation_low_score_form_data");
		$judges_evaluation_low_score 	= $this->request->getSession()->read("judges_evaluation_low_score");
		$this->set('judges_evaluation_low_score',$judges_evaluation_low_score);
		
		// to get eventsubmission details
		$eventsubmissionD 	= $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $event_submission_slug])->contain(["Users","Students"])->first();
		$this->set('eventsubmissionD', $eventsubmissionD);
		$event_id_number 	= $eventsubmissionD->event_id_number;
		//$event_id_number 	= '012';
		//$this->prx($eventsubmissionD);
		
		$event_id_number = str_pad($event_id_number, 3, "0", STR_PAD_LEFT);
		
		$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $eventsubmissionD->conventionregistration_id])->first();
		$this->set('convRegD', $convRegD);
		
		$eventD = $this->Events->find()->where(['Events.id' => $eventsubmissionD->event_id])->first();
		$this->set('eventD', $eventD);
		//$this->prx($eventD);
		
		// now fetch the form based on event id number
		$condEvalForm = array();
		$condEvalForm[] = "(Evaluationforms.event_id_numbers LIKE '".$event_id_number."' OR Evaluationforms.event_id_numbers LIKE '".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number."')";
		
		$evalFormD = $this->Evaluationforms->find()->where($condEvalForm)->order(["Evaluationforms.id" => "DESC"])->first();
		
		
		if ($this->request->is(['post']))
		{	
			//$this->prx($this->request->getData());
			$low_score_pin = $this->request->getData('low_score_pin');
			
			//Now verify this pin
			$settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
			if($settingsInfo->judges_low_score_saving_pin == $low_score_pin)
			{
				//Now save form data
				// to check that if This Judge already submitted evaluation for this submission + event
				$condEvalJudge = array();
				$condEvalJudge[] = "(Judgeevaluations.eventsubmission_id = '".$eventsubmissionD->id."')";
				$condEvalJudge[] = "(Judgeevaluations.conventionregistration_id = '".$eventsubmissionD->conventionregistration_id."')";
				$condEvalJudge[] = "(Judgeevaluations.event_id = '".$eventsubmissionD->event_id."')";
				$condEvalJudge[] = "(Judgeevaluations.uploaded_by_user_id = '".$user_id."')";
				$checkEvalJudge = $this->Judgeevaluations->find()->where($condEvalJudge)->contain(['Judgeevaluationmarks'])->first();
				
				// to check that if This Judge already submitted evaluation for this submission + event
				if($checkEvalJudge)
				{
					$this->Judgeevaluations->deleteAll(["id" => $checkEvalJudge->id]);
					
					// delete judgeevaluationmarks as well
					$this->Judgeevaluationmarks->deleteAll(["judgeevaluation_id" => $checkEvalJudge->id]);
				}
				
				$request_data = $judges_evaluation_low_score_form_data;
				
				if(count((array)$request_data['division_ids']))
				{
					$divSelected = implode(",",$request_data['division_ids']);
				}
				
				if(count((array)$request_data['tags']))
				{
					$tagsSelected = implode(",",$request_data['tags']);
				}
				
				$max_questions = $request_data['max_questions'];
				
				//$this->prx($request_data);
				
				
				// insert new record in judgeevaluations table
				$judgeevaluations = $this->Judgeevaluations->newEntity([]);
				$dataJ = $this->Judgeevaluations->patchEntity($judgeevaluations, array());
				
				$dataJ->slug 							= "judge-event-evaluation-".$eventsubmissionD->id.'-'.time();
				$dataJ->eventsubmission_id				= $eventsubmissionD->id;
				$dataJ->conventionregistration_id		= $eventsubmissionD->conventionregistration_id;
				$dataJ->conventionseason_id				= $eventsubmissionD->conventionseason_id;
				$dataJ->convention_id					= $eventsubmissionD->convention_id;
				$dataJ->user_id							= $eventsubmissionD->user_id;
				$dataJ->season_id						= $eventsubmissionD->season_id;
				$dataJ->season_year						= $eventsubmissionD->season_year;
				$dataJ->event_id						= $eventsubmissionD->event_id;
				$dataJ->event_id_number					= $eventsubmissionD->event_id_number;
				$dataJ->group_name						= $eventsubmissionD->group_name;
				$dataJ->student_id						= $eventsubmissionD->student_id;
				$dataJ->uploaded_by_user_id				= $user_id;
				$dataJ->evaluationform_id				= $evalFormD->id;
				$dataJ->division_ids					= $divSelected;
				$dataJ->tag_ids							= $tagsSelected;
				$dataJ->comments						= $request_data['comments'];
				$dataJ->created 						= date('Y-m-d H:i:s');

				$resultJ = $this->Judgeevaluations->save($dataJ);
				//$this->prx($resultJ);
				
				// now loop through each question & collect marks
				$totalMarksPossible = 0;
				$totalMarksObtained = 0;
				for($cntrQL=1;$cntrQL<=$max_questions;$cntrQL++)
				{
					$question_id 					= $request_data['question_id_'.$cntrQL];
					$question_marks_possible		= $request_data['question_marks_possible_'.$cntrQL];
					$question_marks_obtained 		= $request_data['question_marks_obtained_'.$cntrQL];
					
					$totalMarksPossible 			= $totalMarksPossible + $question_marks_possible;
					$totalMarksObtained 			= intval($totalMarksObtained) + intval($question_marks_obtained);
					
					$judgeevaluationmarks = $this->Judgeevaluationmarks->newEntity([]);
					$dataM = $this->Judgeevaluationmarks->patchEntity($judgeevaluationmarks, array());
					
					$dataM->judgeevaluation_id 			= $resultJ->id;
					$dataM->question_id					= $question_id;
					$dataM->question_marks_possible		= $question_marks_possible;
					$dataM->question_marks_obtained		= $question_marks_obtained;
					$dataM->created 					= date('Y-m-d H:i:s');

					$resultM = $this->Judgeevaluationmarks->save($dataM);
				}
				
				// now update total marks possible and obtained
				$this->Judgeevaluations->updateAll(
				[
					'total_marks_possible' => $totalMarksPossible,
					'total_marks_obtained' => $totalMarksObtained,
					'modified' => date('Y-m-d H:i:s')
				]
				, ["id" => $resultJ->id]);
				
				// to check if any negative marks question posted
				if($request_data['negative_question_id'] >0)
				{
					$negative_question_id 				= $request_data['negative_question_id'];
					$negative_question_marks_obtained 	= $request_data['negative_question_marks_obtained'];
					$negative_question_marks_possible 	= $request_data['negative_question_marks_possible'];
					
					// add this negative question in db table
					$judgeevaluationmarks = $this->Judgeevaluationmarks->newEntity([]);
					$dataM = $this->Judgeevaluationmarks->patchEntity($judgeevaluationmarks, array());
					
					$dataM->judgeevaluation_id 			= $resultJ->id;
					$dataM->question_id					= $negative_question_id;
					$dataM->question_marks_possible		= $negative_question_marks_possible;
					$dataM->question_marks_obtained		= $negative_question_marks_obtained;
					$dataM->created 					= date('Y-m-d H:i:s');

					$resultM = $this->Judgeevaluationmarks->save($dataM);
					
					// now update total marks possible and obtained
					$this->Judgeevaluations->updateAll(
					[
						'total_marks_possible' => $totalMarksPossible,
						'total_marks_obtained' => intval($totalMarksObtained)+intval($negative_question_marks_obtained),
						
						'total_negative_marks_possible' => $negative_question_marks_possible,
						'total_negative_marks_obtained' => $negative_question_marks_obtained,
						'modified' => date('Y-m-d H:i:s')
					]
					, ["id" => $resultJ->id]);
					
				}
				
				// remove session data
				$this->request->getSession()->delete('judges_evaluation_low_score_form_data');
				$this->request->getSession()->delete('judges_evaluation_low_score');
				
				$this->Flash->success('Evaluation submitted successfully.');
				$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$convRegD->slug,$eventD->slug]);
				
			}
			else
			{
				//generate error
				$this->Flash->error('Invalid pin entered.');
			}
			
		}
		
	}
	
	public function addnew($conv_reg_slug=null,$event_submission_slug=null) {
		//echo 'ddd';exit;
		$this->userLoginCheck();
		
		$alphabetArr = range('A', 'Z');
		$this->set('alphabetArr', $alphabetArr);
		
		global $romanNumbers;
		$this->set('romanNumbers', $romanNumbers);
		
		$user_id 	= $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		
		//echo ' fsdf sdf sdf d';exit;
		$this->viewBuilder()->setLayout("home");		
		$this->set('title_for_layout', 'Judging Form '.TITLE_FOR_PAGES);
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$this->set('active_cr_judgeevents','active');
		}
		else
		{
			$this->set('active_convention_registrations','active');
		}
		
		$this->set('conv_reg_slug',$conv_reg_slug);
		
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		$this->set('userDetails', $userDetails);
		
		// to get eventsubmission details
		$eventsubmissionD 	= $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $event_submission_slug])->contain(["Users","Students"])->first();
		$this->set('eventsubmissionD', $eventsubmissionD);
		$event_id_number 	= $eventsubmissionD->event_id_number;
		//$event_id_number 	= '012';
		//$this->prx($eventsubmissionD);
		
		$event_id_number = str_pad($event_id_number, 3, "0", STR_PAD_LEFT);
		
		$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $eventsubmissionD->conventionregistration_id])->first();
		$this->set('convRegD', $convRegD);
		
		$eventD = $this->Events->find()->where(['Events.id' => $eventsubmissionD->event_id])->first();
		$this->set('eventD', $eventD);
		//$this->prx($eventD);
		
		// to check if judging is closed or not
		$conventionSeasonEventD 	= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $eventsubmissionD->conventionseasons_id,'Conventionseasonevents.convention_id' => $eventsubmissionD->convention_id,'Conventionseasonevents.season_id' => $eventsubmissionD->season_id,'Conventionseasonevents.season_year' => $eventsubmissionD->season_year,'Conventionseasonevents.event_id' => $eventsubmissionD->event_id])->first();
		if(!empty($conventionSeasonEventD) && $conventionSeasonEventD->judging_ends == 1)
		{
			$this->Flash->error('Sorry, judging ends for this event.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$convRegD->slug,$eventD->slug]);
		}
		
		// to check if this entry submitted for a student or a group
		if($eventsubmissionD->student_id >0)
		{
			$stGrpName = $eventsubmissionD->Students['first_name'].' '.$eventsubmissionD->Students['middle_name'].' '.$eventsubmissionD->Students['last_name'];
			if(!empty($eventsubmissionD->Students['birth_year']))
			{
				$studentBY = $eventsubmissionD->Students['birth_year'];
				$this->set('studentBY', $studentBY);
			}
		}
		else
		if(!empty($eventsubmissionD->group_name))
		{
			$stGrpName =  $eventsubmissionD->group_name;
		}
		$this->set('stGrpName', $stGrpName);
		
		// to list all members of group, if its a group event
		$groupMembersList = array();
		if($eventD->group_event_yes_no == 1)
		{
			$condAllUGroup = array();
			$condAllUGroup[] = "(Crstudentevents.conventionregistration_id = '".$eventsubmissionD->conventionregistration_id."' AND Crstudentevents.event_id = '".$eventD->id."' AND Crstudentevents.group_name = '".$eventsubmissionD->group_name."')";
			
			$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->all();
			
			foreach($listAllUGroup as $datamembgroup)
			{
				$dGrpM =  $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name'];
				$groupMembersList[] = $dGrpM;
			}
		}
		$this->set('groupMembersList', $groupMembersList);
		
		
		// now fetch the form based on event id number
		$condEvalForm = array();
		$condEvalForm[] = "(Evaluationforms.event_id_numbers LIKE '".$event_id_number."' OR Evaluationforms.event_id_numbers LIKE '".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number."')";
		
		$evalFormD = $this->Evaluationforms->find()->where($condEvalForm)->order(["Evaluationforms.id" => "DESC"])->first();
		$this->set('evalFormD', $evalFormD);
		//$this->prx($condEvalForm);
		if (empty($evalFormD) || empty($evalFormD->id)) {
			$this->Flash->error('No judging form is configured for event '.$event_id_number.'. Please contact admin.');
			return $this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries', $convRegD->slug, $eventD->slug]);
		}
		
		// to check that if This Judge already submitted evaluation for this submission + event
		$condEvalJudge = array();
		$condEvalJudge[] = "(Judgeevaluations.eventsubmission_id = '".$eventsubmissionD->id."')";
		$condEvalJudge[] = "(Judgeevaluations.conventionregistration_id = '".$eventsubmissionD->conventionregistration_id."')";
		$condEvalJudge[] = "(Judgeevaluations.event_id = '".$eventsubmissionD->event_id."')";
		$condEvalJudge[] = "(Judgeevaluations.uploaded_by_user_id = '".$user_id."')";
		$checkEvalJudge = $this->Judgeevaluations->find()->where($condEvalJudge)->contain(['Judgeevaluationmarks'])->first();
		$this->set('checkEvalJudge', $checkEvalJudge);
		
		/* $this->Judgeevaluations->deleteAll(["id" => $checkEvalJudge->id]);
		$this->Judgeevaluationmarks->deleteAll(["judgeevaluation_id" => $checkEvalJudge->id]); */
		
		if ($this->request->is(['post']))
		{	
			//$this->prx($this->request->getData());
			
			$calc_points_allotted = $this->request->getData('calc_points_allotted');
			
			if($calc_points_allotted<50)
			{	
				// Now save the data in session and redirect to a page to ask pin to save low score
				$this->request->getSession()->write("judges_evaluation_low_score_form_data", $this->request->getData());
				$this->request->getSession()->write("judges_evaluation_low_score", $calc_points_allotted);
				
				$this->redirect(['controller' => 'judgeevaluations', 'action' => 'lowscoresave',$conv_reg_slug,$event_submission_slug]);
			}
			else
			{
				// to check that if This Judge already submitted evaluation for this submission + event
				if($checkEvalJudge)
				{
					$this->Judgeevaluations->deleteAll(["id" => $checkEvalJudge->id]);
					
					// delete judgeevaluationmarks as well
					$this->Judgeevaluationmarks->deleteAll(["judgeevaluation_id" => $checkEvalJudge->id]);
				}
				
				if(count((array)$this->request->getData('division_ids')))
				{
					$divSelected = implode(",",$this->request->getData('division_ids'));
				}
				
				if(count((array)$this->request->getData('tags')))
				{
					$tagsSelected = implode(",",$this->request->getData('tags'));
				}
				
				$max_questions = $this->request->getData('max_questions');
				
				
				// insert new record in judgeevaluations table
				$judgeevaluations = $this->Judgeevaluations->newEntity([]);
				$dataJ = $this->Judgeevaluations->patchEntity($judgeevaluations, $this->request->getData());
				
				$dataJ->slug 							= "judge-event-evaluation-".$eventsubmissionD->id.'-'.time();
				$dataJ->eventsubmission_id				= $eventsubmissionD->id;
				$dataJ->conventionregistration_id		= $eventsubmissionD->conventionregistration_id;
				$dataJ->conventionseason_id				= $eventsubmissionD->conventionseason_id;
				$dataJ->convention_id					= $eventsubmissionD->convention_id;
				$dataJ->user_id							= $eventsubmissionD->user_id;
				$dataJ->season_id						= $eventsubmissionD->season_id;
				$dataJ->season_year						= $eventsubmissionD->season_year;
				$dataJ->event_id						= $eventsubmissionD->event_id;
				$dataJ->event_id_number					= $eventsubmissionD->event_id_number;
				$dataJ->group_name						= $eventsubmissionD->group_name;
				$dataJ->student_id						= $eventsubmissionD->student_id;
				$dataJ->uploaded_by_user_id				= $user_id;
				$dataJ->evaluationform_id				= $evalFormD->id;
				$dataJ->division_ids					= $divSelected;
				$dataJ->tag_ids							= $tagsSelected;
				$dataJ->comments						= $this->request->getData('comments');
				$dataJ->created 						= date('Y-m-d H:i:s');

				$resultJ = $this->Judgeevaluations->save($dataJ);
				//$this->prx($resultJ);
				
				// now loop through each question & collect marks
				$totalMarksPossible = 0;
				$totalMarksObtained = 0;
				for($cntrQL=1;$cntrQL<=$max_questions;$cntrQL++)
				{
					$question_id 					= $this->request->getData('question_id_'.$cntrQL);
					$question_marks_possible		= $this->request->getData('question_marks_possible_'.$cntrQL);
					$question_marks_obtained 		= $this->request->getData('question_marks_obtained_'.$cntrQL);
					
					$totalMarksPossible 			= $totalMarksPossible + $question_marks_possible;
					$totalMarksObtained 			= intval($totalMarksObtained) + intval($question_marks_obtained);
					
					$judgeevaluationmarks = $this->Judgeevaluationmarks->newEntity([]);
					$dataM = $this->Judgeevaluationmarks->patchEntity($judgeevaluationmarks, $this->request->getData());
					
					$dataM->judgeevaluation_id 			= $resultJ->id;
					$dataM->question_id					= $question_id;
					$dataM->question_marks_possible		= $question_marks_possible;
					$dataM->question_marks_obtained		= $question_marks_obtained;
					$dataM->created 					= date('Y-m-d H:i:s');

					$resultM = $this->Judgeevaluationmarks->save($dataM);
				}
				
				// now update total marks possible and obtained
				$this->Judgeevaluations->updateAll(
				[
					'total_marks_possible' => $totalMarksPossible,
					'total_marks_obtained' => $totalMarksObtained,
					'modified' => date('Y-m-d H:i:s')
				]
				, ["id" => $resultJ->id]);
				
				// to check if any negative marks question posted
				if($this->request->getData('negative_question_id') >0)
				{
					$negative_question_id 				= $this->request->getData('negative_question_id');
					$negative_question_marks_obtained 	= $this->request->getData('negative_question_marks_obtained');
					$negative_question_marks_possible 	= $this->request->getData('negative_question_marks_possible');
					
					// add this negative question in db table
					$judgeevaluationmarks = $this->Judgeevaluationmarks->newEntity([]);
					$dataM = $this->Judgeevaluationmarks->patchEntity($judgeevaluationmarks, $this->request->getData());
					
					$dataM->judgeevaluation_id 			= $resultJ->id;
					$dataM->question_id					= $negative_question_id;
					$dataM->question_marks_possible		= $negative_question_marks_possible;
					$dataM->question_marks_obtained		= $negative_question_marks_obtained;
					$dataM->created 					= date('Y-m-d H:i:s');

					$resultM = $this->Judgeevaluationmarks->save($dataM);
					
					// now update total marks possible and obtained
					$this->Judgeevaluations->updateAll(
					[
						'total_marks_possible' => $totalMarksPossible,
						'total_marks_obtained' => intval($totalMarksObtained)+intval($negative_question_marks_obtained),
						
						'total_negative_marks_possible' => $negative_question_marks_possible,
						'total_negative_marks_obtained' => $negative_question_marks_obtained,
						'modified' => date('Y-m-d H:i:s')
					]
					, ["id" => $resultJ->id]);
					
				}
				
				$this->Flash->success('Evaluation submitted successfully.');
				$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$convRegD->slug,$eventD->slug]);
			}
		}
    }
	
	public function markdidnotattend($conv_reg_slug=null,$event_submission_slug=null)
	{
		$this->userLoginCheck();
		
		$user_id 	= $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		$this->set('userDetails', $userDetails);
		
		// to get eventsubmission details
		$eventsubmissionD 	= $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $event_submission_slug])->contain(["Users","Students"])->first();
		$this->set('eventsubmissionD', $eventsubmissionD);
		$event_id_number 	= $eventsubmissionD->event_id_number;
		
		$event_id_number = str_pad($event_id_number, 3, "0", STR_PAD_LEFT);
		
		$convRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $eventsubmissionD->conventionregistration_id])->first();
		$this->set('convRegD', $convRegD);
		
		$eventD = $this->Events->find()->where(['Events.id' => $eventsubmissionD->event_id])->first();
		$this->set('eventD', $eventD);
		//$this->prx($eventD);
		
		// to check if judging is closed or not
		$conventionSeasonEventD 	= $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $eventsubmissionD->conventionseasons_id,'Conventionseasonevents.convention_id' => $eventsubmissionD->convention_id,'Conventionseasonevents.season_id' => $eventsubmissionD->season_id,'Conventionseasonevents.season_year' => $eventsubmissionD->season_year,'Conventionseasonevents.event_id' => $eventsubmissionD->event_id])->first();
		if(!empty($conventionSeasonEventD) && $conventionSeasonEventD->judging_ends == 1)
		{
			$this->Flash->error('Sorry, judging ends for this event.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$convRegD->slug,$eventD->slug]);
		}
		
		
		// now fetch the form based on event id number
		$condEvalForm = array();
		$condEvalForm[] = "(Evaluationforms.event_id_numbers LIKE '".$event_id_number."' OR Evaluationforms.event_id_numbers LIKE '".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number.",%' OR Evaluationforms.event_id_numbers LIKE '%,".$event_id_number."')";
		
		$evalFormD = $this->Evaluationforms->find()->where($condEvalForm)->order(["Evaluationforms.id" => "DESC"])->first();
		$this->set('evalFormD', $evalFormD);
		//$this->prx($condEvalForm);
		
		// to check that if This Judge already submitted evaluation for this submission + event
		$condEvalJudge = array();
		$condEvalJudge[] = "(Judgeevaluations.eventsubmission_id = '".$eventsubmissionD->id."')";
		$condEvalJudge[] = "(Judgeevaluations.conventionregistration_id = '".$eventsubmissionD->conventionregistration_id."')";
		$condEvalJudge[] = "(Judgeevaluations.event_id = '".$eventsubmissionD->event_id."')";
		$condEvalJudge[] = "(Judgeevaluations.uploaded_by_user_id = '".$user_id."')";
		$checkEvalJudge = $this->Judgeevaluations->find()->where($condEvalJudge)->contain(['Judgeevaluationmarks'])->first();
		$this->set('checkEvalJudge', $checkEvalJudge);
		
		if($checkEvalJudge)
		{
			$this->Judgeevaluations->deleteAll(["id" => $checkEvalJudge->id]);
			
			// delete judgeevaluationmarks as well
			$this->Judgeevaluationmarks->deleteAll(["judgeevaluation_id" => $checkEvalJudge->id]);
		}
		
		// insert new record in judgeevaluations table as dis not attent
		$judgeevaluations = $this->Judgeevaluations->newEntity([]);
		$dataJ = $this->Judgeevaluations->patchEntity($judgeevaluations, $this->request->getData());
		
		$dataJ->slug 							= "judge-event-evaluation-".$eventsubmissionD->id.'-'.time();
		$dataJ->eventsubmission_id				= $eventsubmissionD->id;
		$dataJ->conventionregistration_id		= $eventsubmissionD->conventionregistration_id;
		$dataJ->conventionseason_id				= $eventsubmissionD->conventionseason_id;
		$dataJ->convention_id					= $eventsubmissionD->convention_id;
		$dataJ->user_id							= $eventsubmissionD->user_id;
		$dataJ->season_id						= $eventsubmissionD->season_id;
		$dataJ->season_year						= $eventsubmissionD->season_year;
		$dataJ->event_id						= $eventsubmissionD->event_id;
		$dataJ->event_id_number					= $eventsubmissionD->event_id_number;
		$dataJ->group_name						= $eventsubmissionD->group_name;
		$dataJ->student_id						= $eventsubmissionD->student_id;
		$dataJ->uploaded_by_user_id				= $user_id;
		$dataJ->evaluationform_id				= $evalFormD->id;
		$dataJ->division_ids					= NULL;
		$dataJ->tag_ids							= NULL;
		$dataJ->comments						= 'Did not attend';
		$dataJ->total_marks_possible			= 0;
		$dataJ->total_marks_obtained			= 0;
		$dataJ->did_not_attend					= 1;
		$dataJ->created 						= date('Y-m-d H:i:s');
		$dataJ->modified 						= date('Y-m-d H:i:s');
		
		//$this->prx($dataJ);
		
		$resultJ = $this->Judgeevaluations->save($dataJ);
		//$this->prx($resultJ);
		 
		
		$this->Flash->success('Evaluation marked as did not attend.');
		$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$convRegD->slug,$eventD->slug]);
		
		
    }
	
	
	public function markbreach($eventsubmission_slug = null) {
		
		$this->userLoginCheck();
		
		if (!$this->request->is('post')) {
			$this->Flash->error('Invalid request.');
			return $this->redirect($this->referer());
		}
		
        $user_id = $this->request->getSession()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		
		$checkExists = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $eventsubmission_slug])->contain(["Conventionregistrations","Conventions","Users","Events","Students","Uploadeduser"])->first();
		
		if($checkExists)
		{	 	
			$breach_reason = trim($this->request->getData('breach_reason'));
			
			// update event entry as guideline breach
			$this->Eventsubmissions->updateAll(['guideline_breach' => '1','breach_reason' => $breach_reason,'guideline_breach_by_judge_id' => $user_id,'modified' => date("Y-m-d H:i:s")], ["slug" => $eventsubmission_slug]);
			$this->Flash->success('Events entry successfully marked as guideline breach. Admin will review and approve/decline.');
			
			// now send email to admin & events team
			try {
			$settingsD	= $this->Settings->find()->where(['Settings.id' => 1])->first();
			$emailId = $settingsD->accounts_team_email;
						
			$emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '21'])->first();
			
			$judge_name = $userDetails->first_name.' '.$userDetails->last_name;
			
			if($checkExists->student_id >0)
			{
				$student_name_or_group_name = $checkExists->Students['first_name'].' '.$checkExists->Students['middle_name'].' '.$checkExists->Students['last_name'];
			}
			else
			{
				$student_name_or_group_name = $checkExists->group_name;
			}

			$toRepArray = array('[!judge_name!]','[!convention_name!]','[!season_year!]','[!school_name!]','[!customer_code!]','[!event_name!]','[!event_id_number!]','[!student_name_or_group_name!]');
			$fromRepArray = array($judge_name,$checkExists->Conventions['name'],$checkExists->season_year,$checkExists->Users['first_name'],$checkExists->Users['customer_code'],$checkExists->Events['event_name'],$checkExists->Events['event_id_number'],$student_name_or_group_name);

			$subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
			$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
			
			//echo $messageToSend; exit;
			
			$email = new Mailer();
			$email->viewBuilder()->setTemplate('default')->setLayout('admintemplate');
				$email->setEmailFormat('html')
				->setTo($emailId)
				->setCc(ACCOUNTS_TEAM_ANOTHER_EMAIL)
				->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
				->setSubject($subjectToSend)
				->setViewVars(['content_for_layout' => $messageToSend])
				->send();
				
			$email = new Mailer();
			$email->viewBuilder()->setTemplate('default')->setLayout('admintemplate');
				$email->setEmailFormat('html')
				->setTo('voizacinc@gmail.com')
				->setFrom([HEADERS_FROM_EMAIL => HEADERS_FROM_NAME])
				->setSubject($subjectToSend)
				->setViewVars(['content_for_layout' => $messageToSend])
				->send();
			} catch (\Exception $e) {
				// Email failure should not block the breach from being recorded
			}
			
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$checkExists->Conventionregistrations['slug'],$checkExists->Events['slug']]);
			 
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
    }
	
	public function markcommand($eventsubmission_slug = null) {
		
		$this->userLoginCheck();
		
        $user_id = $this->request->getSession()->read("user_id");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
		
		$checkExists = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $eventsubmission_slug])->contain(["Conventionregistrations","Conventions","Users","Events","Students","Uploadeduser"])->first();
		
		if($checkExists)
		{	
			// update event entry as guideline breach
			$this->Eventsubmissions->updateAll(['command_performance' => '1','mark_command_by_judge_id' => $user_id,'modified' => date("Y-m-d H:i:s")], ["slug" => $eventsubmission_slug]);
			$this->Flash->success('Events entry successfully marked as command performance.');
			
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'judgeevententries',$checkExists->Conventionregistrations['slug'],$checkExists->Events['slug']]);
		}
		else
		{
			$this->Flash->error('Invalid event entry.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
    }
	
	public function evaluationslist($event_submission_slug=null) {

        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Submission Evaluation" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('event_submission_slug',$event_submission_slug);
		
		$this->set('active_cr_eventsubmission','active');
		
        $msgString = '';

		$user_id = $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
        $condition = array();
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Eventsubmissions.conventionregistration_id = '".$this->request->getSession()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$submissionD = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $event_submission_slug])->contain(['Events'])->first();
        $this->set('submissionD', $submissionD);
		
		$judgeevaluations = $this->Judgeevaluations->find()->where(["Judgeevaluations.eventsubmission_id" => $submissionD->id])->contain(['Eventsubmissions','Conventionregistrations','Conventions','Events','Students','Judge','Judgeevaluationmarks'])->order(['Judgeevaluations.id' => 'DESC'])->all();
		$this->set('judgeevaluations',$judgeevaluations);
		//$this->prx($judgeevaluations);
    }
	
	public function viewevaluationdetails($event_submission_slug=null,$evaulation_slug=null) {

        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Submission Evaluation" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('home');
        
		$this->set('event_submission_slug',$event_submission_slug);
		$this->set('evaulation_slug',$evaulation_slug);
		
		$this->set('active_cr_eventsubmission','active');
		
        $msgString = '';

		$user_id = $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
        $condition = array();
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Eventsubmissions.conventionregistration_id = '".$this->request->getSession()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$submissionD = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $event_submission_slug])->contain(['Events'])->first();
        $this->set('submissionD', $submissionD);
		
		$evaluationD = $this->Judgeevaluations->find()->where(["Judgeevaluations.slug" => $evaulation_slug])->contain(['Eventsubmissions','Conventionregistrations','Conventions','Events','Students','Judge','Judgeevaluationmarks'])->first();
		$this->set('evaluationD',$evaluationD);
		//$this->prx($evaluationD);
    }
	
	public function evaluationslistprint($event_submission_slug=null) {

        $this->schoolAdminLoginCheck();
		
        $this->set("title_for_layout", "Print Judge(s) Evaluations" . TITLE_FOR_PAGES);
        $this->viewBuilder()->setLayout('print_reports');
        
		$this->set('event_submission_slug',$event_submission_slug);
		
		$this->set('active_cr_eventsubmission','active');
		
        $msgString = '';

		$user_id = $this->request->getSession()->read("user_id");
		$user_type 	= $this->request->getSession()->read("user_type");
		$userDetails = $this->Users->find()->where(['Users.id' => $user_id])->first();
        $this->set('userDetails', $userDetails);
		
        $condition = array();
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$condition[] = "(Eventsubmissions.conventionregistration_id = '".$this->request->getSession()->read("sess_selected_convention_registration_id")."')";
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'users', 'action' => 'dashboard']);
		}
		
		$submissionD = $this->Eventsubmissions->find()->where(['Eventsubmissions.slug' => $event_submission_slug])->contain(['Events','Students'])->first();
        $this->set('submissionD', $submissionD);
		
		$judgeevaluations = $this->Judgeevaluations->find()->where(["Judgeevaluations.eventsubmission_id" => $submissionD->id])->contain(['Eventsubmissions','Conventionregistrations','Conventions','Events','Students','Judge','Judgeevaluationmarks'])->all();
		$this->set('judgeevaluations',$judgeevaluations);
		//$this->prx($judgeevaluations);
    }
	
	
	/* Individual result package */
	public function indrespackprint($conv_reg_student_slug=null)
	{
		$this->viewBuilder()->setLayout('print_reports');
		
		global $resultPositions;
		$this->set('resultPositions', $resultPositions);
		
		if($this->request->getSession()->read("sess_selected_convention_registration_id")>0)
		{
			$sess_selected_convention_registration_id = $this->request->getSession()->read("sess_selected_convention_registration_id");
		}
		else
		{
			$this->Flash->error('Please choose convention registration first.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
		}
		
		// to get convention registration student details
		$convRegStudentD = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.slug' => $conv_reg_student_slug])->contain(['Students','Users'])->first();
		$this->set('convRegStudentD', $convRegStudentD);
		
		// to get convention registration details
		$conventionRegD = $this->Conventionregistrations->find()->where(['Conventionregistrations.id' => $convRegStudentD->conventionregistration_id])->contain(['Conventions'])->first();
		$this->set('conventionRegD', $conventionRegD);
		
		//$this->prx($conventionRegD);
		
		// List all events of this student
		$student_event_ids = $convRegStudentD->event_ids;
		if(!empty($student_event_ids))
		{
			$condStEv = array();
			$condStEv[] = "(Events.id IN ($student_event_ids))";
			$eventsList = $this->Events->find()->where($condStEv)->order(['Events.event_name' => 'ASC'])->select(['id','event_name','event_id_number','group_event_yes_no','event_judging_type'])->all();
			$this->set('eventsList', $eventsList);
			//$this->prx($eventsList);
		}
		else
		{
			$this->Flash->error('Sorry, no event found for this student.');
			$this->redirect(['controller' => 'conventionregistrations', 'action' => 'students']);
		}
		
		/* File 2:: Participation certificate PDF */
		// to prepare an array to send for pdf generation
		$arrCertData = array();
		
		$arrCertData['convention_name'] = $conventionRegD->Conventions['name'];
		
		$arrCertData['student_name'] 	= $convRegStudentD->Students['first_name'];
		if(!empty($convRegStudentD->Students['middle_name']))
		{
			$arrCertData['student_name'] .= ' '.$convRegStudentD->Students['middle_name'];
		}
		if(!empty($convRegStudentD->Students['last_name']))
		{
			$arrCertData['student_name'] .= ' '.$convRegStudentD->Students['last_name'];
		}
		
		$arrCertData['school_name'] = $convRegStudentD->Users['first_name'];
		$arrCertData['season_year'] = $convRegStudentD->season_year;
		
		
		/* File 3: Place certificate */
		// Now we need to see if this student placed in any position of any of his event
		if($eventsList)
		{	
			$placeCertData = array();
			// Run through each event and check if any position secured
			foreach($eventsList as $eventrec)
			{
				// Now check results based on group or individual event
				if($eventrec->group_event_yes_no == 0)
				{
					// Fetch points and position - non group event
					$condPOS = array();
					$condPOS[] = "(Resultpositions.conventionregistration_id = '".$conventionRegD->id."' )";
					$condPOS[] = "(Resultpositions.event_id = '".$eventrec->id."' )";
					$condPOS[] = "(Resultpositions.student_id = '".$convRegStudentD->student_id."' )";
					
					$studentPosition 	= $this->Resultpositions->find()->where($condPOS)->first();
					if($studentPosition && $studentPosition->position>=1 && $studentPosition->position<=6)
					{
						$dataC = array(
							'event_name' => $eventrec->event_name,
							'position' => $studentPosition->position,
						);
						
						$placeCertData[] = $dataC;
					}
				}
				else
				{
					// Group event - Firstly check group of this user
					$condGrp = array();
					$condGrp[] = "(Crstudentevents.conventionregistration_id = '".$conventionRegD->id."' )";
					$condGrp[] = "(Crstudentevents.event_id = '".$eventrec->id."' )";
					$checkGroup = $this->Crstudentevents->find()->where($condGrp)->select(['group_name'])->first();
					if(!empty($checkGroup->group_name))
					{
						// Check position of this Group
						$condPOS = array();
						$condPOS[] = "(Resultpositions.conventionregistration_id = '".$conventionRegD->id."' )";
						$condPOS[] = "(Resultpositions.event_id = '".$eventrec->id."' )";
						$condPOS[] = "(Resultpositions.group_name = '".$checkGroup->group_name."' )";
						
						$studentPosition 	= $this->Resultpositions->find()->where($condPOS)->first();
						if($studentPosition && $studentPosition->position>=1 && $studentPosition->position<=6)
						{
							$dataC = array(
								'event_name' => $eventrec->event_name,
								'position' 	=> $studentPosition->position,
							);
							
							$placeCertData[] = $dataC;
						}
						
					}
				}
			}
		}
		
		$this->set('placeCertData', $placeCertData);
		
		/* File 4 :: evaluation form */
		// All data already set
		
		$this->set('arrCertData', $arrCertData);
		
	}
}

?>
