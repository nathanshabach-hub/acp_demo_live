<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php
$total = 0;
$judgeCount = 0;
if (isset($conventionregistrationteachers) && !$conventionregistrationteachers->isEmpty()) {
    foreach ($conventionregistrationteachers as $r) {
        $total++;
        if (($r->Teachers['is_judge'] ?? 0) == 1) { $judgeCount++; }
    }
}
?>
<?php if ($total > 0) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <div style="padding:10px 14px; color:#5b6770; font-size:13px;">
            <strong><?php echo $total; ?></strong> supervisor<?php echo $total === 1 ? '' : 's'; ?>
            &middot; <strong><?php echo $judgeCount; ?></strong> also judging
        </div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]); ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn" style="display:flex; justify-content:space-between; align-items:center; padding:8px 14px;">
                <div class="topn_left" style="font-weight:600;">Supervisors</div>
                <div class="topn_right ajshort" id="pagingLinks">
                    <?php
                        $this->Paginator->options(['update' => '#listID', 'url' => ['controller'=>'conventionregistrationteachers', 'action'=>'teachers', $slug, $separator]]);
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
                            <th>Title</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Gender</th>
                            <th>Judge?</th>
                            <th><?php echo $this->Paginator->sort('created', 'Registered'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cntrT = 0;
                        foreach ($conventionregistrationteachers as $datarecord) {
                            $cntrT++;
                            $isJudge = ($datarecord->Teachers['is_judge'] ?? 0) == 1;
                        ?>
                            <tr>
                                <td><?php echo $cntrT; ?></td>
                                <td><?php echo h($datarecord->Teachers['title'] ?? ''); ?></td>
                                <td><strong><?php echo h(trim(($datarecord->Teachers['first_name'] ?? '') . ' ' . ($datarecord->Teachers['last_name'] ?? ''))); ?></strong></td>
                                <td><?php echo !empty($datarecord->Teachers['email_address']) ? '<i class="fa fa-envelope-o" style="color:#7b8794;"></i> ' . h($datarecord->Teachers['email_address']) : '<span style="color:#b8c2cc;">—</span>'; ?></td>
                                <td><?php echo h($datarecord->Teachers['gender'] ?? ''); ?></td>
                                <td>
                                    <?php if ($isJudge) { ?>
                                        <span class="cr-pill cr-pill-judge"><i class="fa fa-gavel"></i> Judge</span>
                                    <?php } else { ?>
                                        <span style="color:#b8c2cc;">—</span>
                                    <?php } ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
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
    <div class="cr-empty"><i class="fa fa-user-secret"></i>No supervisors registered yet.</div>
<?php } ?>
