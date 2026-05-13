<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;

use Cake\Datasource\ConnectionManager;

class SchedulingtimingsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Schedulings.name' => 'asc']];
    public $components = array('PImage');
private $scheduleWindowWarningShown = false;

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
		$this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
		$this->Events = $this->loadModel('Events');
		$this->Conventionregistrations = $this->loadModel('Conventionregistrations');
		$this->Crstudentevents = $this->loadModel('Crstudentevents');
		$this->Schedulingtimings = $this->loadModel('Schedulingtimings');
		$this->Conventionseasonroomevents = $this->loadModel('Conventionseasonroomevents');
		$this->Schedulings = $this->loadModel('Schedulings');
		$this->Conventionregistrationstudents = $this->loadModel('Conventionregistrationstudents');
		$this->Schedulingeventtweaks = $this->loadModel('Schedulingeventtweaks');
    }
	
	
	public function viewscheduling($convention_season_slug=null,$scheduling_category=null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Pre-check');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
		
        $this->set('convention_season_slug', $convention_season_slug);
        $this->set('scheduling_category', $scheduling_category);
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		//$this->prx($conventionSD);
		
		$this->set('conventionSD', $conventionSD);
		
		$this->set('convention_slug', $conventionSD->Conventions['slug']);
		
		// to list all schedulings
		$schedulingTimingsList = $this->Schedulingtimings->find()->where(['Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.schedule_category' => $scheduling_category])->contain(["Events","Users","Conventionrooms","Opponentuser"])->order(["Schedulingtimings.id" => "ASC"])->all();
		$this->set('schedulingTimingsList', $schedulingTimingsList);
		$this->set('pendingEventsToRoomsList', []);
    }

    public function startschedulec1($convention_season_slug=null) {
        
		
        $this->set('convention_season_slug', $convention_season_slug);
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		// to get details of schedule timings
		$schedulingsD = $this->Schedulings->find()->where(["Schedulings.conventionseasons_id" => $conventionSD->id, "Schedulings.convention_id" => $conventionSD->convention_id, "Schedulings.season_id" => $conventionSD->season_id, "Schedulings.season_year" => $conventionSD->season_year])->first();
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		$first_day 				= $schedulingsD->first_day;
		$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
		$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
		
		$lunch_time_start 		= date("H:i:s",strtotime($schedulingsD->lunch_time_start));
		$lunch_time_end 		= date("H:i:s",strtotime($schedulingsD->lunch_time_end));
		
		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = date("H:i:s",strtotime($schedulingsD->different_first_day_start_time));
			$different_first_day_end_time 	= date("H:i:s",strtotime($schedulingsD->different_first_day_end_time));
		}
		
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
		// group_event = yes || event_kind_id = sequential || needs_schedule = 1 || has_to_be_consecutive = yes
		$arrEventC1 = array();
		$arrEventC1[] = 0;
		$condEventList = array();
		$condEventList[] = "(Events.needs_schedule = '1' AND Events.group_event_yes_no = '1' AND Events.event_kind_id = 'Sequential' AND Events.has_to_be_consecutive = '1')";
		$eventList = $this->Events->find()->where($condEventList)->select(['id','event_id_number'])->all();
		//$this->prx($eventList);
		foreach($eventList as $eventdata)
		{
			$arrEventC1[] = $eventdata->id;
		}
		$arrEventC1Implode = implode(",",$arrEventC1);
		//$this->prx($arrEventC1);
		//$arrEventC1Implode = '344';
		
		
		
		// 1. Fetch all events that is required scheduling for this convention season
		$condEVCS = array();
		$condEVCS[] = "(Conventionseasonevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonevents.convention_id = '".$conventionSD->convention_id."')";
		$condEVCS[] = "(Conventionseasonevents.event_id IN ($arrEventC1Implode))"; // these are for event_id_number 870, 871 = 352, 353
		//$condEVCS[] = "(Conventionseasonevents.event_id IN (352,353))"; // these are for event_id_number 870, 871 = 352, 353
		
		$allEventsCS = $this->Conventionseasonevents->find()->where($condEVCS)->all();
		$c1EventsWithoutRooms = 0;
		$c1EventsWithoutGroups = 0;
		$c1RecordsCreated = 0;
		$c1UngroupedFallbackCount = 0;
		//$this->prx($condEVCS);
		foreach($allEventsCS as $eventcs)
		{
			$mainArrForEvent = array();
			// to check if this event require schedule
			
			$eventD = $this->Events->find()->where(['Events.id' => $eventcs->event_id])->first();
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			$eventIDCS = $eventD->id;
			// for now we are doing schedulings for event_id_number 870,871 for testing
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$eventIDCS."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$eventIDCS.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$eventIDCS."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			//$this->prx($roomArrCSEvent);
			
			// Check if there's only one room, then duplicate
			/* if (count($roomArrCSEvent) === 1) {
				// Duplicate the same record up to 4 times
				while (count($roomArrCSEvent) < 4) {
					$roomArrCSEvent[] = $roomArrCSEvent[0];
				}
			} */
			
			
			
			// ---- Load event tweaks (A: pinned day, B: pinned room, C: pinned start time) ----
			$eventTweak = $this->Schedulingeventtweaks->find()
				->where(['Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
				         'Schedulingeventtweaks.event_id'             => $eventIDCS])
				->first();
			// B: override room list with the pinned room
			if ($eventTweak && $eventTweak->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak->pinned_room_id];
			}
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent)>0)
			{	
				// First to fetch conv registrations
				$condCR = array();
				$condCR[] = "(Conventionregistrations.conventionseason_id = '".$conventionSD->id."' AND Conventionregistrations.convention_id = '".$conventionSD->convention_id."')";
				$conventionRegistrations = $this->Conventionregistrations->find()->where($condCR)->all();
				foreach($conventionRegistrations as $convreg)
				{
					//now fetch groups for this CR
					$condCRSTEV = array();
					$condCRSTEV[] = "(Crstudentevents.conventionseason_id = '".$conventionSD->id."' AND Crstudentevents.convention_id = '".$conventionSD->convention_id."')";
					$condCRSTEV[] = "(Crstudentevents.conventionregistration_id = '".$convreg->id."' AND Crstudentevents.event_id = '".$eventIDCS."')";
					$convRegSTEV = $this->Crstudentevents->find()->where($condCRSTEV)->select(['group_name'])->all();
					//$this->prx($convRegSTEV);
					if($convRegSTEV)
					{
						// if any group exists, then push it to array
						foreach($convRegSTEV as $convregstev)
						{
							// now create a variable with combination 
							// format is conventionseasons_id==convention_id==season_id==season_year==conventionregistration_id==event_id==event_id_number==user_id==group_name
							
							$varEventCombination 	= $conventionSD->id."==";
							$varEventCombination 	.= $conventionSD->convention_id."==";
							$varEventCombination 	.= $conventionSD->season_id."==";
							$varEventCombination 	.= $conventionSD->season_year."==";
							$varEventCombination 	.= $convreg->id."==";
							$varEventCombination	.= $eventIDCS."==";
							$varEventCombination 	.= $eventD->event_id_number."==";
							$varEventCombination 	.= $convreg->user_id."==";

							$groupName = trim((string)($convregstev->group_name ?? ''));
							if ($groupName === '') {
								$groupName = 'Ungrouped';
								$c1UngroupedFallbackCount++;
							}
							$varEventCombination 	.= $groupName;
							
							if(!in_array($varEventCombination,(array)$mainArrForEvent))
							{
								$mainArrForEvent[] = $varEventCombination;
							}
						}
					}
				}
			
			
				// now define timings for schedule for this event
			
				//echo 'eeee';
				//$this->prx($mainArrForEvent);
			
				if(count((array)$mainArrForEvent))
				{
					$cntrDays = 1;
					$resetTime = 1;
					$schDay = $first_day;
					$windowExceeded = false;
					
					$schStartDate = $start_date;
					
					// A: Pinned day — advance schStartDate/schDay to the first matching day in window
					if ($eventTweak && !empty($eventTweak->pinned_day)) {
						$weekArr  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
						$pinnedDayName = $eventTweak->pinned_day;
						$checkDate = new \DateTime($start_date);
						$convEndDate = (new \DateTime($start_date))->modify('+' . ($schedulingsD->number_of_days - 1) . ' days');
						$found = false;
						$dayOffset = 0;
						while ($checkDate <= $convEndDate) {
							$dow = $checkDate->format('l'); // e.g. 'Monday'
							if ($dow === $pinnedDayName) {
								$schStartDate = $checkDate->format('Y-m-d');
								$schDay       = $pinnedDayName;
								$cntrDays     = $dayOffset + 1;
								$found        = true;
								break;
							}
							$checkDate->modify('+1 day');
							$dayOffset++;
						}
						// If not found in window, skip this event tweak (fall back to normal)
						if (!$found) {
							$schDay = $first_day;
							$schStartDate = $start_date;
							$cntrDays = 1;
						}
					}
					
					// C: Pinned start time — override the normal starting time for this event
					$effectiveStartingTime = $normal_starting_time;
					if ($eventTweak && $eventTweak->pinned_start_time) {
						$effectiveStartingTime = date('H:i:s', strtotime($eventTweak->pinned_start_time));
					}
					
					$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
					// now firstly choose first room
					$cntrRoomCSEvent = 0;
					
					shuffle($mainArrForEvent);
					//$this->prx($mainArrForEvent);
					// get each record and enter in database
					for($cntrEVSCH=0;$cntrEVSCH<count((array)$mainArrForEvent);$cntrEVSCH++)
					{
						// data combination is
						// conventionseasons_id==convention_id==season_id==season_year==conventionregistration_id==event_id==event_id_number==user_id==group_name
						$stData = explode("==",$mainArrForEvent[$cntrEVSCH]);
						
						// now calculate timings
						//echo 'resetTime--upper--'.$resetTime;
						
						if($totalRoomsForThisEvent == 1)
						{
							$roomID = $roomArrCSEvent[0];
						}
						else
						{
							$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
						}
						
						
						// calculate start time
						if($resetTime == 1)
						{
							if($cntrDays == 1 && $cntrEVSCH == 0)
							{
								// check if there is a different time for first day
								if($starting_different_time_first_day_yes_no == 1 && !($eventTweak && $eventTweak->pinned_start_time))
								{
									$normal_starting_time 	= $different_first_day_start_time;
									$normal_finish_time 	= $different_first_day_end_time;
								}
							}
							
							// C: use pinned start time for the very first slot only
							$start_time 	= ($cntrEVSCH == 0) ? $effectiveStartingTime : $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						else
						{
							$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						//exit;
						
						/* now check if finish time of this schedule is before day finish time or later */
						if(strtotime($finish_time)<=strtotime($normal_finish_time))
						{
							$resetTime = 0;
						}	
						else
						{
							// here we need to check if multiple rooms are there for an event, then shift to next room
							if($cntrRoomCSEvent < $totalRoomsForThisEvent - 1)
							{
								// no need to change day, just shift to new room
								$cntrRoomCSEvent++;
							}
							else
							{
								// all rooms exhausted, reset room counter and advance to next day
								$cntrRoomCSEvent = 0;
								if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
									$windowExceeded = true;
									$this->flashConventionWindowExceeded($schedulingsD);
									break;
								}
							}
							
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
							
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
						
						
						
						/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
						THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
						*/
						$condRAvail = array();
						$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."')";
						$condRAvail[] = "(Schedulingtimings.room_id = '".$roomID."')";
						$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.id" => "DESC"])->first();
						if($checkRoomAvailability)
						{
							 
							$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));
							
							$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($room_finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
							
							$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
							$schDay 		= $checkRoomAvailability->day;
							
							
							// suppose in this case, finish time reach to day end time, then shift to next day
							if(strtotime($finish_time)>=strtotime($normal_finish_time))
							{
								if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
									$windowExceeded = true;
									$this->flashConventionWindowExceeded($schedulingsD);
									break;
								}
								
								$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
								$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
								
								$start_time 	= $normal_starting_time;
								$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
							}
						}
						
						
						/* echo 'start_time->'.$start_time.'--';
						echo 'finish_time->'.$finish_time.'--';
						echo 'day->'.$schDay.'--';
						echo 'schStartDate->'.$schStartDate.'--';
						echo 'normal_finish_time->'.$normal_finish_time.'--';
						echo 'resetTime->'.$resetTime.'--';
						echo '<hr>'; */
						
						
						
						/* Validate slot against all break periods (lunch, judging, sports) with loop to prevent blind jumps */
						$validSlot = $this->findValidSlot($start_time, $finish_time, $schDay, $schStartDate, $cntrDays, $normal_starting_time, $normal_finish_time, $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$eventIDCS);
						if (!empty($validSlot['window_exhausted'])) {
							$windowExceeded = true;
							$this->flashConventionWindowExceeded($schedulingsD);
							break;
						}
						$start_time = $validSlot['start_time'];
						$finish_time = $validSlot['finish_time'];
						$schDay = $validSlot['schDay'];
						$schStartDate = $validSlot['schStartDate'];
						$cntrDays = $validSlot['cntrDays'];
						$normal_starting_time = $validSlot['normal_starting_time'];
						$normal_finish_time = $validSlot['normal_finish_time'];
						
						
						
						
						
						/* Here we will check that this user_id is School Or student 
						School means its a group event
						Student means it's an individual event
						*/
						$fetchUserType = $this->fetchUserType($stData[7]);
						
						//now enter schedule timings
						$schedulingtimings = $this->Schedulingtimings->newEntity();
						$dataST = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

						$dataST->schedule_category				= 1;
						$dataST->conventionseasons_id			= $stData[0];
						$dataST->convention_id					= $stData[1];
						$dataST->season_id						= $stData[2];
						$dataST->season_year					= $stData[3];
						$dataST->conventionregistration_id 		= $stData[4];
						$dataST->event_id 						= $stData[5];
						$dataST->event_id_number 				= $stData[6];
						$dataST->user_id 						= $stData[7];
						$dataST->group_name 					= $stData[8];
						
						$dataST->room_id 						= $roomID;
						$dataST->day 							= $schDay;
						$dataST->start_time 					= $start_time;
						$dataST->finish_time 					= $finish_time;
						
						$dataST->created 						= date('Y-m-d H:i:s');
						$dataST->modified 						= date('Y-m-d H:i:s');
						
						$dataST->user_type 						= $fetchUserType;
						
						//echo $start_time;exit;
						
						$dataST->sch_date_time 					= $schStartDate.' '.date("H:i:s", strtotime($start_time));
						
						//$this->prx($dataST);

						$resultST = $this->Schedulingtimings->save($dataST);
						if($resultST)
						{
							$c1RecordsCreated++;
						}
					}

					if ($windowExceeded) {
						continue;
					}
				}
				else
				{
					$c1EventsWithoutGroups++;
				}
			
			}
			else
			{
				$c1EventsWithoutRooms++;
			}
		}

		if($c1RecordsCreated === 0 && count((array)$allEventsCS) > 0)
		{
			$this->Flash->warning('Category 1 has no generated schedule rows yet. Possible reasons: rooms not assigned for '.$c1EventsWithoutRooms.' event(s), no grouped participants found for '.$c1EventsWithoutGroups.' event(s), or the configured day window ran out before slots could be assigned.');
		}
		if($c1UngroupedFallbackCount > 0)
		{
			$this->Flash->warning('Category 1 used Ungrouped fallback for '.$c1UngroupedFallbackCount.' registration/event entry(ies) where group name was missing.');
		}
		
		//exit;
		
		//$this->Flash->success($msgSuccess);
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'fillgroupuserids', $convention_season_slug]);
		
    }
	
	
	public function startschedulec2($convention_season_slug=null) {
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		//$this->prx($conventionSD);
		
		// to get details of schedule timings
		$schedulingsD = $this->Schedulings->find()->where(["Schedulings.conventionseasons_id" => $conventionSD->id, "Schedulings.convention_id" => $conventionSD->convention_id, "Schedulings.season_id" => $conventionSD->season_id, "Schedulings.season_year" => $conventionSD->season_year])->first();
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$first_day 				= $schedulingsD->first_day;
		$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
		$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
		
		$lunch_time_start 		= date("H:i:s",strtotime($schedulingsD->lunch_time_start));
		$lunch_time_end 		= date("H:i:s",strtotime($schedulingsD->lunch_time_end));
		
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		
		
		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = date("H:i:s",strtotime($schedulingsD->different_first_day_start_time));
			$different_first_day_end_time 	= date("H:i:s",strtotime($schedulingsD->different_first_day_end_time));
		}
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
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
				$arrEventsC2[] = $eventD->id;
			}
		}
		//$this->pr($arrEventsC2);
		
		/* NOW GET STUDENTS FOR EACH EVENT */
		$arrStudentsC2 = array();
		foreach($arrEventsC2 as $event_id_c2)
		{
			$condSTC2 = array();
			$condSTC2[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND   Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
			$condSTC2[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
			
			$condSTC2[] = "(Conventionregistrationstudents.event_ids LIKE '".$event_id_c2."' OR Conventionregistrationstudents.event_ids LIKE '".$event_id_c2.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c2.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c2."')";
			
			$studentsC2 = $this->Conventionregistrationstudents->find()->where($condSTC2)->all();
			
			if($studentsC2)
			{
				foreach($studentsC2 as $studentEV)
				{
					$arrStudentsC2[$event_id_c2][] = $studentEV->student_id;
				}
			}
		}
		//$this->prx($arrStudentsC2);
		

		/* NOW FETCH STUDENTS FOR EACH EVENT AND PERFORM SCHEDULING */
		foreach($arrStudentsC2 as $event_id_c2 => $studentsListC2)
		{	
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c2])->first();
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id_c2."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id_c2.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c2.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c2."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			//$this->prx($roomArrCSEvent);
			
			// shuffle array
			shuffle($studentsListC2);
			
			$totalStudentsEV 			= count($studentsListC2);
			$totalByePlayer 			= $this->getByePlayerScheduling($totalStudentsEV);
			$arrStudentsForSplice 		= $studentsListC2;
			
			//echo $totalByePlayer;exit;
			
			$match_number = 1;
			/* DEFINE SCHEDULING FOR BYE PLAYERS */
			if($totalByePlayer>0)
			{
				$arrByePlayer 			= array();
				
				// pick number of random players for bye
				for($cntrByeP=0;$cntrByeP<$totalByePlayer;$cntrByeP++)
				{
					// generate a random number from 0 to total count of students
					$randByeNumber 		= rand(0,count($arrStudentsForSplice)-1);
					$byeStudentID 		= $arrStudentsForSplice[$randByeNumber];
					$arrByePlayer[] 	= $byeStudentID;
					array_splice($arrStudentsForSplice, $randByeNumber, 1);
					
					/* Here we will check that this user_id is School Or student 
					School means its a group event
					Student means it's an individual event
					*/
					$fetchUserType = $this->fetchUserType($byeStudentID);
					
					//now save bye player in database, opponent of bye player id will be 0
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 2;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $event_id_c2;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= $byeStudentID;
					$dataBye->group_name 					= NULL;
					$dataBye->room_id 						= $roomArrCSEvent[0];
					$dataBye->day 							= $first_day;
					$dataBye->start_time 					= $starting_different_time_first_day_yes_no == 1 ? $different_first_day_start_time : $normal_starting_time;
					$dataBye->finish_time 					= $starting_different_time_first_day_yes_no == 1 ? $different_first_day_start_time : $normal_starting_time;
					$dataBye->user_id_opponent 				= 0;
					$dataBye->round_number 					= 1;
					$dataBye->match_number 					= $match_number;
					$dataBye->is_bye 						= 1;
					$dataBye->created 						= date('Y-m-d H:i:s');
					
					$dataBye->sch_date_time 				= $start_date.' '.date("H:i:s", strtotime($dataBye->start_time));
					
					$dataBye->user_type 					= $fetchUserType;

					$resultBye = $this->Schedulingtimings->save($dataBye);
					
					$match_number++;
				}
			}
			
			$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
			// now firstly choose first room
			$cntrRoomCSEvent = 0;
			$cntrEVSCH = 0;
			
			//$this->prx($arrStudentsForSplice);
			
			/* DEFINE SCHEDULING FOR REMAINING PLAYERS AFTER BYE PLAYERS */
			// To check how many matches are there
			$totalMatches = ($totalStudentsEV-$totalByePlayer)/2;
			for($cntrRemainP=0;$cntrRemainP<$totalMatches;$cntrRemainP++)
			{
				// to get first player id
				$randFirstP 				= rand(0,count((array)$arrStudentsForSplice)-1);
				$first_student_id 			= $arrStudentsForSplice[$randFirstP];
				array_splice($arrStudentsForSplice, $randFirstP, 1);
				
				// to get opponent user id
				$randSecondP 				= rand(0,count((array)$arrStudentsForSplice)-1);
				$second_student_id 			= $arrStudentsForSplice[$randSecondP];
				array_splice($arrStudentsForSplice, $randSecondP, 1);
				
				/* Here we will check that this user_id is School Or student 
				School means its a group event
				Student means it's an individual event
				*/
				$fetchUserType = $this->fetchUserType($first_student_id);
				
				//now save remaining player in database with opponent user id
				$schedulingtimings = $this->Schedulingtimings->newEntity();
				$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

				$dataBye->schedule_category				= 2;
				$dataBye->conventionseasons_id			= $conventionSD->id;
				$dataBye->convention_id					= $conventionSD->convention_id;
				$dataBye->season_id						= $conventionSD->season_id;
				$dataBye->season_year 					= $conventionSD->season_year;
				$dataBye->conventionregistration_id 	= NULL;
				$dataBye->event_id 						= $event_id_c2;
				$dataBye->event_id_number 				= $eventD->event_id_number;
				$dataBye->user_id 						= $first_student_id;
				$dataBye->group_name 					= NULL;
				$dataBye->room_id 						= (int)$roomArrCSEvent[$cntrRoomCSEvent];
				$dataBye->day 							= NULL;
				$dataBye->start_time 					= NULL;
				$dataBye->finish_time 					= NULL;
				$dataBye->user_id_opponent 				= $second_student_id;
				$dataBye->round_number 					= 1;
				$dataBye->match_number 					= $match_number;
				$dataBye->is_bye 						= 0;
				$dataBye->created 						= date('Y-m-d H:i:s');
				
				$dataBye->sch_date_time 				= $start_date.' 00:00:00';
				
				$dataBye->user_type 					= $fetchUserType;

				$resultBye = $this->Schedulingtimings->save($dataBye);
				
				$match_number++;
				
				$cntrEVSCH++;
			}
		}
		
		
		
		
		/* After first round, we need to schedule next rounds till last round between 2 players */
		// Get all matches for each event and perform scheduling 'Schedulingtimings.schedule_category' => 2
		foreach($arrEventsC2 as $event_id_c2)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c2])->first();
			
			
			// to get total matches played in first round for this event including byes if any
			$countTotalMatR1Event = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c2,'Schedulingtimings.round_number' => 1])->count();
			
			// to get the last match number for this event
			$evLastMatch = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 2,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c2,'Schedulingtimings.round_number' => 1])->order(['Schedulingtimings.match_number' => 'DESC'])->first();
			$lastMatchNumber = $evLastMatch->match_number;
			
			$lastMatchNumber = $lastMatchNumber+1;
			
			/* Generate all subsequent rounds until only the Final remains.
			   Odd-round leftovers get a bye into the next round so no player
			   is dropped from the bracket. */
			$currentRound  = 1;
			$safetyLimit   = 0;
			$roomIdForRound = isset($roomArrCSEvent[0]) ? $roomArrCSEvent[0] : NULL;
			while (true) {
				$safetyLimit++;
				if ($safetyLimit > 15) break;

				$arrNR = array();
				$nextRounds = $this->Schedulingtimings->find()->where([
					'Schedulingtimings.schedule_category'   => 2,
					'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
					'Schedulingtimings.convention_id'       => $conventionSD->convention_id,
					'Schedulingtimings.season_id'           => $conventionSD->season_id,
					'Schedulingtimings.season_year'         => $conventionSD->season_year,
					'Schedulingtimings.event_id'            => $event_id_c2,
					'Schedulingtimings.round_number'        => $currentRound
				])->all();
				foreach ($nextRounds as $nextRound) {
					$arrNR[] = $nextRound->id;
				}

				// 0 or 1 records means this round IS the Final (or empty) — stop
				if (count($arrNR) <= 1) break;

				$tempNR = $arrNR;

				// Pair matches for the next round
				while (count($tempNR) >= 2) {
					$randFirstID = rand(0, count($tempNR) - 1);
					$first_id    = $tempNR[$randFirstID];
					array_splice($tempNR, $randFirstID, 1);

					$randSecondID = rand(0, count($tempNR) - 1);
					$second_id    = $tempNR[$randSecondID];
					array_splice($tempNR, $randSecondID, 1);

					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 2;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $event_id_c2;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= $roomIdForRound;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $first_id;
					$dataBye->schtimeautoid2			= $second_id;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 0;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
					$cntrEVSCH++;
				}

				// Odd player out — give them a bye into the next round
				if (count($tempNR) === 1) {
					$byeMatchId = $tempNR[0];
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 2;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $event_id_c2;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= $roomIdForRound;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $byeMatchId;
					$dataBye->schtimeautoid2			= 0;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 1;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
				}

				$currentRound++;
			}
		}
		
		//exit;
		
		
		
		/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY (EXCEPT BYE), START AND END TIME */
		/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE 
		DAY, START TIME AND END TIME */
		
		//exit;
		
		foreach($arrEventsC2 as $event_id)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			
			//$this->prx($roomArrCSEvent);
			
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '2' AND Schedulingtimings.is_bye = '0' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);
				
				$cntrDays 		= 1;
				$resetTime 		= 1;
				$schDay 		= $first_day;
				$windowExceeded = false;
				
				$schStartDate = $start_date;
				
				$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
				// now firstly choose first room
				$cntrRoomCSEvent 	= 0;
				$cntrEVSCH 			= 0;
				
				foreach($schedulingT as $schdata)
				{
					if($totalRoomsForThisEvent == 1)
					{
						$roomID = $roomArrCSEvent[0];
					}
					else
					{
						$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
					}
					
					/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
					THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
					*/
					$condRAvail = array();
					$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.room_id = '".$roomID."' AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
					
					//$condRAvail[] = "()";
					//$this->pr($condRAvail);
					$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.id" => "DESC"])->first();
					
					
					if($checkRoomAvailability)
					{
						//$this->prx($checkRoomAvailability);
						/* echo $checkRoomAvailability->id;echo '<br>';
						echo $start_date;echo '<br>';
						echo $schdata->id;echo '<br>'; */
						$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));
						
						$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($room_finish_time)));
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						
						$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
						$schDay 		= $checkRoomAvailability->day;
						
						/* echo $schDay;  echo '<br>';
						echo $normal_finish_time; echo '<br>';
						echo $start_time; echo '<br>';
						echo $finish_time; echo '<br>';
						exit; */
						
						// suppose in this case, finish time reach to day end time, then shift to next day
						if(strtotime($finish_time)>=strtotime($normal_finish_time))
						{
							if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
								$windowExceeded = true;
								$this->flashConventionWindowExceeded($schedulingsD);
								break;
							}
							
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
							
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
					}
					else
					{
						///////////////////
						// calculate start time
						if($resetTime == 1)
						{
							if($cntrDays == 1 && $cntrEVSCH == 0)
							{
								// check if there is a different time for first day
								if($starting_different_time_first_day_yes_no == 1)
								{
									$normal_starting_time 	= $different_first_day_start_time;
									$normal_finish_time 	= $different_first_day_end_time;
								}
							}
							
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						else
						{
							$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						//exit;
						
						/* now check if finish time of this schedule is before day finish time or later */
						if(strtotime($finish_time)<=strtotime($normal_finish_time))
						{
							$resetTime = 0;
						}	
						else
						{
							// here we need to check if multiple rooms are there for an event, then shift to next room
							if($cntrRoomCSEvent < $totalRoomsForThisEvent - 1)
							{
								// no need to change day, just shift to new room
								$cntrRoomCSEvent++;
							}
							else
							{
								// all rooms exhausted, reset room counter and advance to next day
								$cntrRoomCSEvent = 0;
								if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
									$windowExceeded = true;
									$this->flashConventionWindowExceeded($schedulingsD);
									break;
								}
							}
							
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
							
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
						///////////////////
					}
					
					
					
					
					/* Validate slot against all break periods (lunch, judging, sports) with loop to prevent blind jumps */
					$validSlot = $this->findValidSlot($start_time, $finish_time, $schDay, $schStartDate, $cntrDays, $normal_starting_time, $normal_finish_time, $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$event_id);
					if (!empty($validSlot['window_exhausted'])) {
						$windowExceeded = true;
						$this->flashConventionWindowExceeded($schedulingsD);
						break;
					}
					$start_time = $validSlot['start_time'];
					$finish_time = $validSlot['finish_time'];
					$schDay = $validSlot['schDay'];
					$schStartDate = $validSlot['schStartDate'];
					$cntrDays = $validSlot['cntrDays'];
					$normal_starting_time = $validSlot['normal_starting_time'];
					$normal_finish_time = $validSlot['normal_finish_time'];
					
					
					
					
					/* echo $start_time;echo '<br>';
					echo $finish_time;echo '<br>';
					echo $schDay;echo '<br>';
					echo '<hr>'; */
					
					/* here we calculate root, day, start time and end time - ends */
					
					
					
					$arrPP = [
					'room_id' 		=> $roomID,
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,
					
					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),
					
					'modified' 		=> date("Y-m-d H:i:s")
					];
					
					//$this->pr($arrPP);
					//echo '<hr>';
					
					// update day, start time and end time
					$this->Schedulingtimings->updateAll(
					[
					'room_id' 		=> $roomID,
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,
					
					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),
					
					'modified' 		=> date("Y-m-d H:i:s")
					],
					["id" => $schdata->id]);
					
					$cntrEVSCH++;
					
				}

				if ($windowExceeded) {
					continue;
				}
			
			}
			
			//exit;
			
			
		}
		
		//exit;
		
		//$this->Flash->success('Scheduling completed successfully for category 2.');
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec3', $convention_season_slug]);
		
	}
	
	
	public function startschedulec3($convention_season_slug=null) {
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		//$this->prx($conventionSD);
		
		// to get details of schedule timings
		$schedulingsD = $this->Schedulings->find()->where(["Schedulings.conventionseasons_id" => $conventionSD->id, "Schedulings.convention_id" => $conventionSD->convention_id, "Schedulings.season_id" => $conventionSD->season_id, "Schedulings.season_year" => $conventionSD->season_year])->first();
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$first_day 				= $schedulingsD->first_day;
		
		$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
		$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
		
		$lunch_time_start 		= date("H:i:s",strtotime($schedulingsD->lunch_time_start));
		$lunch_time_end 		= date("H:i:s",strtotime($schedulingsD->lunch_time_end));
		
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		
		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = date("H:i:s",strtotime($schedulingsD->different_first_day_start_time));
			$different_first_day_end_time 	= date("H:i:s",strtotime($schedulingsD->different_first_day_end_time));
		}
		
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
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
				$arrEventsC3[] = $eventD->id;
			}
		}
		//$this->prx($arrEventsC3);
		/* $arrEventsC3 = array();
		$arrEventsC3[] = 63;
		$arrEventsC3[] = 107; */
		//$arrEventsC3 = array();
		//$arrEventsC3[] = 65;
		//$this->prx($arrEventsC3);
		
		
		$eventCTR = 0;
		// Now run loop on each event and get groups and schedule
		foreach($arrEventsC3 as $event_id_c3)
		{
			/* PART 1 OF THIS EVENT */
			
			$mainArrForEvent = array();
			
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c3])->first();
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id_c3."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id_c3.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c3.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id_c3."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			//$this->prx($roomArrCSEvent);
			 
			// now get groups for this event from convention registration
			// First to fetch conv registrations
			$condCR = array();
			$condCR[] = "(Conventionregistrations.conventionseason_id = '".$conventionSD->id."' AND Conventionregistrations.convention_id = '".$conventionSD->convention_id."')";
			$conventionRegistrations = $this->Conventionregistrations->find()->where($condCR)->all();
			foreach($conventionRegistrations as $convreg)
			{
				//now fetch groups for this CR
				$condCRSTEV = array();
				$condCRSTEV[] = "(Crstudentevents.conventionseason_id = '".$conventionSD->id."' AND Crstudentevents.convention_id = '".$conventionSD->convention_id."')";
				$condCRSTEV[] = "(Crstudentevents.conventionregistration_id = '".$convreg->id."' AND Crstudentevents.event_id = '".$event_id_c3."')";
				$condCRSTEV[] = "(Crstudentevents.group_name != '')";
				
				//$condCRSTEV[] = "(Crstudentevents.user_id = '55')"; // to test
				
				$convRegSTEV = $this->Crstudentevents->find()->where($condCRSTEV)->select(['group_name'])->all();
				//$this->prx($convRegSTEV);
				if($convRegSTEV)
				{
					// if any group exists, then push it to array
					foreach($convRegSTEV as $convregstev)
					{
						// now create a variable with combination 
						// format is conventionseasons_id==convention_id==season_id==season_year==conventionregistration_id==event_id==event_id_number==user_id==group_name
						
						$varEventCombination 	= $conventionSD->id."==";
						$varEventCombination 	.= $conventionSD->convention_id."==";
						$varEventCombination 	.= $conventionSD->season_id."==";
						$varEventCombination 	.= $conventionSD->season_year."==";
						$varEventCombination 	.= $convreg->id."==";
						$varEventCombination	.= $event_id_c3."==";
						$varEventCombination 	.= $eventD->event_id_number."==";
						$varEventCombination 	.= $convreg->user_id."==";
						$varEventCombination 	.= $convregstev->group_name;
						
						if(!in_array($varEventCombination,(array)$mainArrForEvent))
						{
							$mainArrForEvent[] = $varEventCombination;
						}
					}
				}
			}
			
			//$this->prx($mainArrForEvent);
			
			
			
			if(count((array)$mainArrForEvent))
			{
				shuffle($mainArrForEvent);
				
				// now get total bye groups
				$totalGroupsEV 				= count($mainArrForEvent);
				$totalByeGroup 				= $this->getByePlayerScheduling($totalGroupsEV);
				$arrGroupsForSplice 		= $mainArrForEvent;
				
				//$this->prx($arrGroupsForSplice);
				
				//echo $totalByeGroup;exit;
				
				$match_number = 1;
				/* DEFINE SCHEDULING FOR BYE GROUPS */
				if($totalByeGroup>0)
				{
					// pick number of random players for bye
					for($cntrByeP=0;$cntrByeP<$totalByeGroup;$cntrByeP++)
					{
						// generate a random number from 0 to total count of students
						$randByeNumber 		= rand(0,count($arrGroupsForSplice)-1);
						
						// now explode data from array to get al details
						$dataGExplode = explode("==",$arrGroupsForSplice[$randByeNumber]);
						//$this->prx($dataGExplode);
						
						array_splice($arrGroupsForSplice, $randByeNumber, 1);
						
						/* Here we will check that this user_id is School Or student 
						School means its a group event
						Student means it's an individual event
						*/
						$fetchUserType = $this->fetchUserType($dataGExplode[7]);
						
						
						
						//now save bye player in database, opponent of bye player id will be 0
						$schedulingtimings = $this->Schedulingtimings->newEntity();
						$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

						$dataBye->schedule_category				= 3;
						$dataBye->conventionseasons_id			= $conventionSD->id;
						$dataBye->convention_id					= $conventionSD->convention_id;
						$dataBye->season_id						= $conventionSD->season_id;
						$dataBye->season_year 					= $conventionSD->season_year;
						$dataBye->conventionregistration_id 	= $dataGExplode[4];
						$dataBye->event_id 						= $eventD->id;
						$dataBye->event_id_number 				= $eventD->event_id_number;
						$dataBye->user_id 						= $dataGExplode[7];
						$dataBye->group_name 					= $dataGExplode[8];
						$dataBye->room_id 						= $roomArrCSEvent[0];
						$dataBye->day 							= $first_day;
						$dataBye->start_time 					= $starting_different_time_first_day_yes_no == 1 ? $different_first_day_start_time : $normal_starting_time;
						$dataBye->finish_time 					= $starting_different_time_first_day_yes_no == 1 ? $different_first_day_start_time : $normal_starting_time;
						$dataBye->user_id_opponent 				= 0;
						$dataBye->round_number 					= 1;
						$dataBye->match_number 					= $match_number;
						$dataBye->is_bye 						= 1;
						$dataBye->created 						= date('Y-m-d H:i:s');
						
						$dataBye->sch_date_time 				= $start_date.' '.date("H:i:s", strtotime($dataBye->start_time));
						
						$dataBye->user_type 					= $fetchUserType;

						$resultBye = $this->Schedulingtimings->save($dataBye);
						
						$match_number++;
					}
				}
				
				//$this->prx($arrGroupsForSplice);
				
				/* DEFINE SCHEDULING FOR REMAINING PLAYERS AFTER BYE PLAYERS */
				// To check how many matches are there
				$totalMatches = ($totalGroupsEV-$totalByeGroup)/2;
				for($cntrRemainP=0;$cntrRemainP<$totalMatches;$cntrRemainP++)
				{
					// to get first group id
					$randFirstP 				= rand(0,count((array)$arrGroupsForSplice)-1);
					// now explode data to get info
					$dataGExplodeFirst = explode("==",$arrGroupsForSplice[$randFirstP]);
					array_splice($arrGroupsForSplice, $randFirstP, 1);
					
					
					// to get opponent group id
					$randSecondP 				= rand(0,count((array)$arrGroupsForSplice)-1);
					// now explode data to get info
					$dataGExplodeSecond = explode("==",$arrGroupsForSplice[$randSecondP]);
					array_splice($arrGroupsForSplice, $randSecondP, 1);
					
					/* Here we will check that this user_id is School Or student 
					School means its a group event
					Student means it's an individual event
					*/
					$fetchUserType = $this->fetchUserType($dataGExplodeFirst[7]);
					
					//now save remaining player in database with opponent user id
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

					$dataBye->schedule_category				= 3;
					$dataBye->conventionseasons_id			= $conventionSD->id;
					$dataBye->convention_id					= $conventionSD->convention_id;
					$dataBye->season_id						= $conventionSD->season_id;
					$dataBye->season_year 					= $conventionSD->season_year;
					$dataBye->conventionregistration_id 	= NULL;
					$dataBye->event_id 						= $eventD->id;
					$dataBye->event_id_number 				= $eventD->event_id_number;
					$dataBye->user_id 						= $dataGExplodeFirst[7];
					$dataBye->group_name 					= $dataGExplodeFirst[8];
					$dataBye->room_id 						= NULL;
					$dataBye->day 							= NULL;
					$dataBye->start_time 					= NULL;
					$dataBye->finish_time 					= NULL;
					$dataBye->user_id_opponent 				= $dataGExplodeSecond[7];
					$dataBye->group_name_opponent 			= $dataGExplodeSecond[8];
					$dataBye->round_number 					= 1;
					$dataBye->match_number 					= $match_number;
					$dataBye->is_bye 						= 0;
					$dataBye->created 						= date('Y-m-d H:i:s');
					
					$dataBye->sch_date_time 				= $start_date.' 00:00:00';
					
					$dataBye->user_type 					= $fetchUserType;

					$resultBye = $this->Schedulingtimings->save($dataBye);
					
					$match_number++;
					
				}
			}
			
			
			
			
			
			
			/* PART 2 OF THIS EVENT */
			
			/* After first round, we need to schedule next rounds till last round between 2 players */
			// Get all matches for each event and perform scheduling 'Schedulingtimings.schedule_category' => 3
			
			// to get total matches played in first round for this event including byes if any
			$countTotalMatR1Event = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c3,'Schedulingtimings.round_number' => 1])->count();
			
			// to get the last match number for this event
			$evLastMatch = $this->Schedulingtimings->find()->where(['Schedulingtimings.schedule_category' => 3,'Schedulingtimings.conventionseasons_id' => $conventionSD->id,'Schedulingtimings.convention_id' => $conventionSD->convention_id,'Schedulingtimings.season_id' => $conventionSD->season_id,'Schedulingtimings.season_year' => $conventionSD->season_year,'Schedulingtimings.event_id' => $event_id_c3,'Schedulingtimings.round_number' => 1])->order(['Schedulingtimings.match_number' => 'DESC'])->first();
			$lastMatchNumber = $evLastMatch->match_number;
			
			$lastMatchNumber = $lastMatchNumber+1;
			
			/* Generate all subsequent rounds until only the Final remains.
			   Odd-round leftovers get a bye into the next round so no group
			   is dropped from the bracket. */
			$currentRound  = 1;
			$safetyLimit   = 0;
			while (true) {
				$safetyLimit++;
				if ($safetyLimit > 15) break;

				$arrNR = array();
				$nextRounds = $this->Schedulingtimings->find()->where([
					'Schedulingtimings.schedule_category'   => 3,
					'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
					'Schedulingtimings.convention_id'       => $conventionSD->convention_id,
					'Schedulingtimings.season_id'           => $conventionSD->season_id,
					'Schedulingtimings.season_year'         => $conventionSD->season_year,
					'Schedulingtimings.event_id'            => $event_id_c3,
					'Schedulingtimings.round_number'        => $currentRound
				])->all();
				foreach ($nextRounds as $nextRound) {
					$arrNR[] = $nextRound->id;
				}

				// 0 or 1 records means this round IS the Final (or empty) — stop
				if (count($arrNR) <= 1) break;

				$tempNR = $arrNR;

				// Pair matches for the next round
				while (count($tempNR) >= 2) {
					$randFirstID = rand(0, count($tempNR) - 1);
					$first_id    = $tempNR[$randFirstID];
					array_splice($tempNR, $randFirstID, 1);

					$randSecondID = rand(0, count($tempNR) - 1);
					$second_id    = $tempNR[$randSecondID];
					array_splice($tempNR, $randSecondID, 1);

					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 3;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $eventD->id;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= NULL;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $first_id;
					$dataBye->schtimeautoid2			= $second_id;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 0;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
				}

				// Odd group out — give them a bye into the next round
				if (count($tempNR) === 1) {
					$byeMatchId = $tempNR[0];
					$schedulingtimings = $this->Schedulingtimings->newEntity();
					$dataBye = $this->Schedulingtimings->patchEntity($schedulingtimings, array());
					$dataBye->schedule_category			= 3;
					$dataBye->conventionseasons_id		= $conventionSD->id;
					$dataBye->convention_id				= $conventionSD->convention_id;
					$dataBye->season_id					= $conventionSD->season_id;
					$dataBye->season_year				= $conventionSD->season_year;
					$dataBye->conventionregistration_id	= NULL;
					$dataBye->event_id					= $eventD->id;
					$dataBye->event_id_number			= $eventD->event_id_number;
					$dataBye->user_id					= 0;
					$dataBye->group_name				= NULL;
					$dataBye->room_id					= NULL;
					$dataBye->day						= NULL;
					$dataBye->start_time				= NULL;
					$dataBye->finish_time				= NULL;
					$dataBye->user_id_opponent			= 0;
					$dataBye->schtimeautoid1			= $byeMatchId;
					$dataBye->schtimeautoid2			= 0;
					$dataBye->round_number				= $currentRound + 1;
					$dataBye->match_number				= $lastMatchNumber;
					$dataBye->is_bye					= 1;
					$dataBye->created					= date('Y-m-d H:i:s');
					$dataBye->sch_date_time				= $start_date.' 00:00:00';
					$this->Schedulingtimings->save($dataBye);
					$lastMatchNumber++;
				}

				$currentRound++;
			}
			
			
			
			
			
			
			
			
			
			/* PART 3 OF THIS EVENT */
			
			/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY (EXCEPT BYE), START AND END TIME */
			/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE 
			DAY, START TIME AND END TIME */
			
			$event_id = $event_id_c3;
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '3' AND Schedulingtimings.is_bye = '0' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);
				
				$cntrDays 		= 1;
				$resetTime 		= 1;
				$schDay 		= $first_day;
				$windowExceeded = false;
				
				$schStartDate = $start_date;
				
				$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
				// now firstly choose first room
				$cntrRoomCSEvent 	= 0;
				$cntrEVSCH 			= 0;
				
				foreach($schedulingT as $schdata)
				{
					if($totalRoomsForThisEvent == 1)
					{
						$roomID = $roomArrCSEvent[0];
					}
					else
					{
						$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
					}
					
					/* here we calculate room, day, start time and end time - starts */
					if($resetTime == 1)
					{
						if($cntrDays == 1 && $cntrEVSCH == 0)
						{
							// check if there is a different time for first day
							if($starting_different_time_first_day_yes_no == 1)
							{
								$normal_starting_time 	= $different_first_day_start_time;
								$normal_finish_time 	= $different_first_day_end_time;
							}
						}
						
						$start_time 	= $normal_starting_time;
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
					}
					else
					{
						$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($finish_time)));
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					}
					
					/* now check if finish time of this schedule is before day finish time or later */
					if(strtotime($finish_time)<=strtotime($normal_finish_time))
					{
						$resetTime = 0;
					}	
					else
					{
						// here we need to check if multiple rooms are there for an event, then shift to next room
						if($cntrRoomCSEvent < $totalRoomsForThisEvent - 1)
						{
							// no need to change day, just shift to new room
							$cntrRoomCSEvent++;
						}
						else
						{
							// all rooms exhausted, reset room counter and advance to next day
							$cntrRoomCSEvent = 0;
							if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
								$windowExceeded = true;
								$this->flashConventionWindowExceeded($schedulingsD);
								break;
							}
						}
						
						$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
						$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
						
						$start_time 	= $normal_starting_time;
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
					}
					
					
					/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
					THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
					*/
					
					$condRAvail = array();
					$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."')";
					$condRAvail[] = "(Schedulingtimings.room_id = '".$roomID."')";
					
					$condRAvail[] = "(Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
					
					if($eventCTR>0)
					{
						$condRAvail[] = "(Schedulingtimings.is_bye = 0)";
					}
					
					//$this->prx($condRAvail);
					$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.id" => "DESC"])->first();
					//$this->prx($condRAvail);
					
					/* echo '<pre>';print_r($condRAvail);
					echo '</pre>'; */
					
					if($checkRoomAvailability)
					{	//$this->prx($checkRoomAvailability);
						$availID = $checkRoomAvailability->id;
						
						$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));
						
						$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($room_finish_time)));
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						
						$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
						$schDay 		= $checkRoomAvailability->day;
						
						// suppose in this case, finish time reach to day end time, then shift to next day
						if(strtotime($finish_time)>=strtotime($normal_finish_time))
						{
							if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
								$windowExceeded = true;
								$this->flashConventionWindowExceeded($schedulingsD);
								break;
							}
							
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
							
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
						
						if($schDay != $first_day)
						{
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
						}
						
						/* echo $schDay.'->'.$start_time.' :: '.$finish_time.'==eventid--'.$event_id.'---availID=====>'.$availID.'-----normal_starting_time==>'.$normal_starting_time.'--normal_finish_time==>'.$normal_finish_time;
						echo '<br>'; */
					}
					
					
					
					
					/* Validate slot against all break periods (lunch, judging, sports) with loop to prevent blind jumps */
					$validSlot = $this->findValidSlot($start_time, $finish_time, $schDay, $schStartDate, $cntrDays, $normal_starting_time, $normal_finish_time, $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$event_id);
					if (!empty($validSlot['window_exhausted'])) {
						$windowExceeded = true;
						$this->flashConventionWindowExceeded($schedulingsD);
						break;
					}
					$start_time = $validSlot['start_time'];
					$finish_time = $validSlot['finish_time'];
					$schDay = $validSlot['schDay'];
					$schStartDate = $validSlot['schStartDate'];
					$cntrDays = $validSlot['cntrDays'];
					$normal_starting_time = $validSlot['normal_starting_time'];
					$normal_finish_time = $validSlot['normal_finish_time'];
					
					
					
					
					// update day, start time and end time
					$this->Schedulingtimings->updateAll(
					[
					'room_id' 		=> $roomID,
					'day' 			=> $schDay,
					'start_time' 	=> $start_time,
					'finish_time' 	=> $finish_time,
					
					'sch_date_time' 	=> $schStartDate.' '.date("H:i:s", strtotime($start_time)),
					
					'modified' 		=> date("Y-m-d H:i:s")
					],
					["id" => $schdata->id]);
					
					$cntrEVSCH++;
					
					/* echo $schDay.'->'.$start_time.' :: '.$finish_time.'==eventid--'.$event_id.'---availID=====>'.$availID.'-----normal_finish_time==>'.$normal_finish_time;
					echo '<br>'; */
					
				}

				if ($windowExceeded) {
					continue;
				}
			
			}
			
			//echo '<hr>';
			
		$eventCTR++;	
			
		}
		
		//exit;
		//echo $cntrEVSCH;exit;
		
		//$this->Flash->success('Scheduling completed successfully for category 3.');
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec1', $convention_season_slug]);
		
	}
	
	
	public function startschedulec4($convention_season_slug=null) {
		@set_time_limit(0);
		@ini_set('max_execution_time', '0');
		$this->set('convention_season_slug', $convention_season_slug);
		$this->request->getSession()->delete('Scheduling.windowWarningShown');
		$this->scheduleWindowWarningShown = false;
		$defaultConnection = ConnectionManager::get('default');
		if (method_exists($defaultConnection, 'enableQueryLogging')) {
			$defaultConnection->enableQueryLogging(false);
		} elseif (method_exists($defaultConnection, 'logQueries')) {
			$defaultConnection->logQueries(false);
		}
		
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);

		/* Start a fresh full run now that Category 4 is first in chain */
		$this->clearSchedulingtimings($convention_season_slug);
		
		//$this->prx($conventionSD);
		
		// to get details of schedule timings
		$schedulingsD = $this->Schedulings->find()->where(["Schedulings.conventionseasons_id" => $conventionSD->id, "Schedulings.convention_id" => $conventionSD->convention_id, "Schedulings.season_id" => $conventionSD->season_id, "Schedulings.season_year" => $conventionSD->season_year])->first();
		if ($redirect = $this->ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)) {
			return $redirect;
		}
		$first_day 				= $schedulingsD->first_day;
		$normal_starting_time 	= $schedulingsD->normal_starting_time;
		$normal_finish_time 	= $schedulingsD->normal_finish_time;
		
		$lunch_time_start 		= $schedulingsD->lunch_time_start;
		$lunch_time_end 		= $schedulingsD->lunch_time_end;
		
		$start_date 			= date("Y-m-d",strtotime($schedulingsD->start_date));
		
		$starting_different_time_first_day_yes_no = $schedulingsD->starting_different_time_first_day_yes_no;
		if($starting_different_time_first_day_yes_no == 1)
		{
			$different_first_day_start_time = $schedulingsD->different_first_day_start_time;
			$different_first_day_end_time 	= $schedulingsD->different_first_day_end_time;
		}
		
		
		/* TO GET ALL THE EVENTS WITH FOLLOWING CONDITIONS */
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
				$arrEventsC4[] = $eventD->id;
			}
		}
		//$this->prx($arrEventsC4);
		//$arrEventsC4 = array();$arrEventsC4[] = 59;
		
		
		/* NOW GET STUDENTS FOR EACH EVENT */
		$arrStudentsC4 = array();
		foreach($arrEventsC4 as $event_id_c4)
		{
			$condSTC4 = array();
			$condSTC4[] = "(Conventionregistrationstudents.convention_id = '".$conventionSD->convention_id."' AND  Conventionregistrationstudents.season_id = '".$conventionSD->season_id."' AND   Conventionregistrationstudents.season_year = '".$conventionSD->season_year."')";
			$condSTC4[] = "(Conventionregistrationstudents.status = '1' AND Conventionregistrationstudents.student_id > 0)";
			
			$condSTC4[] = "(Conventionregistrationstudents.event_ids LIKE '".$event_id_c4."' OR Conventionregistrationstudents.event_ids LIKE '".$event_id_c4.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c4.",%' OR Conventionregistrationstudents.event_ids LIKE '%,".$event_id_c4."')";
			
			$studentsC4 = $this->Conventionregistrationstudents->find()->where($condSTC4)->all();
			
			if($studentsC4)
			{
				foreach($studentsC4 as $studentEV)
				{
					$arrStudentsC4[$event_id_c4][] = $studentEV->student_id;
				}
			}
		}
		//$this->prx($arrStudentsC4);
		
		
		/* NOW FETCH STUDENTS FOR EACH EVENT AND PERFORM SCHEDULING */
		foreach($arrStudentsC4 as $event_id_c4 => $studentsListC4)
		{	
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id_c4])->first();
			
			// shuffle array
			shuffle($studentsListC4);
			
			foreach($studentsListC4 as $student_id)
			{
				/* Here we will check that this user_id is School Or student 
				School means its a group event
				Student means it's an individual event
				*/
				$fetchUserType = $this->fetchUserType($student_id);
				
				//now enter schedule timings
				$schedulingtimings = $this->Schedulingtimings->newEntity();
				$dataST = $this->Schedulingtimings->patchEntity($schedulingtimings, array());

				$dataST->schedule_category				= 4;
				$dataST->conventionseasons_id			= $conventionSD->id;
				$dataST->convention_id					= $conventionSD->convention_id;
				$dataST->season_id						= $conventionSD->season_id;
				$dataST->season_year					= $conventionSD->season_year;
				$dataST->conventionregistration_id 		= NULL;
				$dataST->event_id 						= $eventD->id;
				$dataST->event_id_number 				= $eventD->event_id_number;
				$dataST->user_id 						= $student_id;
				$dataST->group_name 					= NULL;
				
				$dataST->room_id 						= NULL;
				$dataST->day 							= NULL;
				$dataST->start_time 					= NULL;
				$dataST->finish_time 					= NULL;
				
				$dataST->created 						= date('Y-m-d H:i:s');
				$dataST->modified 						= date('Y-m-d H:i:s');
				
				$dataST->sch_date_time 					= $start_date.' 00:00:00';
				
				$dataST->user_type 						= $fetchUserType;
				//$this->prx($dataST);

				$resultST = $this->Schedulingtimings->save($dataST);
			}
			
			
		}
		
		
		/* IN ABOVE CODE, WE DEFINE SCHEDULING BUT NOT DEFINED DAY, START AND END TIME */
		/* IN BELOW CODE WE WILL FETCH THIS SCHEDULING AGAIN FOR EACH EVENT ONE BY ONE AND DEFINE 
		DAY, START TIME AND END TIME */
		
		foreach($arrEventsC4 as $event_id)
		{
			// to get event details
			$eventD = $this->Events->find()->where(['Events.id' => $event_id])->first();
			
			// to calculate event execution time
			$eventSetupRoundJudTime 	= $eventD->setup_time+$eventD->round_time+$eventD->judging_time;
			
			// now check that if any room is allocated for this event
			$condRoomCS = array();
			$condRoomCS[] = "(Conventionseasonroomevents.conventionseasons_id = '".$conventionSD->id."' AND Conventionseasonroomevents.convention_id = '".$conventionSD->convention_id."' AND Conventionseasonroomevents.season_id = '".$conventionSD->season_id."' AND Conventionseasonroomevents.season_year = '".$conventionSD->season_year."')";
			$condRoomCS[] = "(Conventionseasonroomevents.event_ids = '".$event_id."' OR 
							Conventionseasonroomevents.event_ids LIKE '".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id.",%' OR 
							Conventionseasonroomevents.event_ids LIKE '%,".$event_id."')";
			$roomCSEvent = $this->Conventionseasonroomevents->find()->select(['room_id'])->where($condRoomCS)->all();
			$roomArrCSEvent = array();
			foreach($roomCSEvent as $roomeventcs)
			{
				$roomArrCSEvent[] = $roomeventcs->room_id;
			}
			//$this->prx($roomArrCSEvent);
			
			// ---- Load event tweaks (A: pinned day, B: pinned room, C: pinned start time) ----
			$eventTweak4 = $this->Schedulingeventtweaks->find()
				->where(['Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
				         'Schedulingeventtweaks.event_id'             => $event_id])
				->first();
			// B: override room assignment
			if ($eventTweak4 && $eventTweak4->pinned_room_id) {
				$roomArrCSEvent = [(int)$eventTweak4->pinned_room_id];
			}
			
			// check if there is rooms assigned for this event
			if(count((array)$roomArrCSEvent))
			{
				// now get all scheduling timings except BYE for this convention season
				$condST = array();
				$condST[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
				$condST[] = "(Schedulingtimings.schedule_category = '4' AND Schedulingtimings.event_id = '".$event_id."')";
				$schedulingT = $this->Schedulingtimings->find()->where($condST)->order(["Schedulingtimings.id" => "ASC"])->all();
				//$this->prx($schedulingT);
				
				$cntrDays 		= 1;
				$resetTime 		= 1;
				$schDay 		= $first_day;
				$windowExceeded = false;
				
				$schStartDate = $start_date;
				
				// A: Pinned day — advance schStartDate/schDay to the matching day in window
				if ($eventTweak4 && !empty($eventTweak4->pinned_day)) {
					$weekArr4  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
					$checkDate4 = new \DateTime($start_date);
					$convEnd4   = (new \DateTime($start_date))->modify('+' . ($schedulingsD->number_of_days - 1) . ' days');
					$found4 = false;
					$dayOff4 = 0;
					while ($checkDate4 <= $convEnd4) {
						if ($checkDate4->format('l') === $eventTweak4->pinned_day) {
							$schStartDate = $checkDate4->format('Y-m-d');
							$schDay       = $eventTweak4->pinned_day;
							$cntrDays     = $dayOff4 + 1;
							$found4 = true;
							break;
						}
						$checkDate4->modify('+1 day');
						$dayOff4++;
					}
				}
				
				// C: Pinned start time
				$effectiveStartingTime4 = $normal_starting_time;
				if ($eventTweak4 && $eventTweak4->pinned_start_time) {
					$effectiveStartingTime4 = date('H:i:s', strtotime($eventTweak4->pinned_start_time));
				}
				
				$batchSizeC4 = $this->getCategory4ConcurrentBatchSize($eventD);
				
				$totalRoomsForThisEvent = count((array)$roomArrCSEvent);
				// now firstly choose first room
				$cntrRoomCSEvent 	= 0;
				$cntrEVSCH 			= 0;

				$pendingRows = [];
				foreach ($schedulingT as $schdata) {
					$pendingRows[] = $schdata;
				}

				$slotGuard = 0;
				while (count($pendingRows) > 0)
				{
					$slotGuard++;
					if ($slotGuard > 10000) {
						$this->Flash->warning('Category 4 scheduler stopped early for event '.$eventD->event_id_number.' to avoid an infinite loop while placing participants.');
						break;
					}

					if($totalRoomsForThisEvent == 1)
					{
						$roomID = $roomArrCSEvent[0];
					}
					else
					{
						$roomID = $roomArrCSEvent[$cntrRoomCSEvent];
					}
					
					/* HERE WE NEED TO CHECK IF THIS ROOM ALREADY HAVING AN EVENT
					THEN WE NEED TO CHANGE START/FINISH TIMINGS ON THAT BASIS
					*/
					$condRAvail = array();
					$condRAvail[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.room_id = '".$roomID."' AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
					
					//$condRAvail[] = "()";
					//$this->pr($condRAvail);
					$checkRoomAvailability = $this->Schedulingtimings->find()->where($condRAvail)->order(["Schedulingtimings.sch_date_time" => "DESC","Schedulingtimings.id" => "DESC"])->first();
					
					
					if($checkRoomAvailability)
					{
						//$this->pr($checkRoomAvailability);
						//echo $checkRoomAvailability->id;echo '<br>';
						//echo $start_date;echo '<br>';
						//echo $schdata->id;echo '<br>';
						$room_finish_time 	= date("H:i:s",strtotime($checkRoomAvailability->finish_time));
						
						$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($room_finish_time)));
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						
						$schStartDate 	= date('Y-m-d', strtotime($checkRoomAvailability->sch_date_time));
						$schDay 		= $checkRoomAvailability->day;
						
						/* echo $schDay;  echo '<br>';
						echo $normal_finish_time; echo '<br>';
						echo $start_time; echo '<br>';
						echo $finish_time; echo '<br>';
						exit; */
						
						// suppose in this case, finish time reach to day end time, then shift to next day
						if(strtotime($finish_time)>=strtotime($normal_finish_time))
						{
							if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
								$windowExceeded = true;
								$this->flashConventionWindowExceeded($schedulingsD);
								break;
							}
							
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
							
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
					}
					else
					{
						////////////////////////////
						// calculate start time
						if($resetTime == 1)
						{
							if($cntrDays == 1 && $cntrEVSCH == 0)
							{
								// check if there is a different time for first day
								if($starting_different_time_first_day_yes_no == 1 && !($eventTweak4 && $eventTweak4->pinned_start_time))
								{
									$normal_starting_time 	= $different_first_day_start_time;
									$normal_finish_time 	= $different_first_day_end_time;
								}
							}
							
							// C: use pinned start time for the very first slot only
							$start_time 	= ($cntrEVSCH == 0) ? $effectiveStartingTime4 : $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						else
						{
							$start_time 	= date("H:i:s", strtotime('+0 minutes', strtotime($finish_time)));
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
						}
						//exit;
						
						/* now check if finish time of this schedule is before day finish time or later */
						if(strtotime($finish_time)<=strtotime($normal_finish_time))
						{
							$resetTime = 0;
						}	
						else
						{
							// here we need to check if multiple rooms are there for an event, then shift to next room
							if($cntrRoomCSEvent < $totalRoomsForThisEvent - 1)
							{
								// no need to change day, just shift to new room
								$cntrRoomCSEvent++;
							}
							else
							{
								// all rooms exhausted, reset room counter and advance to next day
								$cntrRoomCSEvent = 0;
								if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
									$windowExceeded = true;
									$this->flashConventionWindowExceeded($schedulingsD);
									break;
								}
							}
							
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
							
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}
						////////////////////////////
					}
					
					
					
					
					/* Validate slot against all break periods (lunch, judging, sports) with loop to prevent blind jumps */
					$validSlot = $this->findValidSlot($start_time, $finish_time, $schDay, $schStartDate, $cntrDays, $normal_starting_time, $normal_finish_time, $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$event_id);
					if (!empty($validSlot['window_exhausted'])) {
						$windowExceeded = true;
						$this->flashConventionWindowExceeded($schedulingsD);
						break;
					}
					$start_time = $validSlot['start_time'];
					$finish_time = $validSlot['finish_time'];
					$schDay = $validSlot['schDay'];
					$schStartDate = $validSlot['schStartDate'];
					$cntrDays = $validSlot['cntrDays'];
					$normal_starting_time = $validSlot['normal_starting_time'];
					$normal_finish_time = $validSlot['normal_finish_time'];

					$slotStartDate = $schStartDate;
					$slotStartTime = $start_time;
					$slotFinishTime = $finish_time;

					$assignIds = [];
					$remainingRows = [];
					$assignedInThisSlot = 0;
					foreach ($pendingRows as $pendingRow) {
						if ($assignedInThisSlot < $batchSizeC4 && !$this->hasUserSchedulingConflict($conventionSD, (int)$pendingRow->user_id, $slotStartDate, $slotStartTime, $slotFinishTime)) {
							$assignIds[] = $pendingRow->id;
							$assignedInThisSlot++;
						} else {
							$remainingRows[] = $pendingRow;
						}
					}

					if (count($assignIds) === 0) {
						$start_time = date("H:i:s", strtotime('+0 minutes', strtotime($slotFinishTime)));
						$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));

						if (strtotime($finish_time) >= strtotime($normal_finish_time)) {
							if($cntrRoomCSEvent < $totalRoomsForThisEvent - 1)
							{
								$cntrRoomCSEvent++;
							}
							else
							{
								$cntrRoomCSEvent = 0;
								if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
									$windowExceeded = true;
									$this->flashConventionWindowExceeded($schedulingsD);
									break;
								}
							}
							$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
							$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
							$start_time 	= $normal_starting_time;
							$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						}

						$validSlot = $this->findValidSlot($start_time, $finish_time, $schDay, $schStartDate, $cntrDays, $normal_starting_time, $normal_finish_time, $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID, (int)$event_id);
						if (!empty($validSlot['window_exhausted'])) {
							$windowExceeded = true;
							$this->flashConventionWindowExceeded($schedulingsD);
							break;
						}
						$schDay = $validSlot['schDay'];
						$schStartDate = $validSlot['schStartDate'];
						$cntrDays = $validSlot['cntrDays'];
						$normal_starting_time = $validSlot['normal_starting_time'];
						$normal_finish_time = $validSlot['normal_finish_time'];
						$start_time = $validSlot['start_time'];
						$finish_time = $validSlot['finish_time'];

						continue;
					}

					$this->Schedulingtimings->updateAll(
					[
					'room_id' 		=> $roomArrCSEvent[$cntrRoomCSEvent],
					'day' 			=> $schDay,
					'start_time' 	=> $slotStartTime,
					'finish_time' 	=> $slotFinishTime,
					'sch_date_time' 	=> $slotStartDate.' '.date("H:i:s", strtotime($slotStartTime)),
					'modified' 		=> date("Y-m-d H:i:s")
					],
					['id IN' => $assignIds]
					);

					$pendingRows = $remainingRows;
					$cntrEVSCH += count($assignIds);
					$resetTime = 0;
					$finish_time = $slotFinishTime;
					$start_time = $slotStartTime;
				}

				if ($windowExceeded) {
					continue;
				}
				
				//exit;
			
			}
			
			
		}
		
		//$this->Flash->success('Scheduling done for category 4.');
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'startschedulec2', $convention_season_slug]);
		
	}
	
	public function fillgroupuserids($convention_season_slug=null)
	{
		$updateDateTime = date("Y-m-d H:i:s");
		
		// To get convention season details
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		// Now fetch group events from Schedulings
		$condGroupScht = array();
		$condGroupScht[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND 
			Schedulingtimings.user_type = 'School'  AND 
			(Schedulingtimings.group_name != '' OR  Schedulingtimings.group_name != NULL) AND
			(Schedulingtimings.group_name_opponent != '' OR  Schedulingtimings.group_name_opponent != NULL) AND
			Schedulingtimings.user_id > 0 AND Schedulingtimings.user_id_opponent > 0 AND
			Schedulingtimings.is_bye != 1 
		)";
		
		// Fetch each schedule and check if there is any group name assigned
		$schGroup = $this->Schedulingtimings
					->find()
					->where($condGroupScht)
					->order(["Schedulingtimings.id" => "ASC"])
					->all();
		//$this->prx($schGroup);
		foreach($schGroup as $schrecord)
		{
			// Now for each record, we need to get users of each group and group_opponent and fillgroupuserids
			
			// 1. First do for user_id, now fetch all users of this group for this user_id and this event
			$groupUsersID = $this->Crstudentevents
							->find()
							->where(
								[
									'conventionseason_id' 	=> $conventionSD->id,
									'user_id' 				=> $schrecord->user_id,
									'event_id' 				=> $schrecord->event_id,
									'group_name' 			=> $schrecord->group_name,
								]
							)
							->select('student_id')
							->order(["Crstudentevents.id" => "ASC"])
							->all();
			$studentIds = $groupUsersID->extract('student_id')->toArray();
			//$this->prx($studentIds);
			if(count($studentIds))
			{
				// Update record
				$this->Schedulingtimings->updateAll(
					[
						'group_name_user_ids' => implode(",",$studentIds)
					], 
					[
						"id" => $schrecord->id
					]
				);
			}
			
			
			
			// 2. Now do for user_id_opponent, now fetch all users of this group for this user_id_opponent and this event
			$groupUsersIDOpponent = $this->Crstudentevents
							->find()
							->where(
								[
									'conventionseason_id' 	=> $conventionSD->id,
									'user_id' 				=> $schrecord->user_id_opponent,
									'event_id' 				=> $schrecord->event_id,
									'group_name' 			=> $schrecord->group_name_opponent,
								]
							)
							->select('student_id')
							->order(["Crstudentevents.id" => "ASC"])
							->all();
			$studentIdsOpponent = $groupUsersIDOpponent->extract('student_id')->toArray();
			//$this->prx($studentIdsOpponent);
			if(count($studentIdsOpponent))
			{
				// Update record
				$this->Schedulingtimings->updateAll(
					[
						'group_name_opponent_user_ids' => implode(",",$studentIdsOpponent)
					], 
					[
						"id" => $schrecord->id
					]
				);
			}
		}
		//exit;
		
		// Now check for conflicts
		$this->redirect(['controller' => 'schedulingtimings', 'action' => 'listconflicts', $convention_season_slug]);
	}
	
	
	public function listconflicts($convention_season_slug=null)
	{
		// First we need to collect all students list of all schools
		$conventionSD = $this->getConventionSeasonBySlug($convention_season_slug);
		
		// To get list of all conflict
		$condSchList = array();
		$condSchList[] = "(
			Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND 
			Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND 
			Schedulingtimings.season_id = '".$conventionSD->season_id."' AND 
			Schedulingtimings.season_year = '".$conventionSD->season_year."' AND 
			Schedulingtimings.user_type = 'Student' AND 
			Schedulingtimings.user_id > 0 AND 
			Schedulingtimings.is_bye != 1 
			
		)";
		// Fetch each schedule and check if there is any group name assigned
		$schedulingtimings = $this->Schedulingtimings
			->find()
			->where($condSchList)
			->order(["Schedulingtimings.id" => "ASC"])
			->all();
		
		// Step 2: Normalize - build a mapping of user_id → their schedules
		$userSchedules = [];
		
		
		foreach($schedulingtimings as $schrecord)
		{
			$day 	= $schrecord->day;
			$start 	= strtotime($schrecord->start_time);
			$end   	= strtotime($schrecord->finish_time);

			// Direct user_id
			if ($schrecord->user_id) {
				$userSchedules[$schrecord->user_id][] = [
					'id' => $schrecord->id,
					'day' => $day,
					'start' => $start,
					'end' => $end
				];
			}
			
			// Direct user_id_opponent
			if ($schrecord->user_id_opponent) {
				$userSchedules[$schrecord->user_id_opponent][] = [
					'id' => $schrecord->id,
					'day' => $day,
					'start' => $start,
					'end' => $end
				];
			}

			// Group members
			foreach (['group_name_user_ids', 'group_name_opponent_user_ids'] as $col) {
				if (!empty($row[$col])) {
					$ids = array_map('trim', explode(',', $row[$col]));
					foreach ($ids as $uid) {
						if ($uid > 0) {
							$userSchedules[$uid][] = [
								'id' => $row['id'],
								'day' => $day,
								'start' => $start,
								'end' => $end
							];
						}
					}
				}
			}
			
		}
		
		// Step 3: Detect conflicts
		$conflicts = [];

		foreach ($userSchedules as $uid => $entries) {
			// Compare each pair of schedules for same user
			for ($i = 0; $i < count($entries); $i++) {
				for ($j = $i + 1; $j < count($entries); $j++) {
					$a = $entries[$i];
					$b = $entries[$j];

					if ($a['day'] == $b['day']) {
						// Check overlap: (startA < endB) and (endA > startB)
						if ($a['start'] < $b['end'] && $a['end'] > $b['start']) {
							$conflicts[$uid][] = [
								'schedule1' => $a['id'],
								'schedule2' => $b['id']
							];
						}
					}
				}
			}
		}
		
		//$this->prx($conflicts);

		// Step 4: Output
		$conflictUIDS 		= [];
		$conflictDBAutoID 	= [];
		foreach ($conflicts as $uid => $conflictList) {
			$conflictUIDS[] = $uid;
			
			// Get db ids of schedules
			foreach ($conflictList as $row) {
				$conflictDBAutoID[] = $row['schedule1'];
				$conflictDBAutoID[] = $row['schedule2'];
			}
		}
		
		$finalDBAutoIDUnique = array_values(array_unique($conflictDBAutoID));
		
		$msG = 'Scheduling completed successfully.';
		
		//$this->prx($conflictUIDS);
		
		// Save conflicted user ids in database
		if(count($conflictUIDS)>0)
		{
			$this->Schedulings->updateAll(['conflict_user_ids' => implode(",",$conflictUIDS)], ["conventionseasons_id" => $conventionSD->id]);
			$msG .= ' There are some conflicts found. Click on resolve conflict button below and resolve conflicts.';
		}
		
		
		// Now filter group db ids where conflict found
		if(count($finalDBAutoIDUnique)>0)
		{
			$finalGroupSchDBIDs = array();
			// filter group ids only and save to db
			foreach($finalDBAutoIDUnique as $group_db_id)
			{
				// check if its a group id
				$checkGroupGame = $this->Schedulingtimings->find()->where(['Schedulingtimings.id' => $group_db_id])->first();
				if($checkGroupGame->user_type == 'School')
				{
					$finalGroupSchDBIDs[] = $group_db_id;
				}
			}
			// Now update group record db auto ids to db
			if(count($finalGroupSchDBIDs)>0)
			{
				$this->Schedulings->updateAll(['conflict_user_ids_group' => implode(",",$finalGroupSchDBIDs)], ["conventionseasons_id" => $conventionSD->id]);
			}
		}
		
		$this->Flash->success($msG);
		$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
	}
	
	
	
	
	
	
	
	public function conflictdone($convention_season_slug=null) {
		
		$this->Flash->success('Scheduling completed successfully. Overlapping and conflicts removed successfully.');
		
		$this->redirect(['controller' => 'schedulings', 'action' => 'schedulecategory', $convention_season_slug]);
		
	}

	private function ensureSchedulingWindowIsValid($schedulingsD, $convention_season_slug)
	{
		$startDate = !empty($schedulingsD->start_date) ? date('Y-m-d', strtotime($schedulingsD->start_date)) : null;
		$actualFirstDay = $this->getWeekDayFromDate($startDate);
		$configuredFirstDay = (string)($schedulingsD->first_day ?? '');
		$numberOfDays = (int)($schedulingsD->number_of_days ?? 0);

		if ($numberOfDays < 1) {
			$this->Flash->error('Scheduling wizard configuration is incomplete. Number of Days must be at least 1 before schedules can be generated.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if (!$startDate || !$actualFirstDay || $configuredFirstDay === '') {
			$this->Flash->error('Scheduling wizard configuration is incomplete. Please review the Start Date and First Day before generating schedules.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		if ($actualFirstDay !== $configuredFirstDay) {
			$this->Flash->error('Scheduling wizard mismatch: Start Date falls on '.$actualFirstDay.', but First Day is set to '.$configuredFirstDay.'. Please fix the wizard before generating schedules.');
			return $this->redirect(['controller' => 'schedulings', 'action' => 'wizard', $convention_season_slug]);
		}

		return null;
	}

	private function applyNextConventionDay(&$schDay, &$schStartDate, &$cntrDays, $schedulingsD): bool
	{
		$numberOfDays = max(1, (int)($schedulingsD->number_of_days ?? 1));
		if ($cntrDays >= $numberOfDays) {
			return false;
		}

		$schStartDate = date('Y-m-d', strtotime($schStartDate . ' +1 day'));
		$schDay = $this->getWeekDayFromDate($schStartDate);
		$cntrDays++;

		return true;
	}

	private function flashConventionWindowExceeded($schedulingsD): void
	{
		$session = $this->request->getSession();
		if ($this->scheduleWindowWarningShown || $session->read('Scheduling.windowWarningShown')) {
			return;
		}

		$startDate = date('Y-m-d', strtotime($schedulingsD->start_date));
		$numberOfDays = max(1, (int)($schedulingsD->number_of_days ?? 1));
		$endDate = date('Y-m-d', strtotime($startDate.' +'.($numberOfDays - 1).' day'));
		$this->Flash->warning('Scheduling reached the configured convention window from '.date('D j M Y', strtotime($startDate)).' to '.date('D j M Y', strtotime($endDate)).'. Some items could not be placed inside the selected Number of Days.');
		$this->scheduleWindowWarningShown = true;
		$session->write('Scheduling.windowWarningShown', true);
	}

	private function getCategory4ConcurrentBatchSize($eventD): int
	{
		$eventIdNumber = (string)($eventD->event_id_number ?? '');
		if (in_array($eventIdNumber, ['003', '053'], true)) {
			return 40;
		}

		return 1;
	}

	private function hasUserSchedulingConflict($conventionSD, int $userId, string $slotDate, string $slotStartTime, string $slotFinishTime): bool
	{
		if ($userId <= 0 || $slotDate === '') {
			return false;
		}

		$condConflict = [];
		$condConflict[] = "(Schedulingtimings.conventionseasons_id = '".$conventionSD->id."' AND Schedulingtimings.convention_id = '".$conventionSD->convention_id."' AND Schedulingtimings.season_id = '".$conventionSD->season_id."' AND Schedulingtimings.season_year = '".$conventionSD->season_year."')";
		$condConflict[] = "(Schedulingtimings.user_id = '".$userId."' AND Schedulingtimings.start_time IS NOT NULL AND Schedulingtimings.finish_time IS NOT NULL)";
		$condConflict[] = "(DATE(Schedulingtimings.sch_date_time) = '".$slotDate."')";
		$condConflict[] = "(Schedulingtimings.finish_time > '".$slotStartTime."' AND Schedulingtimings.start_time < '".$slotFinishTime."')";

		$conflictCount = $this->Schedulingtimings->find()->where($condConflict)->count();
		return $conflictCount > 0;
	}
	
	
	/**
	 * Validate a time slot against all break periods (lunch, judging, sports day, events-after-sport).
	 * Loops until the slot is clean — fixes the "blind jump" bug where sequential break checks
	 * could skip over a break after being pushed past another one.
	 */
	private function findValidSlot($start_time, $finish_time, $schDay, $schStartDate, $cntrDays, $normal_starting_time, $normal_finish_time, $eventSetupRoundJudTime, $schedulingsD, $lunch_time_start, $lunch_time_end, $roomID = null, $eventID = null)
	{
		$maxIterations = 20;
		$iteration = 0;
		$windowExhausted = false;
		$roomAvailableFrom = null;
		$roomAvailableTo = null;
		$eventAvailableFrom = null;
		$eventAvailableTo = null;

		if (!empty($eventID)) {
			$eventTweak = $this->Schedulingeventtweaks->find()->where([
				'Schedulingeventtweaks.conventionseasons_id' => $schedulingsD->conventionseasons_id,
				'Schedulingeventtweaks.event_id' => (int)$eventID,
			])->first();
			if ($eventTweak) {
				if (!empty($eventTweak->available_from_time)) {
					$eventAvailableFrom = date('H:i:s', strtotime($eventTweak->available_from_time));
				}
				if (!empty($eventTweak->available_to_time)) {
					$eventAvailableTo = date('H:i:s', strtotime($eventTweak->available_to_time));
				}
			}
		}

		if (!empty($roomID)) {
			$roomD = $this->Conventionrooms->find()->where(['Conventionrooms.id' => $roomID])->first();
			if ($roomD) {
				if (!empty($roomD->available_from)) {
					$roomAvailableFrom = date('H:i:s', strtotime($roomD->available_from));
				}
				if (!empty($roomD->available_to)) {
					$roomAvailableTo = date('H:i:s', strtotime($roomD->available_to));
				}
			}
		}
		
		do {
			$slotChanged = false;
			$iteration++;

			if ($eventAvailableFrom !== null && strtotime($start_time) < strtotime($eventAvailableFrom)) {
				$start_time = $eventAvailableFrom;
				$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
				$slotChanged = true;
			}

			if ($eventAvailableTo !== null && strtotime($finish_time) > strtotime($eventAvailableTo)) {
				if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
					$windowExhausted = true;
					break;
				}
				$normal_starting_time = date("H:i:s",strtotime($schedulingsD->normal_starting_time));
				$normal_finish_time = date("H:i:s",strtotime($schedulingsD->normal_finish_time));
				$start_time = $eventAvailableFrom !== null ? $eventAvailableFrom : $normal_starting_time;
				$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
				$slotChanged = true;
			}
			
			/* Lunch break check */
			if( (strtotime($start_time)>=strtotime($lunch_time_start) && strtotime($start_time)<=strtotime($lunch_time_end)) || 
				(strtotime($finish_time)>=strtotime($lunch_time_start) && strtotime($finish_time)<=strtotime($lunch_time_end)))
			{
				$start_time 	= $lunch_time_end;
				$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($lunch_time_end)));
				$slotChanged = true;
				
				if(strtotime($finish_time)>strtotime($normal_finish_time))
				{
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
					$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
					$start_time 	= $normal_starting_time;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
				}
			}
			
			/* Judging breaks check */
			if($schedulingsD->judging_breaks_yes_no == 1)
			{
				// Morning break
				$jb_morning_start 	= date("H:i:s",strtotime($schedulingsD->judging_breaks_morning_break_starting_time));
				$jb_morning_end 	= date("H:i:s",strtotime($schedulingsD->judging_breaks_morning_break_finish_time));
				
				if( (strtotime($start_time)>=strtotime($jb_morning_start) && strtotime($start_time)<=strtotime($jb_morning_end)) || 
				(strtotime($finish_time)>=strtotime($jb_morning_start) && strtotime($finish_time)<=strtotime($jb_morning_end)))
				{
					$start_time 	= $jb_morning_end;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($jb_morning_end)));
					$slotChanged = true;
				}
				
				if(strtotime($finish_time)>=strtotime($normal_finish_time))
				{
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
					$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
					$start_time 	= $normal_starting_time;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
					$slotChanged = true;
				}
				
				// Afternoon break
				$jb_afternoon_start = date("H:i:s",strtotime($schedulingsD->judging_breaks_afternoon_break_start_time));
				$jb_afternoon_end 	= date("H:i:s",strtotime($schedulingsD->judging_breaks_afternoon_break_finish_time));
				
				if( (strtotime($start_time)>=strtotime($jb_afternoon_start) && strtotime($start_time)<=strtotime($jb_afternoon_end)) || 
				(strtotime($finish_time)>=strtotime($jb_afternoon_start) && strtotime($finish_time)<=strtotime($jb_afternoon_end)))
				{
					$start_time 	= $jb_afternoon_end;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($jb_afternoon_end)));
					$slotChanged = true;
				}
				
				if(strtotime($finish_time)>=strtotime($normal_finish_time))
				{
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
					$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
					$start_time 	= $normal_starting_time;
					$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
					$slotChanged = true;
				}
			}
			
			/* Sports day check */
			if($schedulingsD->sports_day_yes_no == 1)
			{
				$sports_day					= $schedulingsD->sports_day;
				$sports_day_starting_time	= date("H:i:s",strtotime($schedulingsD->sports_day_starting_time));
				$sports_day_finish_time		= date("H:i:s",strtotime($schedulingsD->sports_day_finish_time));
				
				if($sports_day == $schDay)
				{
					if( (strtotime($start_time)>=strtotime($sports_day_starting_time) && strtotime($start_time)<=strtotime($sports_day_finish_time)) || 
					(strtotime($finish_time)>=strtotime($sports_day_starting_time) && strtotime($finish_time)<=strtotime($sports_day_finish_time)))
					{
						$start_time 	= $sports_day_finish_time;
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($sports_day_finish_time)));
						$slotChanged = true;
					}
					
					if(strtotime($finish_time)>=strtotime($normal_finish_time))
					{
						if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
							$windowExhausted = true;
							break;
						}
						$normal_starting_time 	= date("H:i:s",strtotime($schedulingsD->normal_starting_time));
						$normal_finish_time 	= date("H:i:s",strtotime($schedulingsD->normal_finish_time));
						$start_time 	= $normal_starting_time;
						$finish_time 	= date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($normal_starting_time)));
						$slotChanged = true;
					}
				}
			}
			
			/* Events after sport are allowed in the configured post-sport window.
			 * The lunch and sports-day checks above already move slots out of blocked periods.
			 */

			/* Room availability window check */
			if (!empty($roomID) && ($roomAvailableFrom || $roomAvailableTo)) {
				$effectiveRoomStart = $roomAvailableFrom ?: $normal_starting_time;
				$effectiveRoomEnd = $roomAvailableTo ?: $normal_finish_time;

				if (strtotime($start_time) < strtotime($effectiveRoomStart)) {
					$start_time = $effectiveRoomStart;
					$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					$slotChanged = true;
				}

				if (strtotime($finish_time) > strtotime($effectiveRoomEnd)) {
					if (!$this->applyNextConventionDay($schDay, $schStartDate, $cntrDays, $schedulingsD)) {
						$windowExhausted = true;
						break;
					}
					$normal_starting_time = date("H:i:s", strtotime($schedulingsD->normal_starting_time));
					$normal_finish_time = date("H:i:s", strtotime($schedulingsD->normal_finish_time));
					$start_time = (strtotime($effectiveRoomStart) > strtotime($normal_starting_time)) ? $effectiveRoomStart : $normal_starting_time;
					$finish_time = date("H:i:s", strtotime('+ '.$eventSetupRoundJudTime.' minutes', strtotime($start_time)));
					$slotChanged = true;
				}
			}
			
		} while ($slotChanged && $iteration < $maxIterations);
		
		return [
			'start_time' => $start_time,
			'finish_time' => $finish_time,
			'schDay' => $schDay,
			'schStartDate' => $schStartDate,
			'cntrDays' => $cntrDays,
			'normal_starting_time' => $normal_starting_time,
			'normal_finish_time' => $normal_finish_time,
			'window_exhausted' => $windowExhausted,
		];
	}

	private function getConventionSeasonBySlug($conventionSeasonSlug)
	{
		return $this->Conventionseasons
			->find()
			->where(['Conventionseasons.slug' => $conventionSeasonSlug])
			->contain(['Conventions'])
			->first();
	}

}

?>
