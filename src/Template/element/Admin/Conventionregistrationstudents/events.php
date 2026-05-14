<?php
use Cake\ORM\TableRegistry;
$this->Crstudentevents = TableRegistry::getTableLocator()->get('Crstudentevents');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!empty($CRStudentEventsList) && count($CRStudentEventsList) > 0) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <div style="padding:10px 14px; color:#5b6770; font-size:13px;">
            <strong><?php echo count($CRStudentEventsList); ?></strong> event<?php echo count($CRStudentEventsList) === 1 ? '' : 's'; ?> registered
        </div>

        <section id="no-more-tables" class="lstng-section">
            <div class="tbl-resp-listing">
                <table id="convention_registraions" class="table table-bordered table-hover table-condensed cf cr-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Event #</th>
                            <th>Event Name</th>
                            <th>Type</th>
                            <th>Min/Max</th>
                            <th>Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cntr = 0;
                        foreach ($CRStudentEventsList as $eventD) {
                            $cntr++;
                            $count = $this->Crstudentevents->find()->where([
                                'Crstudentevents.conventionregistration_id' => $CRDetails->id,
                                'Crstudentevents.convention_id' => $CRDetails->convention_id,
                                'Crstudentevents.season_id' => $CRDetails->season_id,
                                'Crstudentevents.season_year' => $CRDetails->season_year,
                                'Crstudentevents.event_id' => $eventD->id,
                            ])->count();
                            $isGroup = ($eventD->group_event_yes_no ?? 0) == 1;
                        ?>
                            <tr>
                                <td><?php echo $cntr; ?></td>
                                <td><strong><?php echo h($eventD->event_id_number); ?></strong></td>
                                <td><?php echo h($eventD->event_name); ?></td>
                                <td>
                                    <?php if ($isGroup) { ?>
                                        <span class="cr-pill cr-pill-tp"><i class="fa fa-users"></i> Group</span>
                                    <?php } else { ?>
                                        <span class="cr-pill cr-pill-school"><i class="fa fa-user"></i> Individual</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo (int)$eventD->min_no; ?> / <?php echo (int)$eventD->max_no; ?></td>
                                <td><strong><?php echo (int)$count; ?></strong></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="cr-empty"><i class="fa fa-puzzle-piece"></i>No events registered yet.</div>
<?php } ?>

<script>
$(document).ready(function() {
    $('#convention_registraions').dataTable({
        "bPaginate": true,
        "bLengthChange": false,
        "pageLength": 100,
        order: [[1, 'asc']]
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
