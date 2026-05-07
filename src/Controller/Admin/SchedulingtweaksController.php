<?php

namespace App\Controller\Admin;

use App\Controller\AppController;

class SchedulingtweaksController extends AppController {

    public $paginate = ['limit' => 100];

    public function initialize(): void {
        parent::initialize();
        $this->loadComponent('Flash');
        $action = $this->request->getParam('action');
        $loggedAdminId = $this->request->getSession()->read('admin_id');
        if ($action != 'forgotPassword' && $action != 'logout') {
            if (!$loggedAdminId && $action != 'login' && $action != 'captcha') {
                $this->redirect(['controller' => 'admins', 'action' => 'login']);
            }
        }

        $this->Conventionseasons           = $this->loadModel('Conventionseasons');
        $this->Conventions                 = $this->loadModel('Conventions');
        $this->Conventionseasonevents      = $this->loadModel('Conventionseasonevents');
        $this->Conventionrooms             = $this->loadModel('Conventionrooms');
        $this->Conventionseasonroomevents  = $this->loadModel('Conventionseasonroomevents');
        $this->Schedulingeventtweaks       = $this->loadModel('Schedulingeventtweaks');
        $this->Events                      = $this->loadModel('Events');
        $this->Schedulings                 = $this->loadModel('Schedulings');
    }

    /* ------------------------------------------------------------------ */
    /*  INDEX – list all events for this convention season with tweak controls */
    /* ------------------------------------------------------------------ */
    public function index($convention_season_slug = null) {
        $this->set('title', ADMIN_TITLE . 'Scheduling Tweaks');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->contain(['Conventions'])
            ->first();
        $this->set('conventionSD', $conventionSD);
        $this->set('convention_slug', $conventionSD->Conventions['slug']);

        /* All events registered for this convention season */
        $cseList = $this->Conventionseasonevents->find()
            ->where([
                'Conventionseasonevents.conventionseasons_id' => $conventionSD->id,
                'Conventionseasonevents.convention_id'        => $conventionSD->convention_id,
            ])
            ->all();

        /* Build an array of events that need_schedule */
        $eventsForTweaks = [];
        foreach ($cseList as $cse) {
            $eventD = $this->Events->find()->where(['Events.id' => $cse->event_id])->first();
            if ($eventD && $eventD->needs_schedule == '1') {
                $eventsForTweaks[] = $eventD;
            }
        }
        $this->set('eventsForTweaks', $eventsForTweaks);

        /* Existing tweaks indexed by event_id */
        $tweakRows = $this->Schedulingeventtweaks->find()
            ->where(['Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id])
            ->all();
        $tweaksMap = [];
        foreach ($tweakRows as $tw) {
            $tweaksMap[$tw->event_id] = $tw;
        }
        $this->set('tweaksMap', $tweaksMap);

        /* Rooms for this convention (for the pinned-room dropdown) */
        $rooms = $this->Conventionrooms->find()
            ->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
            ->order(['Conventionrooms.room_name' => 'ASC'])
            ->all();
        $this->set('rooms', $rooms);

        /* Days dropdown from the scheduling wizard */
        $schedulingD = $this->Schedulings->find()
            ->where(['Schedulings.conventionseasons_id' => $conventionSD->id])
            ->first();
        $this->set('schedulingD', $schedulingD);

        global $weekDays;
        $this->set('weekDays', $weekDays);
    }

    /* ------------------------------------------------------------------ */
    /*  SAVE – AJAX / POST: save or update a single event tweak            */
    /* ------------------------------------------------------------------ */
    public function save($convention_season_slug = null) {
        $this->request->allowMethod(['post']);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->first();

        $postData  = (array) $this->request->getData();
        $event_id  = (int) ($postData['event_id'] ?? 0);

        if (!$conventionSD || $event_id < 1) {
            $this->Flash->error('Invalid request.');
            return $this->redirect(['action' => 'index', $convention_season_slug]);
        }

        /* Find or create the tweak record */
        $existing = $this->Schedulingeventtweaks->find()
            ->where([
                'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
                'Schedulingeventtweaks.event_id'             => $event_id,
            ])
            ->first();

        if ($existing) {
            $tweak = $existing;
        } else {
            $tweak = $this->Schedulingeventtweaks->newEntity();
            $tweak->conventionseasons_id = $conventionSD->id;
            $tweak->event_id             = $event_id;
            $tweak->created              = date('Y-m-d H:i:s');
        }

        /* A: pinned day */
        $tweak->pinned_day = !empty($postData['pinned_day']) ? trim($postData['pinned_day']) : null;

        /* C: pinned start time */
        $rawTime = trim($postData['pinned_start_time'] ?? '');
        if ($rawTime !== '' && strtotime($rawTime) !== false) {
            $tweak->pinned_start_time = date('H:i:s', strtotime($rawTime));
        } else {
            $tweak->pinned_start_time = null;
        }

        /* B: pinned room */
        $tweak->pinned_room_id = !empty($postData['pinned_room_id']) ? (int) $postData['pinned_room_id'] : null;

        $tweak->modified = date('Y-m-d H:i:s');

        if ($this->Schedulingeventtweaks->save($tweak)) {
            $this->Flash->success('Tweak saved.');
        } else {
            $this->Flash->error('Could not save tweak. Check server logs.');
        }

        return $this->redirect(['action' => 'index', $convention_season_slug]);
    }

    /* ------------------------------------------------------------------ */
    /*  CLEAR – remove all tweaks for one event                            */
    /* ------------------------------------------------------------------ */
    public function clear($convention_season_slug = null, $event_id = null) {
        $this->request->allowMethod(['post', 'get']);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->first();

        if ($conventionSD && $event_id) {
            $this->Schedulingeventtweaks->deleteAll([
                'Schedulingeventtweaks.conventionseasons_id' => $conventionSD->id,
                'Schedulingeventtweaks.event_id'             => (int) $event_id,
            ]);
            $this->Flash->success('Tweak cleared.');
        }

        return $this->redirect(['action' => 'index', $convention_season_slug]);
    }

    /* ------------------------------------------------------------------ */
    /*  ROOMAVAILABILITY – set available_from / available_to per room      */
    /* ------------------------------------------------------------------ */
    public function roomavailability($convention_season_slug = null) {
        $this->set('title', ADMIN_TITLE . 'Room Availability Windows');
        $this->viewBuilder()->setLayout('admin');
        $this->set('manageConventions', '1');
        $this->set('conventionList', '1');
        $this->set('convention_season_slug', $convention_season_slug);

        $conventionSD = $this->Conventionseasons->find()
            ->where(['Conventionseasons.slug' => $convention_season_slug])
            ->contain(['Conventions'])
            ->first();
        $this->set('conventionSD', $conventionSD);
        $this->set('convention_slug', $conventionSD->Conventions['slug']);

        $rooms = $this->Conventionrooms->find()
            ->where(['Conventionrooms.convention_id' => $conventionSD->convention_id])
            ->order(['Conventionrooms.room_name' => 'ASC'])
            ->all();
        $this->set('rooms', $rooms);

        if ($this->request->is(['post'])) {
            $postData = (array) $this->request->getData();
            foreach ($rooms as $room) {
                $fromRaw = trim($postData['available_from'][$room->id] ?? '');
                $toRaw   = trim($postData['available_to'][$room->id] ?? '');

                $availFrom = ($fromRaw !== '' && strtotime($fromRaw) !== false)
                    ? date('H:i:s', strtotime($fromRaw)) : null;
                $availTo   = ($toRaw !== '' && strtotime($toRaw) !== false)
                    ? date('H:i:s', strtotime($toRaw)) : null;

                $this->Conventionrooms->updateAll(
                    ['available_from' => $availFrom, 'available_to' => $availTo, 'modified' => date('Y-m-d H:i:s')],
                    ['id' => $room->id]
                );
            }
            $this->Flash->success('Room availability windows saved.');
            return $this->redirect(['action' => 'roomavailability', $convention_season_slug]);
        }
    }
}
