<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

class SchedulingsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
    public $components = array('PImage', 'PImageTest');

    //public $helpers = array('Javascript', 'Ajax');

    public function initialize() {
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
		$this->Events = $this->loadModel('Events');
		$this->Schedulingtimings = $this->loadModel('Schedulingtimings');
		$this->Crstudentevents = $this->loadModel('Crstudentevents');
    }

    public function precheck($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		//$this->prx($conventionSD);
		
		$this->set('conventionSD', $conventionSD);
		
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to check that if record for this conv season entered in scheduling table..
		// ... if not entered, then entered
		$checkSchedulingRecord = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		if(!$checkSchedulingRecord)
		{
			// enter new record
			$schedulings = $this->Schedulings->newEntity();
			$dataSch = $this->Schedulings->patchEntity($schedulings, array());

			$dataSch->slug 						= "scheduling-conv-season-".$conventionSD->id.'-'.time();
			$dataSch->conventionseasons_id		= $conventionSD->id;
			$dataSch->convention_id				= $conventionSD->convention_id;
			$dataSch->season_id					= $conventionSD->season_id;
			$dataSch->season_year 				= $conventionSD->season_year;
			
			$dataSch->created 					= date('Y-m-d H:i:s');

			$resultSch = $this->Schedulings->save($dataSch);
		}
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
    }
	
	public function precheckevents($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		
		// to check events for this convention season
		$cntrPreCheckEvents = 0;
		$conventionSEventsList = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(['Events'])->all();
		foreach($conventionSEventsList as $convevPreCheck)
		{
			if($convevPreCheck->Events['needs_schedule'] == 1)
			{
				$cntrPreCheckEvents++;
			}
		}
		
		
		//$this->prx($conventionSEvents);
		if($cntrPreCheckEvents>0)
		{
			// now update this precheck events in scheduling table
			$this->Schedulings->updateAll(['precheck_events' => 1,'total_events_found' => $cntrPreCheckEvents,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total event found: '.$cntrPreCheckEvents);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_events' => 0,'total_events_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no event found for this convention season.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function prechecklocations($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		// to check location/rooms for this convention
		$conventionRoomsTotal = $this->Conventionrooms->find()->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])->count();
		if($conventionRoomsTotal>0)
		{
			// to check events for this convention season
			$cntrConvSeasonTotalEvents = 0;
			$conventionSEventsList = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(['Events'])->all();
			foreach($conventionSEventsList as $convEv)
			{
				if($convEv->Events['needs_schedule'] == 1)
				{
					$cntrConvSeasonTotalEvents++;
				}
			}
			
			$roomEventsArr = array();
			
			// now get events that is assigned to a room
			$convRoomEvents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->all();
			foreach($convRoomEvents as $convroomev)
			{
				$roomEventIDSExplode = explode(",",$convroomev->event_ids);
				foreach($roomEventIDSExplode as $eventidexplode)
				{
					if(!in_array($eventidexplode,(array)$roomEventsArr))
					{
						$roomEventsArr[] = $eventidexplode;
					}
				}
			}
			
			if(count((array)$roomEventsArr) < $cntrConvSeasonTotalEvents)
			{
				$this->Flash->error('Sorry, '.($cntrConvSeasonTotalEvents-count((array)$roomEventsArr)).' event(s) not assigned to any room. Please assign.');
				
				$this->Schedulings->updateAll(['precheck_locations' => 0,'total_locations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			}
			else
			{
				$this->Schedulings->updateAll(['precheck_locations' => 1,'total_locations_found' => $conventionRoomsTotal,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
				$this->Flash->success('Total locations found: '.$conventionRoomsTotal);
			}
		}
		else
		{
			$this->Flash->error('Sorry no location found for this convention.');
		}
		
		
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckregistrations($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		// to check convention registrations
		$conventionRegCount = $this->Conventionregistrations->find()->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])->count();
		if($conventionRegCount>0)
		{
			$this->Schedulings->updateAll(['precheck_registrations' => 1,'total_registrations_found' => $conventionRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total registrations found: '.$conventionRegCount);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_registrations' => 0,'total_registrations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no registration found for this convention.');
		}
		
		
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckstudents($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		// to check convention registrations
		$studentsRegCount = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,'Conventionregistrationstudents.season_id' => $conventionSD->season_id,'Conventionregistrationstudents.season_year' => $conventionSD->season_year])->count();
		if($studentsRegCount>0)
		{
			$this->Schedulings->updateAll(['precheck_students' => 1,'total_students_found' => $studentsRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Total students found: '.$studentsRegCount);
		}
		else
		{
			$this->Schedulings->updateAll(['precheck_students' => 0,'total_students_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->error('Sorry no stuednts found for this convention.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function precheckall($convention_season_slug=null) {

		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();

		// --- Events ---
		$cntrPreCheckEvents = 0;
		$conventionSEventsList = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id])->contain(['Events'])->all();
		foreach($conventionSEventsList as $convevPreCheck) {
			if($convevPreCheck->Events['needs_schedule'] == 1) { $cntrPreCheckEvents++; }
		}
		if($cntrPreCheckEvents > 0) {
			$this->Schedulings->updateAll(['precheck_events' => 1,'total_events_found' => $cntrPreCheckEvents,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		} else {
			$this->Schedulings->updateAll(['precheck_events' => 0,'total_events_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		}

		// --- Locations ---
		$conventionRoomsTotal = $this->Conventionrooms->find()->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])->count();
		$cntrConvSeasonTotalEvents = $cntrPreCheckEvents;
		$roomEventsArr = [];
		$convRoomEvents = $this->Conventionseasonroomevents->find()->where(['Conventionseasonroomevents.conventionseasons_id' => $conventionSD->id])->all();
		foreach($convRoomEvents as $convroomev) {
			foreach(explode(",", $convroomev->event_ids) as $eid) {
				if(!in_array($eid, $roomEventsArr)) { $roomEventsArr[] = $eid; }
			}
		}
		if($conventionRoomsTotal > 0 && count($roomEventsArr) >= $cntrConvSeasonTotalEvents) {
			$this->Schedulings->updateAll(['precheck_locations' => 1,'total_locations_found' => $conventionRoomsTotal,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		} else {
			$this->Schedulings->updateAll(['precheck_locations' => 0,'total_locations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		}

		// --- Registrations ---
		$conventionRegCount = $this->Conventionregistrations->find()->where(['Conventionregistrations.conventionseason_id' => $conventionSD->id])->count();
		if($conventionRegCount > 0) {
			$this->Schedulings->updateAll(['precheck_registrations' => 1,'total_registrations_found' => $conventionRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		} else {
			$this->Schedulings->updateAll(['precheck_registrations' => 0,'total_registrations_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		}

		// --- Students ---
		$studentsRegCount = $this->Conventionregistrationstudents->find()->where(['Conventionregistrationstudents.convention_id' => $conventionSD->convention_id,'Conventionregistrationstudents.season_id' => $conventionSD->season_id,'Conventionregistrationstudents.season_year' => $conventionSD->season_year])->count();
		if($studentsRegCount > 0) {
			$this->Schedulings->updateAll(['precheck_students' => 1,'total_students_found' => $studentsRegCount,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		} else {
			$this->Schedulings->updateAll(['precheck_students' => 0,'total_students_found' => NULL,'modified' => date('Y-m-d H:i:s')], ["conventionseasons_id" => $conventionSD->id]);
		}

		$this->Flash->success('Pre-check complete. Events: '.$cntrPreCheckEvents.', Locations: '.$conventionRoomsTotal.', Registrations: '.$conventionRegCount.', Students: '.$studentsRegCount.'.');
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
	}

	public function resetallprecheck($convention_season_slug=null) {
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		//$this->prx($conventionSEvents);
		if($conventionSD)
		{
			// now reset all precheck
			$this->Schedulings->updateAll(
			[
			'precheck_events' => 0,'total_events_found' => NULL,
			'precheck_locations' => 0,'total_locations_found' => NULL,
			'precheck_registrations' => 0,'total_registrations_found' => NULL,
			'precheck_students' => 0,'total_students_found' => NULL,
			'modified' => date('Y-m-d H:i:s')
			], 
			["conventionseasons_id" => $conventionSD->id]);
			
			$this->Flash->success('Reset all pre-check prcessed successfully.');
		}
		else
		{	
			$this->Flash->error('Invalid convention season.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck',$convention_season_slug]);
    }
	
	public function wizard($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		global $weekDays;
		$this->set('weekDays', $weekDays);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		$schedulings = $this->Schedulings->get($schedulingD->id);
        if ($this->request->is(['post', 'put'])) {
            $data = $this->Schedulings->patchEntity($schedulings, $this->request->getData());
			
            if (count($data->getErrors()) == 0) {
				$data->modified = date("Y-m-d");
                
				//$this->prx($data);
				
				$data->start_date = date("Y-m-d",strtotime($data->start_date));
				$actualFirstDay = $this->getWeekDayFromDate($data->start_date);
				if ((int)$data->number_of_days < 1) {
					$this->Flash->error('Number of Days must be at least 1.');
					$this->set('schedulings', $schedulings);
					return;
				}
				if ($actualFirstDay !== $data->first_day) {
					$this->Flash->error('Start Date falls on '.$actualFirstDay.', but First Day is set to '.$data->first_day.'. Please align them before saving the wizard.');
					$this->set('schedulings', $schedulings);
					return;
				}
				$allowedConventionDays = $this->getConventionWeekDays($data->first_day, $data->number_of_days);
				
				/* $time = '9:50 PM';
				$timestamp = strtotime($time);
				$mysqlFormat = date('Y-m-d H:i:s', $timestamp);

				echo $mysqlFormat;exit; */
				
				$data->normal_starting_time 	= $this->changeToMysqlTimeFormat($data->normal_starting_time);
				$data->normal_finish_time 		= $this->changeToMysqlTimeFormat($data->normal_finish_time);
				$data->lunch_time_start 		= $this->changeToMysqlTimeFormat($data->lunch_time_start);
				$data->lunch_time_end 			= $this->changeToMysqlTimeFormat($data->lunch_time_end);
				
				if($data->starting_different_time_first_day_yes_no)
				{
					$data->different_first_day_start_time 			= $this->changeToMysqlTimeFormat($data->different_first_day_start_time);
					$data->different_first_day_end_time 			= $this->changeToMysqlTimeFormat($data->different_first_day_end_time);
				}
				else
				{
					$data->different_first_day_start_time 			= NULL;
					$data->different_first_day_end_time 			= NULL;
				}
				
				if($data->judging_breaks_yes_no)
				{
					$data->judging_breaks_morning_break_starting_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_morning_break_starting_time);
					$data->judging_breaks_morning_break_finish_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_morning_break_finish_time);
					$data->judging_breaks_afternoon_break_start_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_afternoon_break_start_time);
					$data->judging_breaks_afternoon_break_finish_time 			= $this->changeToMysqlTimeFormat($data->judging_breaks_afternoon_break_finish_time);
				}
				else
				{
					$data->judging_breaks_morning_break_starting_time 			= NULL;
					$data->judging_breaks_morning_break_finish_time 			= NULL;
					$data->judging_breaks_afternoon_break_start_time 			= NULL;
					$data->judging_breaks_afternoon_break_finish_time 			= NULL;
				}
				
				if($data->sports_day_yes_no)
				{
					if (!in_array($data->sports_day, $allowedConventionDays, true)) {
						$this->Flash->error('Sports Day must fall within the configured convention days: '.implode(', ', $allowedConventionDays).'.');
						$this->set('schedulings', $schedulings);
						return;
					}
					$data->sports_day_starting_time 				= $this->changeToMysqlTimeFormat($data->sports_day_starting_time);
					$data->sports_day_finish_time 					= $this->changeToMysqlTimeFormat($data->sports_day_finish_time);
				}
				else
				{
					$data->sports_day 								= NULL;
					$data->sports_day_starting_time 				= NULL;
					$data->sports_day_finish_time 					= NULL;
				}
				
				if($data->sports_day_having_events_after_sport_yes_no)
				{
					$data->sports_day_other_starting_time 			= $this->changeToMysqlTimeFormat($data->sports_day_other_starting_time);
					$data->sports_day_other_finish_time 			= $this->changeToMysqlTimeFormat($data->sports_day_other_finish_time);
				}
				else
				{
					$data->sports_day_other_starting_time 				= NULL;
					$data->sports_day_other_finish_time 				= NULL;
				}
				
				//$this->prx($data);
				
				if ($this->Schedulings->save($data)) {
                    $this->Flash->success('Data saved successfully.');
                    $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('schedulings', $schedulings);
		
    }
	
	public function schedulecategory($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Schedule category');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		$this->set('conventionSD', $conventionSD);
		
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		/* Category :: 1 */
		// group_event = yes || event_kind_id = Sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventsC1 = array();
		$condC1 = array();
		$condC1[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC1 = $this->Conventionseasonevents->find()->where($condC1)->all();
		foreach($eventsC1 as $eventc1)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc1->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '1' && $eventD->event_kind_id == 'Sequential' && $eventD->has_to_be_consecutive == '1')
			{
				$arrEventsC1[] = $eventc1->event_id;
			}
		}
		$this->set('arrEventsC1', $arrEventsC1);
		
		
		
		
		
		/* Category :: 2 */
		// group_event = no || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC2 = array();
		$condC2 = array();
		$condC2[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC2 = $this->Conventionseasonevents->find()->where($condC2)->all();
		foreach($eventsC2 as $eventc2)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc2->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '0' && $eventD->event_kind_id == 'Elimination' && $eventD->has_to_be_consecutive == '0')
			{
				$arrEventsC2[] = $eventc2->event_id;
			}
		}
		$this->set('arrEventsC2', $arrEventsC2);
		//$this->prx($arrEventsC2);
		
		
		/* Category :: 3 - this is similar to category 2 */
		// group_event = yes || event_kind_id = Elimination || needs_schedule = 1 || has_to_be_consecutive = no
		$arrEventsC3 = array();
		$condC3 = array();
		$condC3[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC3 = $this->Conventionseasonevents->find()->where($condC3)->all();
		foreach($eventsC3 as $eventc3)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc3->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '1' && $eventD->event_kind_id == 'Elimination' && $eventD->has_to_be_consecutive == '0')
			{
				$arrEventsC3[] = $eventc3->event_id;
			}
		}
		$this->set('arrEventsC3', $arrEventsC3);
		//$this->prx($arrEventsC3);
		
		
		/* Category :: 4 - this is similar to category 1 */
		// group_event = no || event_kind_id = Sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventsC4 = array();
		$condC4 = array();
		$condC4[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		
		$eventsC4 = $this->Conventionseasonevents->find()->where($condC4)->all();
		foreach($eventsC4 as $eventc4)
		{
			$eventD = $this->Events->find()->where(['Events.id' => $eventc4->event_id])->first();
			if($eventD->needs_schedule == '1' && $eventD->group_event_yes_no == '0' && $eventD->event_kind_id == 'Sequential' && $eventD->has_to_be_consecutive == '1')
			{
				$arrEventsC4[] = $eventc4->event_id;
			}
		}
		$this->set('arrEventsC4', $arrEventsC4);
		//$this->prx($arrEventsC4);
		
		
    }
	
	
	public function reports($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
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
		
    }
	
	public function overwritetimings($convention_season_slug=null) {
        $this->set('title', ADMIN_TITLE . 'Overwrite Timings');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$this->set('conventionSD', $conventionSD);
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		global $weekDays;
		$this->set('weekDays', $weekDays);
		
		// to fetch scheduling data and send to template
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		$this->set('schedulingD', $schedulingD);
		
		// Nathan provided these 3 events for Overwrite
		/* Spelling U16 - 003--3   Spelling OPEN - 053--11   Bible Memory OPEN - 1056--343 */
		$eventIDArr = array(343,11,3);
		
		// Now check if these events are chosen for this convention season
		
		$finalEventArr = array();
		
		foreach($eventIDArr as $event_id)
		{
			$checkEventCS = $this->Conventionseasonevents->find()->where(['Conventionseasonevents.conventionseasons_id' => $conventionSD->id,'Conventionseasonevents.event_id' => $event_id])->contain(["Events"])->first();
			if($checkEventCS)
			{
				// Now we need to show number of students in each event to show in dropdown
				$countStudentsEvent = $this->Crstudentevents
										->find()
										->where([
											'Crstudentevents.conventionseason_id' => $conventionSD->id,
											'Crstudentevents.event_id' => $event_id
										])
										->count();
				
				$finalEventArr[$event_id] = $checkEventCS->Events['event_name'].' ('.$checkEventCS->Events['event_id_number'].')'.' ('.$countStudentsEvent.')';
			}
		}
		$this->set('finalEventArr', $finalEventArr);
		
		
		if ($this->request->is(['post']))
		{
			$formData = (array)$this->request->getData('Schedulings');
			if (empty($formData)) {
				$formData = (array)$this->request->getData('schedulings');
			}

			$event_id = $formData['event_id'] ?? $this->request->getData('event_id');
			$overwrite_date = $formData['overwrite_date'] ?? $this->request->getData('overwrite_date');
			$overwrite_time = $formData['overwrite_time'] ?? $this->request->getData('overwrite_time');
			$max_students = $formData['max_students'] ?? $this->request->getData('max_students');

			if (empty($event_id) || empty($overwrite_date) || empty($overwrite_time) || empty($max_students) || (int)$max_students < 1) {
				$this->Flash->error('Please choose event/date/time and enter valid max students.');
				return $this->redirect(['controller' => 'schedulings', 'action' => 'overwritetimings', $convention_season_slug]);
			}
			
			
			
			
			// now get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
			if (empty($eventD)) {
				$this->Flash->error('Selected event not found for overwrite.');
				return $this->redirect(['controller' => 'schedulings', 'action' => 'overwritetimings', $convention_season_slug]);
			}
			
			// Now calculate start time and end time based on event data
			$start_date 		= date("Y-m-d",strtotime($overwrite_date));
			$start_time 		= date("H:i:s",strtotime($overwrite_time));
			$setupTime = (int)($eventD->setup_time ?? 0);
			$roundTime = (int)($eventD->round_time ?? 0);
			$judgingTime = (int)($eventD->judging_time ?? 0);
			$slotDuration = $setupTime + $roundTime + $judgingTime;
			$bufferBetweenBlocks = 5;
			
			/* echo $start_date;
			echo '<hr>';
			echo $start_time;
			echo '<hr>';
			echo $finish_time; */
			
			
			// Now fetch students of this event for this convention season
			$cntrTotRec = 0;
			$schedulingtimings = $this->Schedulingtimings->find()->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.event_id' => $event_id])->order(["Schedulingtimings.id"=>"ASC"])->all()->toList();
			$timingBlocks = array_chunk($schedulingtimings, (int)$max_students);

			foreach($timingBlocks as $timingBlock)
			{
				$finish_time = date("H:i:s", strtotime('+ '.$slotDuration.' minutes', strtotime($start_time)));

				foreach($timingBlock as $schrecord)
				{
					$this->Schedulingtimings->updateAll(
					[
						'sch_date_time' 	=> $start_date.' '.$start_time,
						'day' 				=> date("l",strtotime($start_date)),
						'start_time' 		=> $start_time,
						'finish_time' 		=> $finish_time,
						'modified' 			=> date("Y-m-d H:i:s"),
					]
					,
					[
						"id" => $schrecord->id
					]
					);

					$cntrTotRec++;
				}

				$start_time = date("H:i:s", strtotime('+ '.$bufferBetweenBlocks.' minutes', strtotime($finish_time)));
			}
			
			if($cntrTotRec>0)
			{
				$this->Flash->success('Scheduling date/time overwrite successfully. Total '.$cntrTotRec.' record(s) modified.');
			}
			else
			{
				$this->Flash->error('Sorry, no record updated.');
			}
			
			return $this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
			
			
        }
		
    }
	
	public function resolveconflicts($convention_season_slug=null) {
		
		// First we need to collect all students list of all schools
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		
		if(!empty($schedulingD->conflict_user_ids))
		{
			$userIDSConflict = explode(",",$schedulingD->conflict_user_ids);
			shuffle($userIDSConflict);
			
			/////////////////
			$userId = $userIDSConflict[0];
			
			//echo $userId;exit;

			$resolveConflicts = false;
			do {
				$userConflictRecords = $this->userConflictRecordsByUserId($convention_season_slug, $userId);
				
				if (empty($userConflictRecords))
				{
					// No conflict found, then remove this from Conflict
					$nextUserIDSConflicts = array_filter($userIDSConflict, function($item) use ($userId) {
						return $item !== $userId;
					});
					
					// Now update record
					if(count($nextUserIDSConflicts))
					{
						$this->Schedulings->updateAll(
						[
							'conflict_user_ids'		=> implode(",",$nextUserIDSConflicts)
						]
						,
						[
							"id" => $schedulingD->id
						]);
					}
					else
					{
						$this->Schedulings->updateAll(
						[
							'conflict_user_ids'		=> NULL
						]
						,
						[
							"id" => $schedulingD->id
						]);
					}
				}
				
				//$this->prx($userConflictRecords);
				foreach ($userConflictRecords as $userConflictRecord)
				{
					$base_start_time		= date("H:i:s",strtotime($userConflictRecord['start_time']));
					$base_finish_time		= date("H:i:s",strtotime($userConflictRecord['finish_time']));
					$base_sch_date_time 	= date("Y-m-d H:i:s",strtotime($userConflictRecord['sch_date_time']));
					
					foreach($userConflictRecord['conflicts'] as $conflict)
					{
						$resolveConflict	= $this->nextBookings($convention_season_slug,$conflict, $base_start_time, $base_finish_time, $base_sch_date_time,$recordId);

						$recordId			= $resolveConflict['id'];
						$start_time			= $resolveConflict['start_time'];
						$finish_time		= $resolveConflict['finish_time'];
						$sch_date_time		= $resolveConflict['sch_date_time'];

						/* $sqlExist = "UPDATE schedulingtimings
							SET start_time = '$start_time', finish_time = '$finish_time', sch_date_time = '$sch_date_time'
							WHERE id  = $recordId
							";
						$stmt = $pdo->query($sqlExist); */
						
						///////////////
						$this->Schedulingtimings->updateAll(
						[
							'start_time'		=> $start_time,
							'finish_time'		=> $finish_time,
							'sch_date_time'		=> $sch_date_time
						]
						,
						[
							"id" => $recordId
						]);
						
						// remove user id from database because conflict resolved
						$nextUserIDSConflicts = array_filter($userIDSConflict, function($item) use ($userId) {
							return $item !== $userId;
						});
						
						// Now update record
						if(count($nextUserIDSConflicts))
						{
							$this->Schedulings->updateAll(
							[
								'conflict_user_ids'		=> implode(",",$nextUserIDSConflicts)
							]
							,
							[
								"id" => $schedulingD->id
							]);
						}
						else
						{
							$this->Schedulings->updateAll(
							[
								'conflict_user_ids'		=> NULL
							]
							,
							[
								"id" => $schedulingD->id
							]);
						}
						
						///////////////

						$base_start_time	= $start_time;
						$base_finish_time	= $finish_time;
						$base_sch_date_time	= $sch_date_time;
					}
				}

				$userConflictRecords	= $this->userConflictRecordsByUserId($convention_season_slug, $userId);
				$resolveConflicts		= !empty($userConflictRecords);

			} while ($resolveConflicts);

			$this->Flash->success('Conflict resolved successfully.');
			/////////////////
			
		}
		else
		{
			// no conflict found
			//$this->Flash->error('Sorry, no conflict found.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug]);
	}
	
	public function resolveconflictsgroup($convention_season_slug=null) {
		
		// get convention season details
		$conventionSD = $this->Conventionseasons->find()->where(['Conventionseasons.slug' => $convention_season_slug])->contain(["Conventions"])->first();
		
		$schedulingD = $this->Schedulings->find()->where(['Schedulings.conventionseasons_id' => $conventionSD->id])->first();
		
		if(!empty($schedulingD->conflict_user_ids_group))
		{
			$schIDSConflict = explode(",",$schedulingD->conflict_user_ids_group);
			shuffle($schIDSConflict);
			
			/////////////////
			$schedulingId = $schIDSConflict[0];
			
			$schedulingTimingsD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $schedulingId])->first();
			
			// Record no longer exists — purge the stale conflict ID and move on
			if (!$schedulingTimingsD) {
				$nextSchIDSConflict = array_values(array_diff($schIDSConflict, [$schedulingId]));
				$this->Schedulings->updateAll(
					['conflict_user_ids_group' => count($nextSchIDSConflict) ? implode(',', $nextSchIDSConflict) : null],
					['id' => $schedulingD->id]
				);
				return $this->redirect(['controller' => 'schedulings', 'action' => 'resolveconflictsgroup', $convention_season_slug]);
			}

			//$scheduling = findSchedulingTiming($pdo, $schedulingId);
			$groupUserIds = array_filter(explode(',', (string)$schedulingTimingsD->group_name_user_ids), 'strlen');
			$opponentIds  = array_filter(explode(',', (string)$schedulingTimingsD->group_name_opponent_user_ids), 'strlen');

			$allUserIds = array_values(array_merge($groupUserIds, $opponentIds));

			$base_start_time = $schedulingTimingsD->start_time;
			$base_finish_time = $schedulingTimingsD->finish_time;
			$base_sch_date_time = $schedulingTimingsD->sch_date_time;

			$resolveConflict = $this->findNextTime($schedulingTimingsD, $base_start_time, $base_finish_time, $base_sch_date_time, $allUserIds);
			//$this->prx($resolveConflict);

			$recordId 			= $resolveConflict->id;
			$start_time 		= $resolveConflict->start_time;
			$finish_time 		= $resolveConflict->finish_time;
			$sch_date_time 		= $resolveConflict->sch_date_time;
			
			$arrP = [
					'start_time'		=> $start_time,
					'finish_time'		=> $finish_time,
					'sch_date_time'		=> $sch_date_time
				];
				
			//$this->prx($recordId);	
			
			$this->Schedulingtimings->updateAll(
				[
					'start_time'		=> $start_time,
					'finish_time'		=> $finish_time,
					'sch_date_time'		=> $sch_date_time
				]
				,
				[
					"id" => $recordId
				]);
				
			// remove id from database because conflict resolved
			/* $nextSchIDSConflict = array_filter($schIDSConflict, function($item) use ($recordId) {
				return $item !== $recordId;
			}); */
			
			$nextSchIDSConflict = array_values(array_diff($schIDSConflict, [$recordId]));
						
			// Now update record
			if(count($nextSchIDSConflict))
			{
				$this->Schedulings->updateAll(
				[
					'conflict_user_ids_group'		=> implode(",",$nextSchIDSConflict)
				]
				,
				[
					"id" => $schedulingD->id
				]);
			}
			else
			{
				$this->Schedulings->updateAll(
				[
					'conflict_user_ids_group'		=> NULL
				]
				,
				[
					"id" => $schedulingD->id
				]);
			}
			
			
		}
		else
		{
			//$this->Flash->error('Sorry, no conflict found.');
		}
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'precheck', $convention_season_slug]);
		
	}
	
	public function editschedulingtimings($convention_season_slug=null,$sch_auto_id=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Wizard');
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
		
		// to fetch scheduling timings data and send to template
		$schedulingtimingsD = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $sch_auto_id])->contain(['Events','Conventionrooms'])->first();
		$this->set('schedulingtimingsD', $schedulingtimingsD);
		
        if ($this->request->is(['post', 'put'])) {
            
			//$this->prx($this->request->getData('Schedulingtimings'));
			
			$data = $this->request->getData('Schedulingtimings');
			
			
			
			$new_start_time 			= $this->changeToMysqlTimeFormat($data['new_start_time']);
			$new_finish_time 			= $this->changeToMysqlTimeFormat($data['new_finish_time']);
			
			echo $new_start_time;
			echo '<br>';
			echo $new_finish_time;
			
			echo '<hr>';
			
			echo date("H:i:s", strtotime($schedulingtimingsD->start_time));
			echo '<br>';
			echo date("H:i:s", strtotime($schedulingtimingsD->finish_time));
			
			$flagUpdate = 1;
			
			// 1. If save start and finish time not entered
			if($new_start_time == date("H:i:s", strtotime($schedulingtimingsD->start_time))  && $new_finish_time == date("H:i:s", strtotime($schedulingtimingsD->finish_time)))
			{
				$flagUpdate = 0;
				$msgEdit = 'You entered same start and finish timings.';
			}
			else
			{
				// 2. Need to check that if room is free or not for this start and end time
				$condRoomC = array();
				$condRoomC[] =  "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."')";
				$condRoomC[] =  "(Schedulingtimings.room_id = '".$schedulingtimingsD->room_id."')";
				$condRoomC[] =  "(Schedulingtimings.day = '".$schedulingtimingsD->day."')";
				$condRoomC[] =  "('".$new_start_time."' < Schedulingtimings.finish_time AND '".$new_finish_time."' > Schedulingtimings.start_time)";
				
				$checkRoomBusy = $this->Schedulingtimings->find()
					->where($condRoomC)
					->first();
				if($checkRoomBusy)
				{
					$flagUpdate = 0;
					$msgEdit = 'Sorry, room is not free on selected timings.';
				}
				else
				{
					/* To check if there is judging breaks */
					if($schedulingD->judging_breaks_yes_no == 1)
					{
						// 1. Morning break timings - Apply check for 
						$judging_breaks_morning_break_starting_time = date("H:i:s",strtotime($schedulingD->judging_breaks_morning_break_starting_time));
						$judging_breaks_morning_break_finish_time 	= date("H:i:s",strtotime($schedulingD->judging_breaks_morning_break_finish_time));
						
						if( (strtotime($new_start_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($new_start_time)<=strtotime($judging_breaks_morning_break_finish_time)) || 
						(strtotime($new_finish_time)>=strtotime($judging_breaks_morning_break_starting_time) &&  strtotime($new_finish_time)<=strtotime($judging_breaks_morning_break_finish_time)))
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, time conflict in judges morning breaks.';
						}
						
						
						// 2. Afternoon break timings - Apply check for 
						$judging_breaks_afternoon_break_start_time = date("H:i:s",strtotime($schedulingD->judging_breaks_afternoon_break_start_time));
						$judging_breaks_afternoon_break_finish_time 	= date("H:i:s",strtotime($schedulingD->judging_breaks_afternoon_break_finish_time));
						
						if( (strtotime($new_start_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($new_start_time)<=strtotime($judging_breaks_afternoon_break_finish_time)) || 
						(strtotime($new_finish_time)>=strtotime($judging_breaks_afternoon_break_start_time) &&  strtotime($new_finish_time)<=strtotime($judging_breaks_afternoon_break_finish_time)))
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, time conflict in judges afternoon breaks.';
						}
						
						
						/* To check here if sports day is there, then exclude that time - starts */
						if($schedulingD->sports_day_yes_no == 1)
						{
							$sports_day					= $schedulingD->sports_day;
							$sports_day_starting_time	= date("H:i:s",strtotime($schedulingD->sports_day_starting_time));
							$sports_day_finish_time		= date("H:i:s",strtotime($schedulingD->sports_day_finish_time));
							
							// to check if day match
							if($sports_day == $schedulingtimingsD->day)
							{
								// Now check TIMINGS
								if( (strtotime($new_start_time)>=strtotime($sports_day_starting_time) &&  strtotime($new_start_time)<=strtotime($sports_day_finish_time)) || 
								(strtotime($new_finish_time)>=strtotime($sports_day_starting_time) &&  strtotime($new_finish_time)<=strtotime($sports_day_finish_time)))
								{
									$flagUpdate = 0;
									$msgEdit = 'Sorry, time conflict in sports day timings.';
								}
							}
						}
						
						/* To check here if they are having more events after sport - starts */
						if($schedulingD->sports_day_having_events_after_sport_yes_no == 1)
						{
							$sports_day							= $schedulingD->sports_day;
							$sports_day_other_starting_time		= date("H:i:s",strtotime($schedulingD->sports_day_other_starting_time));
							$sports_day_other_finish_time		= date("H:i:s",strtotime($schedulingD->sports_day_other_finish_time));
							
							// to check if day match
							if($sports_day == $schedulingtimingsD->day)
							{
								// Now check TIMINGS
								if( (strtotime($new_start_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($new_start_time)<=strtotime($sports_day_other_finish_time)) || 
								(strtotime($new_finish_time)>=strtotime($sports_day_other_starting_time) &&  strtotime($new_finish_time)<=strtotime($sports_day_other_finish_time)))
								{
									$flagUpdate = 0;
									$msgEdit = 'Sorry, time conflict in events of sports day timings.';
								}
							}
						}
						
						/* To check if user_id is having any game */
						$userId = $schedulingtimingsD->user_id;
						$checkUIDBusy = $this->Schedulingtimings->find()
							->where([
								'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
								'Schedulingtimings.day' => $schedulingtimingsD->day,
							])
							->andWhere(function ($exp) use ($new_start_time, $new_finish_time) {
								return $exp->add(
									"'$new_start_time' < Schedulingtimings.finish_time 
									 AND '$new_finish_time' > Schedulingtimings.start_time"
								);
							})
							->andWhere(function ($exp) use ($userId) {
								return $exp->or_([
									'Schedulingtimings.user_id' => $userId,
									'Schedulingtimings.user_id_opponent' => $userId,
									$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_user_ids)"),
									$exp->add("FIND_IN_SET($userId, Schedulingtimings.group_name_opponent_user_ids)")
								]);
							})
							->count();
							
						if($checkUIDBusy > 0)
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, user is having any other game.';
						}
						
						
						
						
						/* To check if user_id_opponent is having any game */
						$userIdOpponent = $schedulingtimingsD->user_id_opponent;
						$checkUIDOppBusy = $this->Schedulingtimings->find()
							->where([
								'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
								'Schedulingtimings.day' => $schedulingtimingsD->day,
							])
							->andWhere(function ($exp) use ($new_start_time, $new_finish_time) {
								return $exp->add(
									"'$new_start_time' < Schedulingtimings.finish_time 
									 AND '$new_finish_time' > Schedulingtimings.start_time"
								);
							})
							->andWhere(function ($exp) use ($userIdOpponent) {
								return $exp->or_([
									'Schedulingtimings.user_id' => $userIdOpponent,
									'Schedulingtimings.user_id_opponent' => $userIdOpponent,
									$exp->add("FIND_IN_SET($userIdOpponent, Schedulingtimings.group_name_user_ids)"),
									$exp->add("FIND_IN_SET($userIdOpponent, Schedulingtimings.group_name_opponent_user_ids)")
								]);
							})
							->count();
						if($checkUIDOppBusy > 0)
						{
							$flagUpdate = 0;
							$msgEdit = 'Sorry, opponent user is having any other game.';
						}
						
					}
					
				}
			}
			
			//echo $msgEdit;exit;
			
			if($flagUpdate>0)
			{
				// Update
				$schStartDate = date("Y-m-d",strtotime($schedulingtimingsD->sch_date_time));
				$this->Schedulingtimings->updateAll(
				[
					'start_time' 	=> date("H:i:s", strtotime($new_start_time)),
					'finish_time' 	=> date("H:i:s", strtotime($new_finish_time)),
					'sch_date_time' => $schStartDate.' '.date("H:i:s", strtotime($new_start_time)),
					'modified' 		=> date("Y-m-d H:i:s")
				],
				["id" => $sch_auto_id]);
				
				$this->Flash->success('Time updated successfully.');
				
				$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory',$convention_season_slug]);
			}
			else
			{
				$this->Flash->error($msgEdit);
				
				$this->redirect(['controller' => 'schedulings', 'action' => 'editschedulingtimings', $convention_season_slug,$sch_auto_id]);
			}
			
			
        }
		
    }

}

?>
