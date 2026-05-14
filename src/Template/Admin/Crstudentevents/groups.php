<script type="text/javascript">
    $(document).ready(function() { $("#adminForm").validate(); });
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Users = TableRegistry::getTableLocator()->get('Users');
$this->Events = TableRegistry::getTableLocator()->get('Events');
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<style>
.grp-event-card { background:#fff; border:1px solid #e3e6ec; border-radius:8px; margin:0 0 22px; overflow:hidden; }
.grp-event-head { background:#f6f8fb; padding:14px 18px; border-bottom:1px solid #e3e6ec; display:flex; flex-wrap:wrap; align-items:center; gap:12px; }
.grp-event-head .grp-name { font-size:16px; font-weight:700; color:#2c3e50; }
.grp-event-head .grp-meta { font-size:12px; color:#7b8794; }
.grp-event-head .grp-pill { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; background:#e1f3ff; color:#1f6f9b; }
.grp-event-body { padding:16px 18px; }
.grp-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:14px; }
.grp-card { border:1px solid #e3e6ec; border-radius:6px; background:#fff; transition:all .15s ease; }
.grp-card:hover { box-shadow:0 4px 12px rgba(0,0,0,0.06); }
.grp-card.is-ok { border-top:3px solid #1c7a45; }
.grp-card.is-bad { border-top:3px solid #a52a2a; }
.grp-card-head { padding:10px 12px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #eef1f5; font-weight:600; color:#2c3e50; }
.grp-card-head .ok  { color:#1c7a45; }
.grp-card-head .bad { color:#a52a2a; }
.grp-card-body { padding:0; }
.grp-card-body table { width:100%; margin:0; }
.grp-card-body td { padding:8px 12px; border-top:1px solid #f0f2f5; font-size:13px; }
.grp-card-body td:first-child { color:#2c3e50; }
.grp-card-body td.age { text-align:right; color:#7b8794; font-size:12px; width:60px; }
</style>
<div class="content-wrapper">
    <section class="content-header">
      <h1>Groups</h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> Convention Registrations ', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Groups</li>
      </ol>
    </section>

    <section class="content">
        <?php echo $this->element('Admin/Conventionregistrations/registrant_header'); ?>
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-users"></i> Group Events</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="box-body">
                <?php if (empty($arrConvGroups)) { ?>
                    <div class="cr-empty"><i class="fa fa-users"></i>No groups have been created yet.</div>
                <?php } else { ?>
                    <?php foreach ($arrConvGroups as $eventid => $groupstudents) {
                        $eventD = $this->Events->find()->where(['Events.id' => $eventid])->first();
                        if (!$eventD) { continue; }
                        $totalStudentsEvent = $this->Crstudentevents->find()->where([
                            'Crstudentevents.conventionregistration_id' => $CRDetails->id,
                            'Crstudentevents.convention_id' => $CRDetails->convention_id,
                            'Crstudentevents.season_id' => $CRDetails->season_id,
                            'Crstudentevents.season_year' => $CRDetails->season_year,
                            'Crstudentevents.event_id' => $eventD->id,
                        ])->count();
                    ?>
                    <div class="grp-event-card">
                        <div class="grp-event-head">
                            <div>
                                <div class="grp-name"><?php echo h($eventD->event_name); ?> <span class="grp-pill"><?php echo h($eventD->event_id_number); ?></span></div>
                                <div class="grp-meta">Min <?php echo (int)$eventD->min_no; ?> &middot; Max <?php echo (int)$eventD->max_no; ?> &middot; Total students in event: <?php echo (int)$totalStudentsEvent; ?></div>
                            </div>
                        </div>
                        <div class="grp-event-body">
                            <div class="grp-grid">
                                <?php foreach ($groupstudents as $stgname => $studentids) {
                                    $count = count($studentids);
                                    $okMin = (int)$eventD->min_no;
                                    $okMax = (int)$eventD->max_no;
                                    $isOk = ($count >= $okMin && $count <= $okMax);
                                ?>
                                <div class="grp-card <?php echo $isOk ? 'is-ok' : 'is-bad'; ?>">
                                    <div class="grp-card-head">
                                        <span>Group <?php echo h($stgname); ?> <small style="color:#7b8794; font-weight:400;">(<?php echo $count; ?>)</small></span>
                                        <?php if ($isOk) { ?>
                                            <span class="ok" title="Meets min/max"><i class="fa fa-check-circle"></i></span>
                                        <?php } else { ?>
                                            <span class="bad" title="Group does not meet min/max"><i class="fa fa-exclamation-triangle"></i></span>
                                        <?php } ?>
                                    </div>
                                    <div class="grp-card-body">
                                        <table>
                                            <tbody>
                                            <?php
                                            $implodeStIDS = $count ? implode(',', $studentids) : '0';
                                            $studentsDList = $this->Users->find()->where(["(Users.id IN ($implodeStIDS))"])->order(["Users.first_name"=>"ASC","Users.middle_name"=>"ASC"])->all();
                                            foreach ($studentsDList as $st) {
                                                $age = $st->birth_year ? (date('Y') - (int)$st->birth_year) : null;
                                            ?>
                                            <tr>
                                                <td><?php echo h(trim($st->first_name . ' ' . ($st->middle_name ? $st->middle_name . ' ' : '') . $st->last_name)); ?></td>
                                                <td class="age"><?php echo $age !== null ? $age . ' yrs' : ''; ?></td>
                                            </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </div>
    </section>
</div>
