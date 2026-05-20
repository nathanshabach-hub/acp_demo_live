<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
$this->Judgeevaluations = TableRegistry::getTableLocator()->get('Judgeevaluations');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php
$total = 0; $evaluated = 0;
if (isset($eventsubmissions) && !$eventsubmissions->isEmpty()) {
    foreach ($eventsubmissions as $r) {
        $total++;
        $hasEval = $this->Judgeevaluations->find()->where(['Judgeevaluations.eventsubmission_id' => $r->id, 'Judgeevaluations.conventionregistration_id' => $r->conventionregistration_id])->count();
        if ($hasEval) { $evaluated++; }
    }
}
?>
<?php if ($total > 0) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <div style="padding:10px 14px; color:#5b6770; font-size:13px;">
            <strong><?php echo $total; ?></strong> submission<?php echo $total === 1 ? '' : 's'; ?>
            &middot; <strong><?php echo $evaluated; ?></strong> evaluated by judge
        </div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="tbl-resp-listing">
                <table id="event_submissions" class="table table-bordered table-hover table-condensed cf cr-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Convention</th>
                            <th>Season</th>
                            <th><?php echo $this->Paginator->sort('event_id_number', 'Event #'); ?></th>
                            <th>Event Name</th>
                            <th>For</th>
                            <th>Submitted By</th>
                            <th><?php echo $this->Paginator->sort('created', 'Date'); ?></th>
                            <th>Files</th>
                            <th class="action_dvv"><i class="fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventsubmissions as $datarecord) {
                            $isGroup = ($datarecord->Events['group_event_yes_no'] ?? 0) == 1;
                            $forLabel = '';
                            if ($isGroup && !empty($datarecord->group_name)) {
                                $forLabel = '<span class="cr-pill cr-pill-tp"><i class="fa fa-users"></i> Group ' . h($datarecord->group_name) . '</span>';
                            } elseif (($datarecord->student_id ?? 0) > 0) {
                                $stName = trim(($datarecord->Students['first_name'] ?? '') . ' ' . ($datarecord->Students['middle_name'] ?? '') . ' ' . ($datarecord->Students['last_name'] ?? ''));
                                $forLabel = '<span class="cr-pill cr-pill-school"><i class="fa fa-user"></i> ' . h($stName) . '</span>';
                            } else {
                                $forLabel = '<span style="color:#b8c2cc;">—</span>';
                            }

                            $files = [
                                ['file' => $datarecord->mediafile_file_system_name, 'label' => $datarecord->Events['upload_type'] ?? 'File'],
                                ['file' => $datarecord->report,                     'label' => 'Report'],
                                ['file' => $datarecord->score_sheet,                'label' => 'Score Sheet'],
                                ['file' => $datarecord->additional_documents,      'label' => 'Additional'],
                            ];
                            $hasEval = $this->Judgeevaluations->find()->where(['Judgeevaluations.eventsubmission_id' => $datarecord->id, 'Judgeevaluations.conventionregistration_id' => $datarecord->conventionregistration_id])->count();
                        ?>
                            <tr>
                                <td>#<?php echo h($datarecord->id); ?></td>
                                <td><?php echo h($datarecord->Conventions['name'] ?? ''); ?></td>
                                <td><?php echo h($datarecord->season_year); ?></td>
                                <td><?php echo h($datarecord->event_id_number); ?></td>
                                <td>
                                    <strong><?php echo h($datarecord->Events['event_name'] ?? ''); ?></strong>
                                    <?php if ($isGroup) { ?><br><small style="color:#7b8794;"><i class="fa fa-users"></i> Group event</small><?php } ?>
                                </td>
                                <td><?php echo $forLabel; ?></td>
                                <td><?php echo h(trim(($datarecord->Uploadeduser['first_name'] ?? '') . ' ' . ($datarecord->Uploadeduser['last_name'] ?? ''))); ?></td>
                                <td><?php echo safe_date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td>
                                    <div style="display:flex; flex-direction:column; gap:4px;">
                                    <?php foreach ($files as $f) {
                                        if (empty($f['file'])) { continue; }
                                        if (!file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH . $f['file'])) { continue; } ?>
                                        <a class="btn btn-info btn-xs" target="_blank" title="<?php echo h($f['label']); ?>" href="<?php echo DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH . h($f['file']); ?>"><i class="fa fa-cloud-download"></i> <?php echo h($f['label']); ?></a>
                                    <?php } ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($hasEval) { ?>
                                        <span class="cr-status cr-status-active"><span class="dot"></span> Evaluated</span>
                                    <?php } else { ?>
                                        <?php echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'eventsubmissions', 'action' => 'removesubmission', $datarecord->slug, $slug], ['escape' => false, 'title' => 'Remove', 'class'=>'btn btn-danger btn-xs', 'confirm' => 'Are you sure you want to remove this submission?']); ?>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Conventionregistrations.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        } ?>
        <?php echo $this->Form->end(); ?>
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="cr-empty"><i class="fa fa-database"></i>No event submissions yet.</div>
<?php } ?>

<script>
$(document).ready(function() {
    $('#event_submissions').dataTable({
        "bPaginate": true,
        "bLengthChange": false,
        "pageLength": 100,
        order: [[0, 'asc']]
    });
});
</script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<style>
.page-link { color:#1c2452 !important; background-color:#fff !important; }
.active>.page-link, .page-link.active { background-color:#1c2452 !important; border-color:#1c2452 !important; color:#fff !important; }
.pagination { border-radius:0 !important; }
</style>
