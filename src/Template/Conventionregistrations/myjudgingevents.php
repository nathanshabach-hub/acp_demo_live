<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.jpa-season-header {
    background: #2980b9;
    color: #fff;
    font-weight: 600;
    padding: 10px 16px;
    border-radius: 4px 4px 0 0;
    font-size: 15px;
}
.jpa-season-header .badge-count {
    background: #fff;
    color: #2980b9;
    font-size: 12px;
    padding: 2px 8px;
    border-radius: 10px;
    margin-left: 8px;
    font-weight: 700;
}
.jpa-no-assignments {
    border-left: 4px solid #3498db;
    background: #f3f8fc;
    padding: 14px 16px;
    border-radius: 3px;
    margin-top: 16px;
    font-size: 14px;
}
</style>

<div class="container-fluid p-0">
    <div class="row">
        <?php echo $this->element('user_left_menu'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

            <div class="ersu_message">
                <?php echo $this->Flash->render() ?>
            </div>

            <div class="teachers-top-heading">
                <span><i class="fa fa-gavel"></i> My Judging Assignments</span>
            </div>

            <?php if (empty($portalData)) { ?>
                <div class="jpa-no-assignments mt-3">
                    <i class="fa fa-info-circle"></i>
                    You have not been assigned to any judging panels yet.
                    Assignments are confirmed by the convention admin — check back closer to the event date.
                </div>
            <?php } else { ?>

                <p class="text-muted" style="margin-top:12px; font-size:13px;">
                    Below are the events you are confirmed to judge. Please be ready before each event begins.
                </p>

                <?php foreach ($portalData as $group) {
                    $seasonLabel = 'Season #'.(int)$group['season_id'];
                    if (!empty($group['season'])) {
                        $s = $group['season'];
                        $conventionName = (!empty($s->Conventions)) ? h($s->Conventions->name) : '';
                        $seasonYear = h($s->season_year);
                        $seasonLabel = ($conventionName ? $conventionName . ' &mdash; ' : '') . $seasonYear;
                    }
                    $eventCount = count($group['events']);
                ?>
                <div style="margin-top:20px; border:1px solid #ddd; border-radius:4px; overflow:hidden;">
                    <div class="jpa-season-header">
                        <i class="fa fa-calendar"></i>
                        <?php echo $seasonLabel; ?>
                        <span class="badge-count"><?php echo $eventCount; ?> event<?php echo $eventCount !== 1 ? 's' : ''; ?></span>
                    </div>
                    <?php if (empty($group['events'])) { ?>
                        <div style="padding:12px 16px; color:#999;">No events found for this season.</div>
                    <?php } else { ?>
                    <table class="table table-bordered table-striped table-condensed mb-0" style="margin:0;">
                        <thead>
                            <tr>
                                <th style="width:90px;">Event Code</th>
                                <th>Event Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($group['events'] as $ev) { ?>
                            <tr>
                                <td><?php echo h($ev->event_id_number); ?></td>
                                <td><?php echo h($ev->event_name); ?></td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                    <?php } ?>
                </div>
                <?php } ?>

            <?php } ?>

        </main>
    </div>
</div>
