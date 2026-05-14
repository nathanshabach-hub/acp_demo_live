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
<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php
$total = 0; $boys = 0; $girls = 0;
if (isset($conventionregistrationstudents) && !$conventionregistrationstudents->isEmpty()) {
    foreach ($conventionregistrationstudents as $r) {
        $total++;
        $g = strtolower((string)($r->Students['gender'] ?? ''));
        if ($g === 'male' || $g === 'm' || $g === 'boy') { $boys++; }
        elseif ($g === 'female' || $g === 'f' || $g === 'girl') { $girls++; }
    }
}
?>
<?php if ($total > 0) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <div style="padding:10px 14px; color:#5b6770; font-size:13px;">
            <strong><?php echo $total; ?></strong> student<?php echo $total === 1 ? '' : 's'; ?>
            <?php if ($boys || $girls) { ?> &middot; <?php echo $boys; ?> male &middot; <?php echo $girls; ?> female<?php } ?>
        </div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn" style="display:flex; justify-content:space-between; align-items:center; padding:8px 14px;">
                <div class="topn_left" style="font-weight:600;">Students</div>
                <div class="topn_right ajshort" id="pagingLinks">
                    <?php
                        $this->Paginator->options(['update' => '#listID', 'url' => ['controller'=>'conventionregistrations', 'action'=>'students', $slug, $separator]]);
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                    ?>
                </div>
            </div>

            <div class="tbl-resp-listing">
                <table class="table table-bordered table-hover table-condensed cf cr-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Birth Year</th>
                            <th>Gender</th>
                            <th>Supervisor</th>
                            <th><?php echo $this->Paginator->sort('created', 'Registered'); ?></th>
                            <th>Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cntrS = 0;
                        foreach ($conventionregistrationstudents as $datarecord) {
                            $cntrS++;
                            $fullName = trim(($datarecord->Students['first_name'] ?? '') . ' ' . ($datarecord->Students['middle_name'] ?? '') . ' ' . ($datarecord->Students['last_name'] ?? ''));
                            $supName  = trim(($datarecord->Teachers['first_name'] ?? '') . ' ' . ($datarecord->Teachers['last_name'] ?? ''));
                            $eventCount = !empty($datarecord->event_ids) ? count(explode(',', $datarecord->event_ids)) : 0;
                        ?>
                            <tr>
                                <td><?php echo $cntrS; ?></td>
                                <td><strong><?php echo h($fullName); ?></strong></td>
                                <td><?php echo h($datarecord->Students['birth_year'] ?? ''); ?></td>
                                <td><?php echo h($datarecord->Students['gender'] ?? ''); ?></td>
                                <td><?php echo $supName !== '' ? h($supName) : '<span style="color:#b8c2cc;">—</span>'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
                                <td>
                                    <?php if ($eventCount > 0) { ?>
                                        <a href="#info<?php echo $datarecord->id; ?>" rel="facebox" title="View events" class="btn btn-info btn-xs"><i class="fa fa-eye"></i> <?php echo $eventCount; ?></a>
                                    <?php } else { ?>
                                        <span style="color:#b8c2cc;">—</span>
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
    <div class="cr-empty"><i class="fa fa-graduation-cap"></i>No students registered yet.</div>
<?php } ?>

<?php if (!empty($conventionregistrationstudents)) { foreach ($conventionregistrationstudents as $datarecord) { ?>
    <div id="info<?php echo $datarecord->id; ?>" style="display:none;">
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
                <?php echo h(trim(($datarecord->Students['first_name'] ?? '') . ' ' . ($datarecord->Students['middle_name'] ?? '') . ' ' . ($datarecord->Students['last_name'] ?? ''))); ?>
                [Total Events: <?php echo !empty($datarecord->event_ids) ? count(explode(',', $datarecord->event_ids)) : 0; ?>]
                </legend>
                <div class="drt">
                    <table class="table table-bordered table-striped table-condensed cf cr-table">
                        <?php if (!empty($datarecord->event_ids)) {
                            $eventsL = $this->Events->find()->where(["(Events.id IN ({$datarecord->event_ids}))"])->all(); ?>
                            <thead><tr><th>Event #</th><th>Event Name</th></tr></thead>
                            <tbody>
                            <?php foreach ($eventsL as $eventd) { ?>
                                <tr>
                                    <td><?php echo h($eventd->event_id_number); ?></td>
                                    <td><?php echo h($eventd->event_name); ?></td>
                                </tr>
                            <?php } ?>
                            </tbody>
                        <?php } else { ?>
                            <tr><td>Sorry, no event found.</td></tr>
                        <?php } ?>
                    </table>
                </div>
            </fieldset>
        </div>
    </div>
<?php }} ?>
