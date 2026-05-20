<?php
use Cake\ORM\TableRegistry;
$this->Schedulingtimings = TableRegistry::getTableLocator()->get('Schedulingtimings');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Schedule Category - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Schedule Category </li>
      </ol>
    </section>

    <section class="content">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>

        <!-- Info Alert - Collapsible -->
        <div class="alert alert-info alert-dismissible" role="alert">
            <button type="button" class="close" data-toggle="collapse" data-target="#scheduleInfo" aria-label="Toggle Info">
                <span aria-hidden="true"><i class="fa fa-chevron-down"></i></span>
            </button>
            <strong><i class="fa fa-info-circle"></i> Important Information</strong>
            <div id="scheduleInfo" class="collapse in" style="margin-top: 10px;">
                <ul style="margin-bottom: 0;">
                    <li>Schedulings for all categories will be done when you press <strong>"Start Scheduling"</strong> button.</li>
                    <li>If the button is not visible, there are no events in one or more categories.</li>
                    <li>Scheduling may take time. Please be patient while processing.</li>
                    <li>All previous scheduling will reset and start from scratch for this convention season.</li>
                    <li>You can perform "Overwrite Timings" after scheduling to resolve conflicts.</li>
                </ul>
            </div>
        </div>

        <!-- Status Bar -->
        <?php
            $c1Count = count($arrEventsC1);
            $c2Count = count($arrEventsC2);
            $c3Count = count($arrEventsC3);
            $c4Count = count($arrEventsC4);
            $totalEvents = $c1Count + $c2Count + $c3Count + $c4Count;
            $allReady = $c1Count > 0 && $c2Count > 0 && $c3Count > 0 && $c4Count > 0;
            $progressPercent = ($allReady ? 100 : 75);
        ?>
        <div class="row" style="margin-bottom: 20px;">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 style="margin: 0 0 10px 0;">Overall Status</h4>
                                <div class="progress" style="height: 25px; margin-bottom: 5px;">
                                    <div class="progress-bar progress-bar-<?php echo $allReady ? 'success' : 'warning'; ?>" role="progressbar" style="width: <?php echo $progressPercent; ?>%">
                                        <?php echo $allReady ? '✓ Ready to Schedule' : '⚠ Not Ready'; ?>
                                    </div>
                                </div>
                                <small class="text-muted">
                                    <?php echo $totalEvents; ?> total events across all categories
                                </small>
                            </div>
                            <div class="col-md-4 text-right">
                                <?php
                                if($allReady)
                                {
                                    echo $this->Html->link(
                                        '<i class="fa fa-play"></i> Start Scheduling',
                                        ['controller'=>'schedulingtimings', 'action' => 'startschedulec1',$convention_season_slug],
                                        [
                                            'class'=>'btn btn-success btn-lg',
                                            'confirm' => 'Are you sure you want to start scheduling? This will reset all previous scheduling.',
                                            'escape'=>false
                                        ]
                                    );
                                }
                                else
                                {
                                    echo '<button class="btn btn-success btn-lg" disabled><i class="fa fa-play"></i> Start Scheduling</button>';
                                    echo '<br/><small class="text-muted" style="display: block; margin-top: 5px;">All categories must have events</small>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Category Cards Grid -->
        <div class="row">
            <?php
            $categories = [
                [
                    'number' => 1,
                    'type' => 'Sequential',
                    'groupEvent' => 'Yes',
                    'consecutive' => 'Yes',
                    'count' => $c1Count,
                    'color' => 'primary',
                    'icon' => 'fa-bars'
                ],
                [
                    'number' => 2,
                    'type' => 'Elimination',
                    'groupEvent' => 'No',
                    'consecutive' => 'No',
                    'count' => $c2Count,
                    'color' => 'warning',
                    'icon' => 'fa-trophy'
                ],
                [
                    'number' => 3,
                    'type' => 'Elimination',
                    'groupEvent' => 'Yes',
                    'consecutive' => 'No',
                    'count' => $c3Count,
                    'color' => 'info',
                    'icon' => 'fa-star'
                ],
                [
                    'number' => 4,
                    'type' => 'Sequential',
                    'groupEvent' => 'No',
                    'consecutive' => 'Yes',
                    'count' => $c4Count,
                    'color' => 'danger',
                    'icon' => 'fa-list'
                ]
            ];

            foreach($categories as $cat):
                $hasEvents = $cat['count'] > 0;
                $isScheduled = false;
                if($hasEvents):
                    $checkScheduling = $this->Schedulingtimings->find()->where([
                        'Schedulingtimings.schedule_category' => $cat['number'],
                        'Schedulingtimings.conventionseasons_id' => $conventionSD->id,
                        'Schedulingtimings.convention_id' => $conventionSD->convention_id,
                        'Schedulingtimings.season_id' => $conventionSD->season_id,
                        'Schedulingtimings.season_year' => $conventionSD->season_year
                    ])->first();
                    $isScheduled = !empty($checkScheduling);
                endif;
                $statusBadge = $hasEvents ? ($isScheduled ? '<span class="label label-success"><i class="fa fa-check"></i> Scheduled</span>' : '<span class="label label-warning"><i class="fa fa-clock-o"></i> Pending</span>') : '<span class="label label-default">No Events</span>';
            ?>
            <div class="col-md-6 col-lg-3 col-xs-12" style="margin-bottom: 20px;">
                <div class="box box-<?php echo $cat['color']; ?> <?php echo $isScheduled ? 'box-solid' : ''; ?>">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            <i class="fa <?php echo $cat['icon']; ?>"></i>
                            Category <?php echo $cat['number']; ?>
                        </h3>
                        <div class="box-tools pull-right">
                            <?php echo $statusBadge; ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <!-- Event Count (KPI Style) -->
                        <div style="text-align: center; margin-bottom: 15px;">
                            <h2 style="margin: 0; color: #<?php echo $hasEvents ? '333' : 'ccc'; ?>; font-weight: bold;">
                                <?php echo $cat['count']; ?>
                            </h2>
                            <small>Events</small>
                        </div>

                        <!-- Category Details -->
                        <table class="table table-condensed" style="margin-bottom: 10px; font-size: 12px;">
                            <tr>
                                <td><strong>Type:</strong></td>
                                <td><?php echo $cat['type']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Group Event:</strong></td>
                                <td><?php echo $cat['groupEvent']; ?></td>
                            </tr>
                            <tr>
                                <td><strong>Consecutive:</strong></td>
                                <td><?php echo $cat['consecutive']; ?></td>
                            </tr>
                        </table>

                        <!-- Action Button -->
                        <?php
                        if($hasEvents):
                            if($isScheduled):
                                echo $this->Html->link(
                                    '<i class="fa fa-eye"></i> View Scheduling',
                                    ['controller'=>'schedulingtimings', 'action' => 'viewscheduling',$convention_season_slug,$cat['number']],
                                    ['class'=>'btn btn-sm btn-success btn-block', 'escape'=>false]
                                );
                            else:
                                echo '<button class="btn btn-sm btn-default btn-block" disabled><i class="fa fa-hourglass"></i> Awaiting Scheduling</button>';
                            endif;
                        else:
                            echo '<button class="btn btn-sm btn-default btn-block" disabled><i class="fa fa-minus"></i> No Events</button>';
                        endif;
                        ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Footer Actions -->
        <div class="row" style="margin-top: 20px;">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-footer">
                        <?php
                        echo $this->Html->link(
                            '<i class="fa fa-arrow-left"></i> Back To Pre-check',
                            ['controller'=>'schedulings', 'action' => 'precheck',$convention_season_slug],
                            ['class'=>'btn btn-default', 'escape'=>false]
                        );
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
  </div>