<?php

namespace App\Controller\Admin;

use App\Controller\AppController;
use Cake\Core\Configure;
use Cake\Core\Configure\Engine\PhpConfig;
use Cake\Mailer\Mailer;

class AdminsController extends AppController {

    public $paginate = ['limit' => 1];
    public $components = array('PImage');

    public function initialize() {
        parent::initialize();
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') { // check admin login session, direct to admin login if session not active
            if (!$loggedAdminId && $action != "login" && $action != 'captcha') {
                $this->redirect(['action' => 'login']);
            }
        }
		
		$this->Emailtemplates = $this->loadModel('Emailtemplates');
		$this->Users = $this->loadModel('Users');
		$this->Seasons = $this->loadModel('Seasons');
		$this->Events = $this->loadModel('Events');
		$this->Conventions = $this->loadModel('Conventions');
		$this->Divisions = $this->loadModel('Divisions');
		$this->Settings = $this->loadModel('Settings');
		$this->Transactions = $this->loadModel('Transactions');
		$this->Conventionregistrations = $this->loadModel('Conventionregistrations');
		$this->Conventionregistrationstudents = $this->loadModel('Conventionregistrationstudents');
		$this->Conventionregistrationteachers = $this->loadModel('Conventionregistrationteachers');
		$this->Conventionseasonevents = $this->loadModel('Conventionseasonevents');
		$this->Conventionseasons = $this->loadModel('Conventionseasons');
    }

    public function login() {
        $this->set('title', ADMIN_TITLE . 'Admin Login');
        $this->viewBuilder()->setLayout('admin_login');

        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($loggedAdminId) {
            $this->redirect(['action' => 'dashboard']);
        }

        // echo Configure::version(); exit;

        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $admin = $this->Admins->patchEntity($admin, $this->request->getData());
            if (count($admin->getErrors()) == 0) {
                $userName = $this->request->getData('Admins.username');
                $password = $this->request->getData('Admins.password');
                $adminInfo = $this->Admins->find()->where(['Admins.username' => $userName])->first();
                if ($adminInfo) {
                    if ($adminInfo->status == 0) {
                        $this->Flash->error('Your account got temporary disabled.');
                    } elseif (!empty($adminInfo) && crypt($password, $adminInfo->password) == $adminInfo->password) {

                        if ($this->request->getData('Admins.remember') !== null && $this->request->getData('Admins.remember') == '1') {
                            setcookie("admin_username", $userName, time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_password", $password, time() + 60 * 60 * 24 * 100, "/");
                        } else {
                            setcookie("admin_username", '', time() + 60 * 60 * 24 * 100, "/");
                            setcookie("admin_username", '', time() + 60 * 60 * 24 * 100, "/");
                        }
                        $this->request->getSession()->write('admin_id', $adminInfo->id);
                        $this->request->getSession()->write('admin_username', $userName);
                        $this->redirect(['action' => 'dashboard']);
                    } else {
                        $this->Flash->error('Invalid username or password.');
                    }
                } else {
                    $this->Flash->error('Invalid username or password.');
                }
            } else {
                $this->Flash->error('Please below listed errors.');
            }
        } else {
            if (isset($_COOKIE["admin_username"]) && isset($_COOKIE["admin_password"])) {
                $this->request = $this->request->withData('Admins.username', $_COOKIE["admin_username"]);
                $this->request = $this->request->withData('Admins.password', $_COOKIE["admin_password"]);
                $this->request = $this->request->withData('Admins.remember', 1);
            }
        }
        $this->set('admin', $admin);
    }

    public function forgotPassword() {
        $this->set('title', ADMIN_TITLE . 'Forgot Password');
        $this->viewBuilder()->setLayout('admin_login');

        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $admin = $this->Admins->patchEntity($admin, $this->request->getData(), ['validate' => 'forgotPassword']);
            if (count($admin->getErrors()) == 0) {
                $email = $this->request->getData('Admins.email');
                $adminInfo = $this->Admins->find()->where(['Admins.email' => $email])->first();
                if ($adminInfo) {
                    $new_password = rand(1000000, 999999999);
                    $salt = uniqid(mt_rand(), true);
                    $password = crypt($new_password, '$2a$07$' . $salt . '$');
                    $this->Admins->updateAll(['password' => $password], ['id' => $adminInfo->id]);

                    $username = $adminInfo['username'];
                    $emailId = $adminInfo['email'];
                    
                    $emailtemplateMessage = $this->Emailtemplates->find()->where(['Emailtemplates.id' => '1'])->first();

                    $toRepArray = array('[!email!]', '[!username!]', '[!password!]', '[!HTTP_PATH!]', '[!SITE_TITLE!]');
                    $fromRepArray = array($emailId, $username, $new_password, HTTP_PATH, SITE_TITLE);

                    $subjectToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['subject']);
					$messageToSend = str_replace($toRepArray, $fromRepArray, $emailtemplateMessage['template']);
					
					//echo $messageToSend;exit;

                    $email = new Mailer();
                    $email->viewBuilder()->setTemplate('default')->setLayout('admintemplate');
                            $email->setEmailFormat('html')
                            ->setTo($emailId)
                            ->setFrom([MAIL_FROM => SITE_TITLE])
                            ->setSubject($subjectToSend)
                            ->setViewVars(['content_for_layout' => $messageToSend])
                            ->deliver();

                    $this->Flash->success('New admin password sent to admin email address.');
                    $this->redirect(['action' => 'login']);
                } else {
                    $this->Flash->error('Invalid email address, please enter correct email address.');
                }
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
    }

    public function logout() {
        session_destroy();
        $this->Flash->success('Logout successfully.');
        $this->redirect(['action' => 'login']);
    }

    public function headerchooseconvseas() {
		
		//$this->prx($this->request->getData());
		
		$admin_header_season_id = $this->request->getData('admin_header_season_id');
		
		if($admin_header_season_id>0)
		{
			$convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$admin_header_season_id])->contain(['Conventions'])->first();
			
			if($convSD)
			{
				$this->request->getSession()->write('sess_admin_header_season_id', $admin_header_season_id);
				
				$this->redirect(['controller' => 'conventions', 'action' => 'seasons', $convSD->Conventions['slug']]);
			}
		}
		else
		{
			$this->request->getSession()->write('sess_admin_header_season_id', 0);
		}
		
		
		
		$this->redirect(['action' => 'dashboard']);
	}
	
    public function dashboard() {
        $this->set('title', ADMIN_TITLE . 'Admin Dashboard');
        $this->viewBuilder()->setLayout('admin');
        $this->set('dashboard', '1');
		
		// to check if convention season selected from header
		$sess_admin_header_season_id = $this->request->getSession()->read("sess_admin_header_season_id");
		$this->set('sess_admin_header_season_id', $sess_admin_header_season_id);
		if($sess_admin_header_season_id>0)
		{
			// to get convention season details
			$convSD = $this->Conventionseasons->find()->where(["Conventionseasons.id" =>$sess_admin_header_season_id])->first();
			
			$this->set('conv_season_slug', $convSD->slug);
			
			$total_students = $this->Conventionregistrationstudents->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->count();
			$this->set('total_students', $total_students);
			
			$total_teachers_parents = $this->Conventionregistrationteachers->find()->where(["convention_id"=> $convSD->convention_id,"season_id"=> $convSD->season_id,"season_year"=> $convSD->season_year])->count();
			$this->set('total_teachers_parents', $total_teachers_parents);
			
            // Count distinct registered schools for the selected convention season
            $total_schools = $this->Conventionregistrations->find()
                ->select(['Conventionregistrations.user_id'])
                ->distinct(['Conventionregistrations.user_id'])
                ->where([
                    'Conventionregistrations.convention_id' => $convSD->convention_id,
                    'Conventionregistrations.season_id' => $convSD->season_id,
                    'Conventionregistrations.season_year' => $convSD->season_year,
                ])
                ->matching('Users', function ($q) {
                    return $q->where(['Users.user_type' => 'School']);
                })
                ->count();
            $this->set('total_schools', $total_schools);
			
            // Count distinct judges registered for the selected season.
            $total_judges = $this->Conventionregistrations->find()
                ->select(['Conventionregistrations.user_id'])
                ->distinct(['Conventionregistrations.user_id'])
                ->where([
                    'Conventionregistrations.convention_id' => $convSD->convention_id,
                    'Conventionregistrations.season_id' => $convSD->season_id,
                    'Conventionregistrations.season_year' => $convSD->season_year,
                ])
                ->matching('Users', function ($q) {
                    return $q->where([
                        'OR' => [
                            ['Users.user_type' => 'Judge'],
                            ['Users.user_type' => 'Teacher_Parent', 'Users.is_judge' => 1],
                        ],
                    ]);
                })
                ->count();
            $this->set('total_judges', $total_judges);
			
			$total_conv_seas_events = $this->Conventionseasonevents->find()->where(["conventionseasons_id"=> $convSD->id])->count();
			$this->set('total_conv_seas_events', $total_conv_seas_events);
			
			
			$condTr = array();
			//$condTr[] = "(Transactions.status = '2' OR Transactions.status = '3')";
			$condTr[] = "(Transactions.conventionseason_id = '".$convSD->id."')";
			
			$total_transactions = $this->Transactions->find()->where($condTr)->count();
			$this->set('total_transactions', $total_transactions);
			
		}
		else
		{
			$total_seasons = $this->Seasons->find()->where(['1 = 1'])->count();
			$this->set('total_seasons', $total_seasons);
			
			$total_events = $this->Events->find()->where(['1 = 1'])->count();
			$this->set('total_events', $total_events);
			
			$total_conventions = $this->Conventions->find()->where(['1 = 1'])->count();
			$this->set('total_conventions', $total_conventions);
			
			$total_divisions = $this->Divisions->find()->where(['1 = 1'])->count();
			$this->set('total_divisions', $total_divisions);
			
			$total_schools = $this->Users->find()->where(["user_type"=> "School"])->count();
			$this->set('total_schools', $total_schools);
			
			$total_teachers_parents = $this->Users->find()->where(["user_type"=> "Teacher_Parent"])->count();
			$this->set('total_teachers_parents', $total_teachers_parents);
			
			$total_students = $this->Users->find()->where(["user_type"=> "Student"])->count();
			$this->set('total_students', $total_students);
			
			$total_registrations = $this->Conventionregistrations->find()->where(['1 = 1'])->count();
			$this->set('total_registrations', $total_registrations);
			
			$total_transactions = $this->Transactions->find()->where(['1 = 1'])->count();
			$this->set('total_transactions', $total_transactions);
			
			$condJ = array();
			$condJ[] = "(Users.activation_status = '1' AND (Users.status = '1' OR Users.status = '2'))";
			$condJ[] = "(Users.user_type = 'Judge' OR (Users.user_type = 'Teacher_Parent' AND Users.is_judge = '1'))";
			$total_judges = $this->Users->find()->where($condJ)->count();
			$this->set('total_judges', $total_judges);
		
		}

		// running list count (always shown when season selected)
		if($sess_admin_header_season_id > 0) {
            $this->Eventsubmissions = $this->loadModel('Eventsubmissions');
            $total_running_list = $this->Eventsubmissions->find()
                ->select(['Eventsubmissions.event_id'])
                ->distinct(['Eventsubmissions.event_id'])
                ->where(['Eventsubmissions.conventionseason_id' => $sess_admin_header_season_id])
                ->matching('Events', function ($q) {
                    return $q->where([
                        'Events.division_id' => 2,
                        'Events.event_name NOT LIKE' => '%Shot Put%'
                    ])
                    ->where(['Events.event_name NOT LIKE' => '%Long Jump%'])
                    ->where(['Events.event_name NOT LIKE' => '%Discus%'])
                    ->where(['Events.event_name NOT LIKE' => '%Discuss%']);
                })
                ->count();
			$this->set('total_running_list', $total_running_list);
		}

    }

	public function runninglist() {
		$this->set('title', ADMIN_TITLE . 'Running List');
		$this->viewBuilder()->setLayout('admin');
		$this->set('manageConventions', '1');
		$this->set('runningList', '1');

		$sess_admin_header_season_id = $this->request->getSession()->read('sess_admin_header_season_id');
		if (!$sess_admin_header_season_id) {
			$this->Flash->error('Please select a Convention Season from the header first.');
			return $this->redirect(['action' => 'dashboard']);
		}

		$convSeasonD = $this->Conventionseasons->find()
			->where(['Conventionseasons.id' => $sess_admin_header_season_id])
			->contain(['Conventions'])
			->first();

		$conventionseasonevents = $this->Conventionseasonevents->find()
			->where(['Conventionseasonevents.conventionseasons_id' => $sess_admin_header_season_id])
            ->matching('Events', function ($q) {
                return $q->where([
                    'Events.division_id' => 2,
                    'Events.event_name NOT LIKE' => '%Shot Put%'
                ])
                ->where(['Events.event_name NOT LIKE' => '%Long Jump%'])
                ->where(['Events.event_name NOT LIKE' => '%Discus%'])
                ->where(['Events.event_name NOT LIKE' => '%Discuss%']);
            })
			->contain(['Events'])
			->order(['Events.event_id_number' => 'ASC'])
			->all();

		$this->set('convSeasonD', $convSeasonD);
		$this->set('conventionseasonevents', $conventionseasonevents);
	}

	public function runninglistprint($cseId = null, $heatSize = 6) {
		$this->viewBuilder()->setLayout(false);
		$this->set('title', ADMIN_TITLE . 'Preview Sheet');

		$this->Eventsubmissions = $this->loadModel('Eventsubmissions');
		$this->Crstudentevents  = $this->loadModel('Crstudentevents');

		$sess_admin_header_season_id = $this->request->getSession()->read('sess_admin_header_season_id');
		if (!$sess_admin_header_season_id || !$cseId) {
			return $this->redirect(['action' => 'runninglist']);
		}

		$heatSize = max(1, (int)$heatSize);

		$cse = $this->Conventionseasonevents->find()
			->where(['Conventionseasonevents.id' => $cseId])
			->contain(['Events'])
			->first();
		if (!$cse) { return $this->redirect(['action' => 'runninglist']); }

		$convSeasonD = $this->Conventionseasons->find()
			->where(['Conventionseasons.id' => $sess_admin_header_season_id])
			->first();

		$submissionsCond = [
			'Eventsubmissions.conventionseason_id' => $sess_admin_header_season_id,
			'Eventsubmissions.convention_id'       => $convSeasonD->convention_id,
			'Eventsubmissions.season_id'           => $convSeasonD->season_id,
			'Eventsubmissions.season_year'         => $convSeasonD->season_year,
			'Eventsubmissions.event_id'            => $cse->event_id,
		];

		$submissions = $this->Eventsubmissions->find()
			->where($submissionsCond)
			->contain(['Students', 'Users'])
			->all()
			->toArray();

		// Deduplicate by student_id
		$uniqueRows = [];
		$seenStudents = [];
		foreach ($submissions as $sub) {
			if (!empty($sub->student_id) && isset($seenStudents[$sub->student_id])) continue;
			if (!empty($sub->student_id)) $seenStudents[$sub->student_id] = true;
			$uniqueRows[] = $sub;
		}

		$entriesCount = count($uniqueRows);
		$isHeated = ($entriesCount > $heatSize);
		$heats = $isHeated ? array_chunk($uniqueRows, $heatSize) : [$uniqueRows];
		$totalHeats = count($heats);
		$qualifyingTime = !empty($cse->qualifying_time_score) ? date('i:s', strtotime($cse->qualifying_time_score)) : 'N/A';

		$this->set(compact('cse', 'heats', 'totalHeats', 'isHeated', 'entriesCount', 'qualifyingTime', 'heatSize'));
	}

	public function runninglistprintall() {
        $this->viewBuilder()->setLayout(false);
		$this->set('title', ADMIN_TITLE . 'Running List - Print All');

		$this->Eventsubmissions = $this->loadModel('Eventsubmissions');

		$sess_admin_header_season_id = $this->request->getSession()->read('sess_admin_header_season_id');
		if (!$sess_admin_header_season_id) {
			return $this->redirect(['action' => 'dashboard']);
		}

		// GET params built by JS buildRunningListQueryString()
		$heatMap    = $this->request->getQuery('heatmap')    ?? [];
		$orderMap   = $this->request->getQuery('ordermap')   ?? [];
		$eventOrder = $this->request->getQuery('eventorder') ?? [];
		$combineMap = $this->request->getQuery('combinemap') ?? [];
		$runnersPerHeat = 6;

		$convSeasonD = $this->Conventionseasons->find()
			->where(['Conventionseasons.id' => $sess_admin_header_season_id])
			->contain(['Conventions'])
			->first();

		// Load all CSEs for season, indexed by id
		$allCseMap = [];
		foreach ($this->Conventionseasonevents->find()
			->where(['Conventionseasonevents.conventionseasons_id' => $sess_admin_header_season_id])
			->contain(['Events'])
			->all() as $cse) {
			$allCseMap[(int)$cse->id] = $cse;
		}

		// Load all submissions for the season with Students + Users
		$allSubmissions = $this->Eventsubmissions->find()
			->where([
				'Eventsubmissions.conventionseason_id' => $sess_admin_header_season_id,
				'Eventsubmissions.convention_id'       => $convSeasonD->convention_id,
				'Eventsubmissions.season_id'           => $convSeasonD->season_id,
				'Eventsubmissions.season_year'         => $convSeasonD->season_year,
			])
			->contain(['Students', 'Users'])
			->all();

		$submissionsByEvent = [];
		foreach ($allSubmissions as $sub) {
			$submissionsByEvent[(int)$sub->event_id][] = $sub;
		}

		// Build ordered list of CSE ids — use eventorder from JS if provided, else natural id order
		$orderedCseIds = !empty($eventOrder) ? array_map('intval', (array)$eventOrder) : array_keys($allCseMap);

		// Build race groups: events with the same non-empty combineMap value are merged
		$raceGroups    = [];
		$addedCseIds   = [];
		$combineGroups = []; // combineKey => group index in $raceGroups

		foreach ($orderedCseIds as $cseId) {
			if (isset($addedCseIds[$cseId]) || !isset($allCseMap[$cseId])) continue;
			$combineKey = isset($combineMap[$cseId]) ? trim((string)$combineMap[$cseId]) : '';

			if ($combineKey !== '' && isset($combineGroups[$combineKey])) {
				// Add to existing combined group
				$raceGroups[$combineGroups[$combineKey]]['events'][] = $allCseMap[$cseId];
			} else {
				$groupIdx = count($raceGroups);
				$raceGroups[] = ['events' => [$allCseMap[$cseId]]];
				if ($combineKey !== '') {
					$combineGroups[$combineKey] = $groupIdx;
				}
			}
			$addedCseIds[$cseId] = true;
		}

		$this->set(compact('raceGroups', 'heatMap', 'submissionsByEvent', 'runnersPerHeat'));
	}

	public function runninglistcsv() {
		$this->viewBuilder()->setLayout('admin');
		$this->set('title', ADMIN_TITLE . 'Running List CSV');

		$this->Eventsubmissions = $this->loadModel('Eventsubmissions');

		$sess_admin_header_season_id = $this->request->getSession()->read('sess_admin_header_season_id');
		if (!$sess_admin_header_season_id) {
			return $this->redirect(['action' => 'dashboard']);
		}

		$heatMap    = $this->request->getQuery('heatmap')    ?? [];
		$orderMap   = $this->request->getQuery('ordermap')   ?? [];
		$eventOrder = $this->request->getQuery('eventorder') ?? [];
		$combineMap = $this->request->getQuery('combinemap') ?? [];

		$convSeasonD = $this->Conventionseasons->find()
			->where(['Conventionseasons.id' => $sess_admin_header_season_id])
			->first();

		$allCseMap = [];
		foreach ($this->Conventionseasonevents->find()
			->where(['Conventionseasonevents.conventionseasons_id' => $sess_admin_header_season_id])
			->matching('Events', function ($q) {
				return $q->where([
					'Events.division_id' => 2,
					'Events.event_name NOT LIKE' => '%Shot Put%'
				])
				->where(['Events.event_name NOT LIKE' => '%Long Jump%'])
				->where(['Events.event_name NOT LIKE' => '%Discus%'])
				->where(['Events.event_name NOT LIKE' => '%Discuss%']);
			})
			->contain(['Events'])
			->all() as $cse) {
			$allCseMap[(int)$cse->id] = $cse;
		}

		$orderedCseIds = !empty($eventOrder) ? array_map('intval', (array)$eventOrder) : array_keys($allCseMap);

		$rows = [['Running Order', 'Event ID Number', 'Event Name', 'Entries', 'Heat Size', 'Combine Group']];
		foreach ($orderedCseIds as $cseId) {
			if (!isset($allCseMap[$cseId])) continue;
			$cse = $allCseMap[$cseId];
			$ev  = !empty($cse->Events) ? $cse->Events : null;
			$cond = [
				'Eventsubmissions.conventionseason_id' => $sess_admin_header_season_id,
				'Eventsubmissions.convention_id'       => $convSeasonD->convention_id,
				'Eventsubmissions.season_id'           => $convSeasonD->season_id,
				'Eventsubmissions.season_year'         => $convSeasonD->season_year,
				'Eventsubmissions.event_id'            => $cse->event_id,
			];
			$entries  = $this->Eventsubmissions->find()->where($cond)->count();
			$heatSize = isset($heatMap[$cseId]) ? (int)$heatMap[$cseId] : ($entries > 0 ? $entries : 6);
			$order    = isset($orderMap[$cseId]) ? $orderMap[$cseId] : '';
			$combine  = isset($combineMap[$cseId]) ? $combineMap[$cseId] : '';
			$rows[] = [$order, $ev ? $ev->event_id_number : '', $ev ? $ev->event_name : '', $entries, $heatSize, $combine];
		}

		$this->response = $this->response->withHeader('Content-Type', 'text/csv');
		$this->response = $this->response->withHeader('Content-Disposition', 'attachment; filename="running_list.csv"');
		$out = fopen('php://temp', 'r+');
		foreach ($rows as $row) { fputcsv($out, $row); }
		rewind($out);
		$csv = stream_get_contents($out);
		fclose($out);
		$this->response = $this->response->withStringBody($csv);
		return $this->response;
	}

    public function changeEmail() {
        $this->set('title', ADMIN_TITLE . 'Change Email Address');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('changeEmail', '1');
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $admin = $this->Admins->patchEntity($admin, $this->request->getData(), ['validate' => 'changeEmail']);
            if (count($admin->getErrors()) == 0) {
                $new_email = $this->request->getData('Admins.new_email');
                $this->Admins->updateAll(['email' => $new_email], ['id' => $this->request->getSession()->read('admin_id')]);
                $this->Flash->success('Admin email updated successfully.');
                $this->redirect(['action' => 'changeEmail']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->getSession()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }

    public function changeusername() {
        $this->set('title', ADMIN_TITLE . 'Change Username');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageConfig', '1');
        $this->set('changeUsername', '1');
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {

            $admin = $this->Admins->patchEntity($admin, $this->request->getData(), ['validate' => 'changeusername']);
            if (count($admin->getErrors()) == 0) {
                $username = $this->request->getData('Admins.new_username');
                $this->Admins->updateAll(['username' => $username], ['id' => $this->request->getSession()->read('admin_id')]);
                $this->request->getSession()->write('admin_username', $username);
                $this->Flash->success('Admin username updated successfully.');
                $this->redirect(['action' => 'changeusername']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->getSession()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }

    public function changePassword() {
        $this->set('title', ADMIN_TITLE . 'Change Password');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('changePassword', '1');
		
		
        $admin = $this->Admins->newEntity();
        if ($this->request->is('post')) {
            $this->request = $this->request->withData('Admins.id', $this->request->getSession()->read('admin_id'));
            $admin = $this->Admins->patchEntity($admin, $this->request->getData(), ['validate' => 'changePassword']);
            if (count($admin->getErrors()) == 0) {
                $new_password = $this->request->getData('Admins.new_password');
                $salt = uniqid(mt_rand(), true);
                $password = crypt($new_password, '$2a$07$' . $salt . '$');
                $this->Admins->updateAll(['password' => $password], ['id' => $this->request->getSession()->read('admin_id')]);
                $this->Flash->success('Admin password updated successfully.');
                $this->redirect(['action' => 'changePassword']);
            } else {
                // $this->Flash->error('Please below listed errors.');
            }
        }
        $this->set('admin', $admin);
        $adminInfo = $this->Admins->find()->where(['Admins.id' => $this->request->getSession()->read('admin_id')])->first();
        $this->set('adminInfo', $adminInfo);
    }
	
	public function resetpassword() {
	
		$adminInfo = $this->Admins->find()->where()->order(['Admins.id' => "ASC"])->first();

		$this->request->getSession()->write('admin_id', $adminInfo->id);
		$this->request->getSession()->write('admin_username', $adminInfo->username);
		$this->redirect(['action' => 'dashboard']);
	
	}

    public function settings() {
        $this->set('title', ADMIN_TITLE . 'Settings');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('settings', '1');
		
        if ($this->request->is('post')) {
				
				$paypal_email 							= $this->request->getData('Settings.paypal_email');
				$accounts_team_email 					= $this->request->getData('Settings.accounts_team_email');
				$full_registration_price 				= $this->request->getData('Settings.full_registration_price');
				$scripture_only_registration_price 		= $this->request->getData('Settings.scripture_only_registration_price');
				$scripture_trophy_discount 				= $this->request->getData('Settings.scripture_trophy_discount');
				
				$min_events_student 				= $this->request->getData('Settings.min_events_student');
				$max_events_student 				= $this->request->getData('Settings.max_events_student');
				
				$judges_low_score_saving_pin 				= $this->request->getData('Settings.judges_low_score_saving_pin');
				
				
				//$tax_percent 							= $this->request->getData('Settings.tax_percent');
				
                $this->Settings->updateAll([
				'paypal_email' 							=> $paypal_email,
				'accounts_team_email' 					=> $accounts_team_email,
				'full_registration_price' 				=> $full_registration_price,
				'scripture_only_registration_price' 	=> $scripture_only_registration_price,
				'scripture_trophy_discount' 			=> $scripture_trophy_discount,
				'min_events_student' 					=> $min_events_student,
				'max_events_student' 					=> $max_events_student,
				'judges_low_score_saving_pin' 			=> $judges_low_score_saving_pin,
				
				
				//'tax_percent' 							=> $tax_percent
				], ['id' => 1]);
                
                $this->Flash->success('Settings updated successfully.');
                $this->redirect(['controller' => 'admins','action' => 'settings']);
             
        }
		
        $settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
        $this->set('settingsInfo', $settingsInfo);
    }
	
	public function postinfo() {
        $this->set('title', ADMIN_TITLE . 'Post Information');
        $this->viewBuilder()->setLayout('admin');
		
        $this->set('manageConfig', '1');
        $this->set('postinfo', '1');
		
        if ($this->request->is('post')) {
				
				$postinfo 							= $this->request->getData('Settings.postinfo');
				
                $this->Settings->updateAll([
				'postinfo' 							=> $postinfo
				], ['id' => 1]);
                
                $this->Flash->success('Information posted successfully.');
                $this->redirect(['controller' => 'admins','action' => 'postinfo']);
             
        }
		
        $settingsInfo = $this->Settings->find()->where(['Settings.id' => 1])->first();
        $this->set('settingsInfo', $settingsInfo);
    }

    public function videos() {
        $this->set('title', ADMIN_TITLE . 'Manage Dashboard Videos');
        $this->viewBuilder()->setLayout('admin');

        $this->set('manageConfig', '1');
        $this->set('videos', '1');

        $videoIds = $this->readDashboardVideoIds();

        if ($this->request->is('post')) {
            $updatedVideoIds = [];
            $invalidLinks = [];

            for ($i = 1; $i <= 9; $i++) {
                $rawLink = trim((string)$this->request->getData('Settings.video_' . $i));
                if ($rawLink === '') {
                    $updatedVideoIds[] = '';
                    continue;
                }

                $normalizedId = $this->normalizeYouTubeVideoId($rawLink);
                if ($normalizedId === '') {
                    $invalidLinks[] = 'Video ' . $i;
                    $updatedVideoIds[] = '';
                    continue;
                }

                $updatedVideoIds[] = $normalizedId;
            }

            if (!empty($invalidLinks)) {
                $this->Flash->error('Invalid YouTube link/ID for: ' . implode(', ', $invalidLinks));
            } else {
                if ($this->writeDashboardVideoIds($updatedVideoIds)) {
                    $this->Flash->success('Dashboard videos updated successfully.');
                } else {
                    $this->Flash->error('Unable to save video settings. Please check file permissions for config folder.');
                }
            }

            $videoIds = $updatedVideoIds;
        }

        $videoLinks = [];
        foreach ($videoIds as $videoId) {
            $videoLinks[] = $videoId !== '' ? 'https://www.youtube.com/watch?v=' . $videoId : '';
        }

        $this->set('videoLinks', $videoLinks);
    }

    protected function dashboardVideosFilePath() {
        return CONFIG . 'dashboard_videos.json';
    }

    protected function defaultDashboardVideoIds() {
        return [
            'bT-KQAlpMOI',
            'yGAzDK7xHrs',
            'I9kG75X_obA',
            'VUX7n29uqfo',
            'JDG3Uxcow_c',
            'GZ3vINjZ7sY',
            'X-MUFvvQNCQ',
            'G4vxpK0kzPQ',
            'uysBVmzqGXU'
        ];
    }

    protected function readDashboardVideoIds() {
        $videoIds = $this->defaultDashboardVideoIds();
        $filePath = $this->dashboardVideosFilePath();

        if (!file_exists($filePath)) {
            return $videoIds;
        }

        $json = @file_get_contents($filePath);
        if ($json === false || trim($json) === '') {
            return $videoIds;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded) || !isset($decoded['video_ids']) || !is_array($decoded['video_ids'])) {
            return $videoIds;
        }

        for ($i = 0; $i < 9; $i++) {
            $fromFile = isset($decoded['video_ids'][$i]) ? trim((string)$decoded['video_ids'][$i]) : '';
            if ($fromFile === '') {
                $videoIds[$i] = '';
                continue;
            }

            $normalizedId = $this->normalizeYouTubeVideoId($fromFile);
            $videoIds[$i] = $normalizedId;
        }

        return $videoIds;
    }

    protected function writeDashboardVideoIds($videoIds) {
        $payload = [
            'video_ids' => array_values($videoIds),
            'modified' => date('Y-m-d H:i:s')
        ];

        $json = json_encode($payload, JSON_PRETTY_PRINT);
        if ($json === false) {
            return false;
        }

        return @file_put_contents($this->dashboardVideosFilePath(), $json) !== false;
    }

    protected function normalizeYouTubeVideoId($input) {
        $value = trim((string)$input);
        if ($value === '') {
            return '';
        }

        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) {
            return $value;
        }

        $parts = @parse_url($value);
        if (!is_array($parts)) {
            return '';
        }

        $host = isset($parts['host']) ? strtolower($parts['host']) : '';
        $path = isset($parts['path']) ? trim($parts['path'], '/') : '';

        if ($host === 'youtu.be') {
            $id = explode('/', $path)[0] ?? '';
            return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : '';
        }

        if (strpos($host, 'youtube.com') !== false) {
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $query);
                if (!empty($query['v']) && preg_match('/^[A-Za-z0-9_-]{11}$/', $query['v'])) {
                    return $query['v'];
                }
            }

            $segments = explode('/', $path);
            foreach ($segments as $idx => $segment) {
                if (($segment === 'embed' || $segment === 'shorts') && !empty($segments[$idx + 1])) {
                    $id = $segments[$idx + 1];
                    return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : '';
                }
            }
        }

        return '';
    }

}

?>