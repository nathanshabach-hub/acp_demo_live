<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.jl-metric {
    border-radius: 4px;
    padding: 10px 12px;
    color: #fff;
    margin-bottom: 12px;
}
.jl-metric h3 {
    margin: 0;
    font-size: 24px;
    font-weight: 700;
}
.jl-metric p {
    margin: 2px 0 0 0;
    font-size: 13px;
}
.jl-metric.blue { background: #2980b9; }
.jl-metric.green { background: #27ae60; }
.jl-metric.orange { background: #e67e22; }
.jl-metric.red { background: #c0392b; }
.jl-list {
    margin: 0;
    padding-left: 18px;
}
.jl-list li {
    margin-bottom: 3px;
}
.jl-empty {
    color: #999;
    font-style: italic;
}
.jl-note {
    border-left: 4px solid #3498db;
    background: #f3f8fc;
    padding: 10px 12px;
    border-radius: 3px;
    margin-bottom: 12px;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Judging List
         <small><?php echo h($convSeasonD->slug); ?></small>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li class="active">Judging List</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-check-square-o"></i>&nbsp; Judging Coverage Draft</h3>
            </div>
            <div class="panel-body">
                <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

                <div class="jl-note">
                    This page builds a draft panel from judge-selected events. Use the 2-judge and 3-judge suggestions as a starting point, then finalize manually.
                </div>

                <div class="row">
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric blue">
                            <h3><?php echo count($eventJudgeRows); ?></h3>
                            <p>Events In Season</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric green">
                            <h3><?php echo $totalJudgesInPool; ?></h3>
                            <p>Judges In Pool</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric orange">
                            <h3><?php echo $eventsWithUnderTwo; ?></h3>
                            <p>Events With &lt; 2 Judges</p>
                        </div>
                    </div>
                    <div class="col-lg-3 col-sm-6">
                        <div class="jl-metric red">
                            <h3><?php echo $eventsWithUnderThree; ?></h3>
                            <p>Events With &lt; 3 Judges</p>
                        </div>
                    </div>
                </div>

                <?php if (!empty($eventJudgeRows)) { ?>
                <section id="no-more-tables" class="lstng-section">
                    <div class="tbl-resp-listing">
                        <table id="judging_list_table" class="table table-bordered table-striped table-condensed cf">
                            <thead class="cf ajshort">
                                <tr>
                                    <th>#ID</th>
                                    <th>Event ID Number</th>
                                    <th>Event Name</th>
                                    <th>Preferred Judges</th>
                                    <th>Suggested 2-Judge Panel</th>
                                    <th>Suggested 3-Judge Panel</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($eventJudgeRows as $row) { ?>
                                <tr>
                                    <td data-title="ID"><?php echo (int)$row['event_id']; ?></td>
                                    <td data-title="Event ID Number"><?php echo h($row['event_id_number']); ?></td>
                                    <td data-title="Event Name"><?php echo h($row['event_name']); ?></td>
                                    <td data-title="Preferred Judges">
                                        <?php if (!empty($row['preferred_names'])) { ?>
                                            <ul class="jl-list">
                                                <?php foreach ($row['preferred_names'] as $name) { ?>
                                                    <li><?php echo h($name); ?></li>
                                                <?php } ?>
                                            </ul>
                                        <?php } else { ?>
                                            <span class="jl-empty">No judges selected this event yet</span>
                                        <?php } ?>
                                    </td>
                                    <td data-title="Suggested 2-Judge Panel">
                                        <?php if (!empty($row['panel_two_names'])) { ?>
                                            <ul class="jl-list">
                                                <?php foreach ($row['panel_two_names'] as $name) { ?>
                                                    <li><?php echo h($name); ?></li>
                                                <?php } ?>
                                            </ul>
                                        <?php } else { ?>
                                            <span class="jl-empty">Need more judge preferences</span>
                                        <?php } ?>
                                    </td>
                                    <td data-title="Suggested 3-Judge Panel">
                                        <?php if (!empty($row['panel_three_names'])) { ?>
                                            <ul class="jl-list">
                                                <?php foreach ($row['panel_three_names'] as $name) { ?>
                                                    <li><?php echo h($name); ?></li>
                                                <?php } ?>
                                            </ul>
                                        <?php } else { ?>
                                            <span class="jl-empty">Need more judge preferences</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </section>
                <?php } else { ?>
                    <div class="alert alert-warning">No season events found. Add events to this season first.</div>
                <?php } ?>
            </div>
            <div class="box-footer">
                <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['class'=>'btn btn-default', 'escape'=>false]); ?>
                <?php echo $this->Html->link('<i class="fa fa-user"></i> Manage Judges', ['controller'=>'conventionregistrations', 'action'=>'alljudges'], ['class'=>'btn btn-info', 'escape'=>false]); ?>
            </div>
        </div>
    </section>
</div>
