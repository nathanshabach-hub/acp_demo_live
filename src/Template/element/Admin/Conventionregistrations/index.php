<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo HTTP_IMAGE ?>/loading.gif',
            closeImage: '<?php echo HTTP_IMAGE ?>/close.png'
        })
    })            
</script>
<style>
.cr-table { font-size: 13px; }
.cr-table thead th { background:#f6f8fb; color:#34495e; font-weight:600; border-bottom:2px solid #d8dde4 !important; white-space:nowrap; }
.cr-table tbody td { vertical-align: middle !important; }
.cr-pill { display:inline-block; padding:2px 9px; border-radius:11px; font-size:11px; font-weight:600; line-height:1.6; letter-spacing:.02em; }
.cr-pill-school { background:#e1f3ff; color:#1f6f9b; }
.cr-pill-judge  { background:#fff1d6; color:#a06600; }
.cr-pill-tp     { background:#ece6ff; color:#5037a7; }
.cr-pill-other  { background:#eceff1; color:#5b6770; }
.cr-status { display:inline-flex; align-items:center; gap:5px; padding:2px 9px; border-radius:11px; font-size:11px; font-weight:600; }
.cr-status .dot { width:7px; height:7px; border-radius:50%; }
.cr-status-active   { background:#e1f5ec; color:#1c7a45; }
.cr-status-active .dot { background:#1c7a45; }
.cr-status-pending  { background:#fff5e1; color:#9a6500; }
.cr-status-pending .dot { background:#d99100; }
.cr-status-inactive { background:#fdecec; color:#a52a2a; }
.cr-status-inactive .dot { background:#a52a2a; }
.cr-registrant .name { font-weight:600; color:#2c3e50; }
.cr-registrant .email { font-size:11px; color:#7b8794; }
.cr-actions { white-space:nowrap; display:flex; flex-wrap:wrap; gap:4px; }
.cr-actions .btn { padding:4px 8px; line-height:1; }
.cr-empty { padding:38px 18px; text-align:center; color:#7b8794; }
.cr-empty i { font-size:34px; color:#cbd2d9; display:block; margin-bottom:8px; }
</style>
<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
$this->Heartevents = TableRegistry::getTableLocator()->get('Heartevents');
$this->Eventsubmissions = TableRegistry::getTableLocator()->get('Eventsubmissions');
$conventionregistrations = $conventionregistrations ?? [];
$priceStructureCR = $priceStructureCR ?? [];

$typeMeta = [
    'School'         => ['label' => 'School',          'pill' => 'cr-pill-school'],
    'Judge'          => ['label' => 'Judge',           'pill' => 'cr-pill-judge'],
    'Teacher_Parent' => ['label' => 'Teacher / Parent','pill' => 'cr-pill-tp'],
];
$statusMeta = [
    1 => ['label' => 'Active',   'class' => 'cr-status-active'],
    2 => ['label' => 'Pending',  'class' => 'cr-status-pending'],
    0 => ['label' => 'Inactive', 'class' => 'cr-status-inactive'],
];

// quick stat counts
$totalCnt = count($conventionregistrations);
$schoolCnt = $judgeCnt = $tpCnt = $pendingCnt = 0;
foreach ($conventionregistrations as $r) {
    $u = $r->Users ?? null;
    $t = $u->user_type ?? null;
    if ($t === 'School') $schoolCnt++;
    elseif ($t === 'Judge') $judgeCnt++;
    elseif ($t === 'Teacher_Parent') $tpCnt++;
    if (($r->status ?? null) == 2) $pendingCnt++;
}
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>

<div class="cr-stat-row">
    <div class="cr-stat"><span class="cr-ico" style="background:#3c8dbc;"><i class="fa fa-list"></i></span><div><div class="cr-num"><?php echo $totalCnt; ?></div><div class="cr-lbl">Registrations</div></div></div>
    <div class="cr-stat"><span class="cr-ico" style="background:#1f6f9b;"><i class="fa fa-graduation-cap"></i></span><div><div class="cr-num"><?php echo $schoolCnt; ?></div><div class="cr-lbl">Schools</div></div></div>
    <div class="cr-stat"><span class="cr-ico" style="background:#a06600;"><i class="fa fa-gavel"></i></span><div><div class="cr-num"><?php echo $judgeCnt; ?></div><div class="cr-lbl">Judges</div></div></div>
    <div class="cr-stat"><span class="cr-ico" style="background:#5037a7;"><i class="fa fa-user"></i></span><div><div class="cr-num"><?php echo $tpCnt; ?></div><div class="cr-lbl">Teacher / Parent</div></div></div>
    <div class="cr-stat"><span class="cr-ico" style="background:#d99100;"><i class="fa fa-clock-o"></i></span><div><div class="cr-num"><?php echo $pendingCnt; ?></div><div class="cr-lbl">Pending Approval</div></div></div>
</div>

<?php if (!empty($conventionregistrations)) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="tbl-resp-listing">
                <table id="convention_registraions" class="table table-bordered table-hover table-condensed cf cr-table">
                    <thead class="cf ajshort">
                        <tr>
                            <th>#ID</th>
                            <th>Convention</th>
                            <th>Type</th>
                            <th>Registrant</th>
                            <th>Season</th>
                            <th>Price Structure</th>
                            <th>Per Student</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th class="action_dvv"><i class="fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conventionregistrations as $datarecord) {
                            $u = $datarecord->Users ?? null;
                            $type = $u->user_type ?? null;
                            $tm = $typeMeta[$type] ?? ['label' => 'N/A', 'pill' => 'cr-pill-other'];
                            $sm = $statusMeta[$datarecord->status ?? -1] ?? ['label' => 'Unknown', 'class' => 'cr-status-inactive'];
                            $name = trim((string)($u->first_name ?? '') . ' ' . (string)($u->last_name ?? ''));
                            if ($type === 'School') {
                                $name = (string)($u->first_name ?? '');
                            }
                            ?>
                            <tr>
                                <td>#<?php echo h($datarecord->id); ?></td>
                                <td><?php echo h($datarecord->Conventions->name ?? ''); ?></td>
                                <td><span class="cr-pill <?php echo $tm['pill']; ?>"><?php echo h($tm['label']); ?></span></td>
                                <td class="cr-registrant">
                                    <div class="name"><?php echo h($name !== '' ? $name : 'N/A'); ?></div>
                                    <?php if (!empty($u->email_address)) { ?>
                                        <div class="email"><i class="fa fa-envelope-o"></i> <?php echo h($u->email_address); ?></div>
                                    <?php } ?>
                                </td>
                                <td><?php echo h($datarecord->season_year); ?></td>
                                <td><?php echo h($priceStructureCR[$datarecord->price_structure] ?? 'N/A'); ?></td>
                                <td>
                                    <?php
                                    if (!empty($datarecord->price_structure)) {
                                        echo h(CURR . ' ' . number_format($datarecord->price_per_student, 2));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td><span class="cr-status <?php echo $sm['class']; ?>"><span class="dot"></span><?php echo h($sm['label']); ?></span></td>
                                <td><?php echo $datarecord->created ? date('M d, Y', strtotime($datarecord->created)) : ''; ?></td>
                                <td>
                                    <div class="cr-actions">
                                    <?php
                                    if ($type === 'School') {
                                        if ($datarecord->status == 1) {
                                            echo $this->Html->link('<i class="fa fa-user-secret"></i>', ['controller' => 'conventionregistrations', 'action' => 'teachers', $datarecord->slug], ['escape' => false, 'title' => 'Supervisors', 'class' => 'btn btn-primary btn-xs']);
                                            echo $this->Html->link('<i class="fa fa-group"></i>', ['controller' => 'conventionregistrations', 'action' => 'students', $datarecord->slug], ['escape' => false, 'title' => 'Students', 'class' => 'btn btn-primary btn-xs']);

                                            $checkHE = $this->Heartevents->find()->where(['Heartevents.conventionregistration_id' => $datarecord->id])->count();
                                            if ($checkHE > 0) {
                                                echo $this->Html->link('<i class="fa fa-heart"></i>', ['controller' => 'conventionregistrations', 'action' => 'heartevents', $datarecord->slug], ['escape' => false, 'title' => 'Events of the Heart', 'class' => 'btn btn-danger btn-xs']);
                                            }

                                            $checkES = $this->Eventsubmissions->find()->where(['Eventsubmissions.conventionregistration_id' => $datarecord->id])->count();
                                            if ($checkES > 0) {
                                                echo $this->Html->link('<i class="fa fa-database"></i>', ['controller' => 'eventsubmissions', 'action' => 'index', $datarecord->slug], ['escape' => false, 'title' => 'Event Submissions', 'class' => 'btn btn-info btn-xs']);
                                            }

                                            echo $this->Html->link('<i class="fa fa-users"></i>', ['controller' => 'crstudentevents', 'action' => 'groups', $datarecord->slug], ['escape' => false, 'title' => 'Groups', 'class' => 'btn btn-primary btn-xs']);
                                            echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventionregistrationstudents', 'action' => 'events', $datarecord->slug], ['escape' => false, 'title' => 'View Events Registered By This School', 'class' => 'btn btn-success btn-xs']);
                                        }
                                    }

                                    if ($type === 'Teacher_Parent' || $type === 'Judge') {
                                        echo $this->Html->link('<i class="fa fa-puzzle-piece"></i>', ['controller' => 'conventionregistrations', 'action' => 'judgeregevents', $datarecord->slug], ['escape' => false, 'title' => 'Judge Events', 'class' => 'btn btn-primary btn-xs']);
                                        if ($datarecord->status == 2) {
                                            echo $this->Html->link('<i class="fa fa-check"></i>', ['controller' => 'conventionregistrations', 'action' => 'approvejudgeregistration', $datarecord->slug], ['escape' => false, 'title' => 'Approve', 'class' => 'btn btn-success btn-xs', 'confirm' => 'Are you sure you want to approve this registration?']);
                                            echo $this->Html->link('<i class="fa fa-times"></i>', ['controller' => 'conventionregistrations', 'action' => 'declinejudgeregistration', $datarecord->slug], ['escape' => false, 'title' => 'Reject', 'class' => 'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to reject this registration?']);
                                        }
                                    }
                                    ?>
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm" style="display:none;">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Conventionregistrations.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="cr-empty">
        <i class="fa fa-inbox"></i>
        No registrations found. Try clearing your filters.
    </div>
<?php } ?>


<?php
if (!empty($conventionregistrations)) {
foreach ($conventionregistrations as $datarecord)
{
    if(!empty($datarecord->judges_event_ids))
    {
?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
                Events Selected By: <?php echo h(trim((string)($datarecord->Users->first_name ?? '').' '.(string)($datarecord->Users->last_name ?? ''))); ?>
                </legend>
                <div class="drt">
                    <?php
                    $cntrE = 1;
                    $condEv = array();
                    $condEv[] = "(Events.id IN (".$datarecord->judges_event_ids.") )";
                    $judgeEvents = $this->Events->find()->where($condEv)->order(["Events.event_name" =>"ASC"])->all();
                    foreach($judgeEvents as $judgeevent)
                    {
                    ?>
                    <div class="admin_pop">
                        <span><?php echo $cntrE.'. '.h($judgeevent->event_name); ?></span>
                        <label><?php echo h($judgeevent->event_id_number); ?></label>
                    </div>
                    <?php
                    $cntrE++;
                    }
                    ?>
                </div>
            </fieldset>
        </div>
    </div>
<?php }} } ?>


<script>
$(document).ready(function() {
$('#convention_registraions').dataTable({
    "bPaginate": true,
    "bLengthChange": false,
    "pageLength": 100,
    order: [[0, 'desc']],
    });
});
</script>

<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
    .page-link { color: #1c2452 !important; background-color: #fff !important; }
    .active>.page-link, .page-link.active { background-color: #1c2452 !important; border-color: #1c2452 !important; color: #fff !important; }
    .pagination { border-radius: 0rem !important; }
</style>
