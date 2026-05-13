<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Datasource\ConnectionManager;

class JudgeevaluationsController extends AppController {

    public $paginate = ['limit' => 50, 'order' => ['Conventions.name' => 'asc']];
    public $components = array('PImage');

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
		
		$this->Conventions = $this->loadModel('Conventions');
		$this->Conventionseasons = $this->loadModel('Conventionseasons');
		$this->Seasons = $this->loadModel('Seasons');
		$this->Events = $this->loadModel('Events');
		$this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
		$this->Conventionregistrations = $this->loadModel('Conventionregistrations');
		$this->Eventsubmissions = $this->loadModel('Eventsubmissions');
		$this->Judgeevaluationmarks = $this->loadModel('Judgeevaluationmarks');
        $this->JudgingAssignments = $this->loadModel('JudgingAssignments');
        $this->Users = $this->loadModel('Users');
    }
	
	public function index() {

        $this->set('title', ADMIN_TITLE . 'Judge Evaluations');
        $this->viewBuilder()->setLayout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('judgeEvaluationsList', '1');
		
		$conventionsDD = $this->Conventions->find()->where([])->order(['Conventions.name' => 'ASC'])->all()->combine('id', 'name')->toArray();
		$this->set('conventionsDD', $conventionsDD);
		
		$seasonsDD = $this->Seasons->find()->where([])->order(['Seasons.season_year' => 'DESC'])->all()->combine('season_year', 'season_year')->toArray();
		$this->set('seasonsDD', $seasonsDD);
		
		$eventsDD = array();
		$eventsList = $this->Events->find()->where([])->order(['Events.event_name' => 'DESC'])->all();
		foreach($eventsList as $eventl)
		{
			$eventsDD[$eventl->id] = $eventl->event_name. ' ('.$eventl->event_id_number.')';
		}
		$this->set('eventsDD', $eventsDD);
		
		$separator = array();
        $condition = array();
		
		// to check if conv season selected from header then filter list
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
        if($sess_admin_header_season_id<=0)
        {
            $this->Flash->error('Please select a Convention Season from the header first.');
            return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
        }
        $condition[] = "(Judgeevaluations.conventionseason_id = '".$sess_admin_header_season_id."')";
		
		if ($this->request->is('post')) {
            if ($this->request->getData('action') !== null) {
                $idList = implode(',', $this->request->getData('chkRecordId'));
                $action = $this->request->getData('action');
                if ($idList) {
                    if ($action == "Activate") {
                        $this->Judgeevaluations->updateAll(['status' => '1'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are activated successfully.');
                    } elseif ($action == "Deactivate") {
                        $this->Judgeevaluations->updateAll(['status' => '0'], ["id IN ($idList)"]);
                        $this->Flash->success('Records are deactivated successfully.');
                    } elseif ($action == "Delete") {
                        $this->Judgeevaluations->deleteAll(["id IN ($idList)"]);
                        $this->Flash->success('Records are deleted successfully.');
                    }
                }
            }

            if ($this->request->getData('Judgeevaluations.convention_id') !== null && $this->request->getData('Judgeevaluations.convention_id') != '') {
                $convention_id = trim($this->request->getData('Judgeevaluations.convention_id'));
            }
			if ($this->request->getData('Judgeevaluations.season_year') !== null && $this->request->getData('Judgeevaluations.season_year') != '') {
                $season_year = trim($this->request->getData('Judgeevaluations.season_year'));
            }
			if ($this->request->getData('Judgeevaluations.event_id') !== null && $this->request->getData('Judgeevaluations.event_id') != '') {
                $event_id = trim($this->request->getData('Judgeevaluations.event_id'));
            }
        } elseif ($this->request->getAttribute('params')) {
            if (!empty($this->request->getParam('pass', []))) {
                $searchArr = $this->request->getParam('pass');
                foreach ($searchArr as $val) {
                    if (strpos($val, ":") !== false) {
                        $vars = explode(":", $val);
                        ${$vars[0]} = urldecode($vars[1]);
                    }
                }
            }
        }

        /* if (isset($convention_id) && $convention_id != '') {
            $separator[] = 'convention_id:' . urlencode($convention_id);
            $condition[] = "(Judgeevaluations.convention_id = '".addslashes($convention_id)."')";
            $this->set('convention_id', $convention_id);
        }
		if (isset($season_year) && $season_year != '') {
            $separator[] = 'season_year:' . urlencode($season_year);
            $condition[] = "(Judgeevaluations.season_year = '".addslashes($season_year)."')";
            $this->set('season_year', $season_year);
        } */
		
		if (isset($event_id) && $event_id != '') {
            $separator[] = 'event_id:' . urlencode($event_id);
            $condition[] = "(Judgeevaluations.event_id = '".addslashes($event_id)."')";
            $this->set('event_id', $event_id);
        }
		
        /* //$this->prx($condition);exit;
        $separator = implode("/", $separator);
        $this->set('separator', $separator);
        $query = $this->Judgeevaluations->find()
            ->contain(['Eventsubmissions','Conventionregistrations','Conventions','Events','Students','Schools','Judge','Judgeevaluationmarks'])
            ->where($condition);
        $this->paginate = ['limit' => 1000000000];
        $this->set('judgeevaluations', $this->paginate($query));
        if ($this->request->is("ajax")) {
            $this->viewBuilder()->setLayout(($this->request->is("ajax")) ? "" : "default");
            $this->viewBuilder()->setTemplatePath('Element' . DS . 'Admin/Judgeevaluations');
            $this->render('index');
        } */
		
        $latestEvalIdByJudge = [];
        $latestEvalIdRows = $this->Judgeevaluations->find()
            ->select(['Judgeevaluations.id', 'Judgeevaluations.uploaded_by_user_id'])
            ->where($condition)
            ->order(['Judgeevaluations.created' => 'DESC'])
            ->all();
        foreach($latestEvalIdRows as $erow) {
            $judgeId = (int)$erow->uploaded_by_user_id;
            if($judgeId > 0 && !isset($latestEvalIdByJudge[$judgeId])) {
                $latestEvalIdByJudge[$judgeId] = (int)$erow->id;
            }
        }

        // Build assigned events per judge from Conventionregistrations.judges_event_ids
        $assignedEventsByJudge = [];
        $convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
        if(!empty($convSeasonD)) {
            $judgeRegs = $this->Conventionregistrations->find()
                ->contain(['Users'])
                ->where([
                    'Conventionregistrations.convention_id' => $convSeasonD->convention_id,
                    'Conventionregistrations.season_id'     => $convSeasonD->season_id,
                    'Conventionregistrations.season_year'   => $convSeasonD->season_year,
                ])
                ->all();
            foreach($judgeRegs as $reg) {
                $userData = !empty($reg->Users) ? $reg->Users : (!empty($reg->user) ? $reg->user : null);
                if(empty($userData)) continue;
                $isJudge = ($userData['user_type'] == 'Judge') || ($userData['user_type'] == 'Teacher_Parent' && (int)$userData['is_judge'] === 1);
                if(!$isJudge) continue;
                $userId = (int)$reg->user_id;
                if($userId <= 0) continue;
                if(!empty($reg->judges_event_ids)) {
                    foreach(explode(',', (string)$reg->judges_event_ids) as $rawId) {
                        $eventId = (int)trim($rawId);
                        if($eventId > 0) {
                            $assignedEventsByJudge[$userId][$eventId] = true;
                        }
                    }
                }
            }
        }

        $assignedCountByJudge = [];
        foreach($assignedEventsByJudge as $judgeId => $eventsMap) {
            $assignedCountByJudge[(int)$judgeId] = count($eventsMap);
        }

        // Fetch event names for all assigned event IDs
        $allAssignedEventIds = [];
        foreach($assignedEventsByJudge as $eventsMap) {
            $allAssignedEventIds = array_merge($allAssignedEventIds, array_keys($eventsMap));
        }
        $allAssignedEventIds = array_unique($allAssignedEventIds);
        $eventNamesById = [];
        if(!empty($allAssignedEventIds)) {
            $eventRows = $this->Events->find()->where(['Events.id IN' => $allAssignedEventIds])->all();
            foreach($eventRows as $evrow) {
                $eventNamesById[(int)$evrow->id] = $evrow->event_name . ' (' . $evrow->event_id_number . ')';
            }
        }

        // Build judged event IDs per judge
        $judgedCountByJudge = [];
        $judgedEventIdsByJudge = [];
        $judgedAgg = $this->Judgeevaluations->find();
        $judgedAgg = $judgedAgg
            ->select([
                'Judgeevaluations.uploaded_by_user_id',
                'Judgeevaluations.event_id',
            ])
            ->where($condition)
            ->group(['Judgeevaluations.uploaded_by_user_id', 'Judgeevaluations.event_id'])
            ->all();
        foreach($judgedAgg as $jrow) {
            $judgeId = (int)$jrow->uploaded_by_user_id;
            $eventId = (int)$jrow->event_id;
            if($judgeId > 0 && $eventId > 0) {
                if (!isset($judgedEventIdsByJudge[$judgeId])) $judgedEventIdsByJudge[$judgeId] = [];
                $judgedEventIdsByJudge[$judgeId][$eventId] = true;
            }
        }
        foreach($judgedEventIdsByJudge as $judgeId => $evMap) {
            $judgedCountByJudge[$judgeId] = count($evMap);
        }

        $latestEvaluationByJudge = [];
        $evaluationsByJudgeAndEvent = [];
        if(!empty($latestEvalIdByJudge)) {
            $latestEvalIds = array_values($latestEvalIdByJudge);
            $latestDetailedRows = $this->Judgeevaluations->find()
                ->where(['Judgeevaluations.id IN' => $latestEvalIds])
                ->contain(['Eventsubmissions','Conventions','Events','Students','Schools','Judge','Judgeevaluationmarks'])
                ->all();
            foreach($latestDetailedRows as $ldrow) {
                $latestEvaluationByJudge[(int)$ldrow->uploaded_by_user_id] = $ldrow;
            }
        }
        
        // Fetch all evaluations per judge+event for the accordion icons
        $allEvaluationsForJudges = $this->Judgeevaluations->find()
            ->where($condition)
            ->order(['Judgeevaluations.created' => 'DESC'])
            ->all();
        foreach($allEvaluationsForJudges as $evalRow) {
            $judgeId = (int)$evalRow->uploaded_by_user_id;
            $eventId = (int)$evalRow->event_id;
            if($judgeId > 0 && $eventId > 0) {
                if(!isset($evaluationsByJudgeAndEvent[$judgeId])) {
                    $evaluationsByJudgeAndEvent[$judgeId] = [];
                }
                if(!isset($evaluationsByJudgeAndEvent[$judgeId][$eventId])) {
                    $evaluationsByJudgeAndEvent[$judgeId][$eventId] = $evalRow;
                }
            }
        }

        $judgeIds = array_unique(array_merge(array_keys($assignedCountByJudge), array_keys($judgedCountByJudge)));
        sort($judgeIds);

        $judgeNamesById = [];
        if(!empty($judgeIds)) {
            $judgeUsers = $this->Users->find()->where(['Users.id IN' => $judgeIds])->all();
            foreach($judgeUsers as $ju) {
                $judgeNamesById[(int)$ju->id] = trim($ju->first_name.' '.$ju->last_name);
            }
        }

        $evaluationUpdateRows = [];
        foreach($judgeIds as $judgeId) {
            $assignedCount = isset($assignedCountByJudge[$judgeId]) ? (int)$assignedCountByJudge[$judgeId] : 0;
            $judgedCount = isset($judgedCountByJudge[$judgeId]) ? (int)$judgedCountByJudge[$judgeId] : 0;
            $notJudgedCount = max($assignedCount - $judgedCount, 0);

            $latestEval = isset($latestEvaluationByJudge[$judgeId]) ? $latestEvaluationByJudge[$judgeId] : null;
            $schoolName = '-';
            $studentName = '-';
            $submittedDate = null;
            if(!empty($latestEval)) {
                if(!empty($latestEval->Schools['first_name'])) {
                    $schoolName = $latestEval->Schools['first_name'];
                }
                if(!empty($latestEval->Eventsubmissions['student_id']) && !empty($latestEval->Students)) {
                    $studentName = trim($latestEval->Students['first_name'].' '.$latestEval->Students['middle_name'].' '.$latestEval->Students['last_name']);
                } elseif(!empty($latestEval->Eventsubmissions['group_name'])) {
                    $studentName = 'Group '.$latestEval->Eventsubmissions['group_name'];
                }
                $submittedDate = $latestEval->created;
            }

            $assignedEventList = [];
            $remainingEventList = [];
            $judgedEventIds = isset($judgedEventIdsByJudge[$judgeId]) ? array_keys($judgedEventIdsByJudge[$judgeId]) : [];
            $assignedEventIds = !empty($assignedEventsByJudge[$judgeId]) ? array_keys($assignedEventsByJudge[$judgeId]) : [];
            foreach($assignedEventIds as $eid) {
                $ename = isset($eventNamesById[$eid]) ? $eventNamesById[$eid] : ('Event #'.$eid);
                $assignedEventList[] = ['id' => $eid, 'name' => $ename];
                if (!in_array($eid, $judgedEventIds)) {
                    $remainingEventList[] = ['id' => $eid, 'name' => $ename];
                }
            }
            // Sort by name
            usort($assignedEventList, function($a, $b) { return strcasecmp($a['name'], $b['name']); });
            usort($remainingEventList, function($a, $b) { return strcasecmp($a['name'], $b['name']); });

            $evaluationUpdateRows[] = [
                'judge_id' => $judgeId,
                'judge_name' => isset($judgeNamesById[$judgeId]) ? $judgeNamesById[$judgeId] : ('Judge #'.$judgeId),
                'registered_events' => $assignedCount,
                'assigned_event_list' => $assignedEventList,
                'remaining_event_list' => $remainingEventList,
                'event_evaluations' => isset($evaluationsByJudgeAndEvent[$judgeId]) ? $evaluationsByJudgeAndEvent[$judgeId] : [],
                'judged_events' => $judgedCount,
                'not_judged_events' => $notJudgedCount,
                'school_name' => $schoolName,
                'student_name' => $studentName,
                'submitted_date' => $submittedDate,
                'evaluation' => $latestEval,
            ];
        }

        usort($evaluationUpdateRows, function($a, $b) {
            return strcasecmp((string)$a['judge_name'], (string)$b['judge_name']);
        });

        $this->set('evaluationUpdateRows', $evaluationUpdateRows);
    }
	
	public function judgedetail($judge_id = null) {
		$this->set('title', ADMIN_TITLE . 'Judge Details');
		$this->viewBuilder()->setLayout('admin');
		$this->set('judgeEvaluations', '1');
		$this->set('judgeEvaluationsList', '1');
		
		if (!$judge_id) {
			$this->Flash->error('Invalid judge ID.');
			return $this->redirect(['action' => 'index']);
		}
		
		$judge_id = (int)$judge_id;
		
		// Get session convention season
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		if($sess_admin_header_season_id <= 0) {
			$this->Flash->error('Please select a Convention Season from the header first.');
			return $this->redirect(['controller' => 'admins', 'action' => 'dashboard']);
		}
		
		$condition = ["(Judgeevaluations.conventionseason_id = '".$sess_admin_header_season_id."')"];
		
		// Get judge name
		$judge = $this->Users->find()->where(['Users.id' => $judge_id])->first();
		if (!$judge) {
			$this->Flash->error('Judge not found.');
			return $this->redirect(['action' => 'index']);
		}
		$judgeName = trim($judge->first_name.' '.$judge->last_name);
		
		// Get convention season for context
		$convSeasonD = $this->Conventionseasons->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])->first();
		
		// Get assigned events for this judge
		$assignedEventsList = [];
		$assignedEventIds = [];
		if(!empty($convSeasonD)) {
			$judgeRegs = $this->Conventionregistrations->find()
				->where([
					'Conventionregistrations.user_id' => $judge_id,
					'Conventionregistrations.convention_id' => $convSeasonD->convention_id,
					'Conventionregistrations.season_id'     => $convSeasonD->season_id,
					'Conventionregistrations.season_year'   => $convSeasonD->season_year,
				])
				->all();
			foreach($judgeRegs as $reg) {
				if(!empty($reg->judges_event_ids)) {
					foreach(explode(',', (string)$reg->judges_event_ids) as $rawId) {
						$eventId = (int)trim($rawId);
						if($eventId > 0) {
							$assignedEventIds[$eventId] = true;
						}
					}
				}
			}
		}
		
		// Get event details for assigned events
		if(!empty($assignedEventIds)) {
			$eventIds = array_keys($assignedEventIds);
			$eventRows = $this->Events->find()->where(['Events.id IN' => $eventIds])->all();
			foreach($eventRows as $evrow) {
				$assignedEventsList[] = [
					'id' => (int)$evrow->id,
					'name' => $evrow->event_name . ' (' . $evrow->event_id_number . ')'
				];
			}
			usort($assignedEventsList, function($a, $b) { 
				return strcasecmp($a['name'], $b['name']); 
			});
		}
		
		// Get judged events for this judge
		$judgedEventIds = [];
		$judgedAgg = $this->Judgeevaluations->find();
		$judgedAgg = $judgedAgg
			->select([
				'Judgeevaluations.event_id',
			])
			->where($condition + ['Judgeevaluations.uploaded_by_user_id' => $judge_id])
			->group(['Judgeevaluations.event_id'])
			->all();
		foreach($judgedAgg as $jrow) {
			$judgedEventIds[(int)$jrow->event_id] = true;
		}
		
		// Fetch all evaluations per event for this judge
		$evaluationsByEvent = [];
		$allEvaluationsForJudge = $this->Judgeevaluations->find()
			->where($condition + ['Judgeevaluations.uploaded_by_user_id' => $judge_id])
			->contain(['Eventsubmissions','Conventions','Events','Students','Schools','Judge','Judgeevaluationmarks'])
			->order(['Judgeevaluations.created' => 'DESC'])
			->all();
		foreach($allEvaluationsForJudge as $evalRow) {
			$eventId = (int)$evalRow->event_id;
			if($eventId > 0) {
				if(!isset($evaluationsByEvent[$eventId])) {
					$evaluationsByEvent[$eventId] = [];
				}
				$evaluationsByEvent[$eventId][] = $evalRow;
			}
		}
		
		// Build event details for display
		$eventDetails = [];
		foreach($assignedEventsList as $event) {
			$eventId = (int)$event['id'];
			$isJudged = isset($judgedEventIds[$eventId]);
			$evaluations = isset($evaluationsByEvent[$eventId]) ? $evaluationsByEvent[$eventId] : [];
			$eventDetails[] = [
				'id' => $eventId,
				'name' => $event['name'],
				'is_judged' => $isJudged,
				'evaluations' => $evaluations,
				'evaluation_count' => count($evaluations),
			];
		}
		
		$this->set('judge_id', $judge_id);
		$this->set('judge_name', $judgeName);
		$this->set('eventDetails', $eventDetails);
		$this->set('allEvaluations', $allEvaluationsForJudge);
	}
	
	public function removejudgeevaluation($evaluation_slug=null) {
		
		$judgeEvalD = $this->Judgeevaluations->find()->where(['Judgeevaluations.slug' => $evaluation_slug])->first();
		if($judgeEvalD)
		{
			$this->Judgeevaluations->deleteAll(["slug" => $evaluation_slug]);
			
			// remove evaluation questions marks
			$this->Judgeevaluationmarks->deleteAll(["judgeevaluation_id" => $judgeEvalD->id]);
			$this->Flash->success('Judge evaluation removed successfully.');
		}
		else
		{
			$this->Flash->error('Judge evaluation not found.');
		}
		
		$this->redirect(['controller' => 'judgeevaluations', 'action' => 'index']);
    }
	
	public function timesscoreedit($evaluation_slug = null) {
		
		$this->set('title', ADMIN_TITLE . 'Edit Times Score');
        $this->viewBuilder()->setLayout('admin');
        $this->set('judgeEvaluations', '1');
        $this->set('judgeEvaluationsList', '1');
		
        if ($evaluation_slug) {
            $judgeEvalD = $this->Judgeevaluations->find()->where(['Judgeevaluations.slug' => $evaluation_slug])->contain(['Events','Schools','Students'])->first();
			$this->set('judgeEvalD', $judgeEvalD);
            $uid = $judgeEvalD->id;
        }
		
        $judgeevaluations = $this->Judgeevaluations->get($uid);
        if ($this->request->is(['post', 'put']))
		{
			//$this->prx($this->request->getData());
			
			$this->Judgeevaluations->updateAll(
			[
				'time_score' 		=> $this->request->getData('Judgeevaluations.time_score'),
				'place' 			=> $this->request->getData('Judgeevaluations.place'),
				'withdraw_yes_no' 	=> $this->request->getData('Judgeevaluations.withdraw_yes_no'),
				'modified'			=> date("Y-m-d H:i:s")
			], 
			['slug' => $evaluation_slug]
			);
			
            $this->Flash->success('Times score updated successfully.');
            $this->redirect(['controller' => 'judgeevaluations', 'action' => 'index']);
        }
        $this->set('judgeevaluations', $judgeevaluations);
    }

}

?>
