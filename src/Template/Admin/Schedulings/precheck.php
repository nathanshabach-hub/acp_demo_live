<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');

// Determine overall readiness
$allChecked = ($schedulingD->total_events_found !== null || $schedulingD->precheck_events > 0)
           && ($schedulingD->total_locations_missing !== null || $schedulingD->precheck_locations > 0)
           && ($schedulingD->total_registrations_found !== null || $schedulingD->precheck_registrations > 0)
           && ($schedulingD->total_students_found !== null || $schedulingD->precheck_students > 0);
$allPassed = $schedulingD->precheck_events > 0
          && $schedulingD->precheck_locations > 0
          && $schedulingD->precheck_registrations > 0
          && $schedulingD->precheck_students > 0;
$failCount = ($schedulingD->precheck_events > 0 ? 0 : ($allChecked ? 1 : 0))
           + ($schedulingD->precheck_locations > 0 ? 0 : ($allChecked ? 1 : 0))
           + ($schedulingD->precheck_registrations > 0 ? 0 : ($allChecked ? 1 : 0))
           + ($schedulingD->precheck_students > 0 ? 0 : ($allChecked ? 1 : 0));

// Conflict count
$totalConflict = 0;
if (!empty($schedulingD->conflict_user_ids)) {
    $totalConflict += count(explode(",", $schedulingD->conflict_user_ids));
}
if (!empty($schedulingD->conflict_user_ids_group)) {
    $totalConflict += count(explode(",", $schedulingD->conflict_user_ids_group));
}

// Card data
$evPassed  = $schedulingD->precheck_events > 0;
$evChecked = $evPassed || ($schedulingD->precheck_events === 0 && $allChecked);
$evStat    = $evPassed ? $schedulingD->total_events_found : null;
$evMsg     = $evPassed ? 'schedulable event(s) ready for scheduling' : 'No schedulable events found for this season';

$locPassed  = $schedulingD->precheck_locations > 0;
$locChecked = $locPassed || ($schedulingD->precheck_locations === 0 && $allChecked);
$locStat    = $locPassed ? $schedulingD->total_locations_found : null;
if ($locPassed) {
    $locMsg = 'room(s) assigned across scheduled events';
} elseif ($allChecked && $schedulingD->total_locations_missing > 0) {
    $locMsg = $schedulingD->total_locations_missing . ' event(s) not assigned to any room';
} else {
    $locMsg = 'No rooms configured for this convention';
}

$regPassed  = $schedulingD->precheck_registrations > 0;
$regChecked = $regPassed || ($schedulingD->precheck_registrations === 0 && $allChecked);
$regStat    = $regPassed ? $schedulingD->total_registrations_found : null;
$regMsg     = $regPassed ? 'registration(s) found for this season' : 'No registrations found for this season';

$stuPassed  = $schedulingD->precheck_students > 0;
$stuChecked = $stuPassed || ($schedulingD->precheck_students === 0 && $allChecked);
$stuStat    = $stuPassed ? $schedulingD->total_students_found : null;
$stuMsg     = $stuPassed ? 'student(s) eligible for scheduling' : 'No students found for this convention';
?>
<style>
.pc-card {
    background: #fff;
    border-radius: 6px;
    border: 1px solid #ddd;
    border-left-width: 5px;
    padding: 16px 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    box-shadow: 0 1px 3px rgba(0,0,0,.06);
}
.pc-card.pass  { border-left-color: #27ae60; }
.pc-card.fail  { border-left-color: #c0392b; }
.pc-card.unchecked { border-left-color: #bbb; }
.pc-card .pc-icon {
    width: 52px; height: 52px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0; font-size: 22px; color: #fff;
}
.pc-card.pass .pc-icon  { background: #27ae60; }
.pc-card.fail .pc-icon  { background: #c0392b; }
.pc-card.unchecked .pc-icon { background: #bbb; }
.pc-card .pc-body { flex: 1; min-width: 0; }
.pc-card .pc-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: #999; margin-bottom: 2px; }
.pc-card .pc-stat  { font-size: 28px; font-weight: 700; line-height: 1; color: #222; margin-bottom: 3px; }
.pc-card .pc-msg   { font-size: 12px; color: #666; }
.pc-card .pc-badge { flex-shrink: 0; font-size: 11px; font-weight: 700; letter-spacing: .5px; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; }
.pc-card.pass .pc-badge  { background: #eafaf1; color: #27ae60; border: 1px solid #a9dfbf; }
.pc-card.fail .pc-badge  { background: #fdecea; color: #c0392b; border: 1px solid #f1aaa5; }
.pc-card.unchecked .pc-badge { background: #f5f5f5; color: #aaa; border: 1px solid #ddd; }
.pc-banner { display: flex; align-items: center; gap: 12px; padding: 13px 18px; border-radius: 6px; margin-bottom: 20px; font-size: 14px; font-weight: 600; }
.pc-banner.success { background: #eafaf1; border: 1px solid #a9dfbf; color: #1e8449; }
.pc-banner.danger  { background: #fdecea; border: 1px solid #f1aaa5; color: #922b21; }
.pc-banner.warning { background: #fef9e7; border: 1px solid #f9e79f; color: #7d6608; }
.pc-banner i { font-size: 20px; }
.pc-footer-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; }
.pc-footer-nav .pc-nav-divider { width: 1px; height: 24px; background: #ddd; margin: 0 4px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Scheduling Pre-check
            <small><?php echo h($conventionSD->Conventions['name']); ?> &mdash; <?php echo h($conventionSD->season_year); ?></small>
        </h1>
        <ol class="breadcrumb">
            <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Conventions', ['controller'=>'conventions', 'action'=>'index']); ?></li>
            <li><?php echo $this->Html->link('Seasons', ['controller'=>'conventions', 'action'=>'seasons', $convention_slug]); ?></li>
            <li class="active">Scheduling Pre-check</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-check-square-o"></i>&nbsp; Pre-check Status</h3>
                <div class="box-tools pull-right" style="display:flex; gap:6px; align-items:center;">
                    <?php echo $this->Html->link(
                        '<i class="fa fa-refresh"></i> Re-Check All',
                        ['controller'=>'schedulings', 'action'=>'precheckall', $convention_season_slug],
                        ['escape'=>false, 'class'=>'btn btn-primary btn-sm', 'confirm'=>'Re-check all items (Events, Locations, Registrations, Students)?']
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-undo"></i> Reset',
                        ['controller'=>'schedulings', 'action'=>'resetallprecheck', $convention_season_slug],
                        ['escape'=>false, 'class'=>'btn btn-default btn-sm', 'confirm'=>'Are you sure you want to reset all pre-checks?']
                    ); ?>
                </div>
            </div>

            <div class="box-body">
                <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

                <?php /* ── Status Banner ── */ ?>
                <?php if (!$allChecked): ?>
                    <div class="pc-banner warning">
                        <i class="fa fa-clock-o"></i>
                        <span>Pre-checks have not been run yet &mdash; click <strong>Re-Check All</strong> to begin.</span>
                    </div>
                <?php elseif ($allPassed): ?>
                    <div class="pc-banner success">
                        <i class="fa fa-check-circle"></i>
                        <span>All 4 checks passed &mdash; this season is <strong>ready to schedule</strong>.</span>
                        <?php if ($totalConflict > 0): ?>
                            <span style="margin-left:auto; font-weight:400; font-size:13px;">
                                <i class="fa fa-exclamation-triangle" style="color:#e67e22;"></i>
                                <?php echo $totalConflict; ?> conflict(s) detected
                            </span>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="pc-banner danger">
                        <i class="fa fa-times-circle"></i>
                        <span><strong><?php echo $failCount; ?> of 4 check<?php echo $failCount > 1 ? 's' : ''; ?> failed</strong> &mdash; resolve the issues below before scheduling.</span>
                    </div>
                <?php endif; ?>

                <?php /* ── Check Cards ── */ ?>
                <?php
                $renderCard = function($label, $iconClass, $passed, $checked, $stat, $msg) {
                    $state = !$checked ? 'unchecked' : ($passed ? 'pass' : 'fail');
                    $statusIcon = !$checked ? 'fa-minus' : ($passed ? 'fa-check' : 'fa-times');
                    $badgeText = !$checked ? 'Pending' : ($passed ? 'Passed' : 'Failed');
                    echo '<div class="pc-card '.$state.'">';
                    echo '<div class="pc-icon"><i class="fa '.$iconClass.'"></i></div>';
                    echo '<div class="pc-body">';
                    echo '<div class="pc-label">'.$label.'</div>';
                    if ($passed && $stat !== null) {
                        echo '<div class="pc-stat">'.$stat.'</div>';
                    }
                    echo '<div class="pc-msg">'.($checked ? $msg : 'Not yet checked').'</div>';
                    echo '</div>';
                    echo '<span class="pc-badge"><i class="fa '.$statusIcon.'"></i>&nbsp; '.$badgeText.'</span>';
                    echo '</div>';
                };
                ?>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:24px;">
                    <?php $renderCard('Events',        'fa-calendar-check-o', $evPassed,  $evChecked,  $evStat,  $evMsg);  ?>
                    <?php $renderCard('Locations',     'fa-map-marker',       $locPassed, $locChecked, $locStat, $locMsg); ?>
                    <?php $renderCard('Registrations', 'fa-id-card-o',        $regPassed, $regChecked, $regStat, $regMsg); ?>
                    <?php $renderCard('Students',      'fa-users',            $stuPassed, $stuChecked, $stuStat, $stuMsg); ?>
                </div>
            </div>

            <div class="box-footer">
                <div class="pc-footer-nav">
                    <?php echo $this->Html->link(
                        '<i class="fa fa-arrow-left"></i> Back To Seasons',
                        ['controller'=>'conventions', 'action'=>'seasons', $convention_slug],
                        ['escape'=>false, 'class'=>'btn btn-default btn-sm']
                    ); ?>

                    <div class="pc-nav-divider"></div>

                    <?php echo $this->Html->link(
                        '<i class="fa fa-magic"></i> Scheduling Wizard',
                        ['controller'=>'schedulings', 'action'=>'wizard', $convention_season_slug],
                        ['escape'=>false, 'class'=>'btn btn-success btn-sm']
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-play-circle"></i> View / Start Scheduling',
                        ['controller'=>'schedulings', 'action'=>'schedulecategory', $convention_season_slug],
                        ['escape'=>false, 'class'=>'btn btn-success btn-sm']
                    ); ?>

                    <div class="pc-nav-divider"></div>

                    <?php echo $this->Html->link(
                        '<i class="fa fa-sliders"></i> Scheduling Tweaks',
                        ['controller'=>'schedulingtweaks', 'action'=>'index', $convention_season_slug],
                        ['escape'=>false, 'class'=>'btn btn-info btn-sm']
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-clock-o"></i> Overwrite Timings',
                        ['controller'=>'schedulings', 'action'=>'overwritetimings', $convention_season_slug],
                        ['escape'=>false, 'class'=>'btn btn-warning btn-sm']
                    ); ?>
                    <?php echo $this->Html->link(
                        '<i class="fa fa-file-text-o"></i> Reports',
                        ['controller'=>'schedulings', 'action'=>'reports', $convention_season_slug],
                        ['escape'=>false, 'class'=>'btn btn-default btn-sm']
                    ); ?>

                    <?php if ($totalConflict > 0): ?>
                        <div class="pc-nav-divider"></div>
                        <?php echo $this->Html->link(
                            '<i class="fa fa-exclamation-triangle"></i> Resolve Conflicts ('.$totalConflict.' found)',
                            ['controller'=>'schedulings', 'action'=>'resolveconflicts', $convention_season_slug],
                            ['escape'=>false, 'class'=>'btn btn-danger btn-sm']
                        ); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>
