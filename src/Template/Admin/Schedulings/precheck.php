<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
?>
<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });

</script>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Scheduling Pre-check - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Pre-check </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">&nbsp;</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			
			
			<?php
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
			?>

			<div style="padding: 0 20px 10px;">

				<?php if(!$allChecked): ?>
					<div class="alert alert-warning" style="border-radius:6px; font-size:15px;">
						<i class="fa fa-question-circle"></i>&nbsp; <strong>Not yet checked</strong> — click <strong>Re-Check All</strong> to run all pre-checks.
					</div>
				<?php elseif($allPassed): ?>
					<div class="alert alert-success" style="border-radius:6px; font-size:15px;">
						<i class="fa fa-check-circle"></i>&nbsp; <strong>All checks passed</strong> — this season is ready to schedule.
					</div>
				<?php else: ?>
					<div class="alert alert-danger" style="border-radius:6px; font-size:15px;">
						<i class="fa fa-times-circle"></i>&nbsp; <strong><?php echo $failCount; ?> of 4 check<?php echo $failCount > 1 ? 's' : ''; ?> failed</strong> — resolve the issues below before scheduling.
					</div>
				<?php endif; ?>

				<?php
				// Helper to render one status card
				$renderCard = function($label, $icon, $passed, $checked, $detail) {
					if (!$checked) {
						$borderColor = '#aaa'; $bg = '#f9f9f9'; $iconColor = '#aaa'; $iconClass = 'fa-question-circle';
					} elseif ($passed) {
						$borderColor = '#3c763d'; $bg = '#dff0d8'; $iconColor = '#3c763d'; $iconClass = 'fa-check-circle';
					} else {
						$borderColor = '#a94442'; $bg = '#f2dede'; $iconColor = '#a94442'; $iconClass = 'fa-times-circle';
					}
					echo '<div style="border:2px solid '.$borderColor.'; border-radius:8px; background:'.$bg.'; padding:18px 20px; display:flex; align-items:center; gap:18px;">';
					echo '<i class="fa '.$iconClass.'" style="font-size:38px; color:'.$iconColor.'; flex-shrink:0;"></i>';
					echo '<div>';
					echo '<div style="font-size:16px; font-weight:700; color:#333;">'.$label.'</div>';
					echo '<div style="margin-top:4px; font-size:13px; color:#555;">'.$detail.'</div>';
					echo '</div>';
					echo '</div>';
				};

				// Events
				$evPassed  = $schedulingD->precheck_events > 0;
				$evChecked = $evPassed || $schedulingD->total_events_found !== null || ($schedulingD->precheck_events === 0 && $schedulingD->total_events_found === null && $allChecked);
				// treat "ran but null" as checked+failed
				$evChecked = $evPassed || ($schedulingD->precheck_events === 0 && $allChecked);
				$evDetail  = $evPassed
					? '<span style="color:#3c763d;">'.($schedulingD->total_events_found).' schedulable event(s) found</span>'
					: ($allChecked ? '<span style="color:#a94442;">No schedulable events found for this season</span>' : '<em style="color:#aaa;">Not checked yet</em>');

				// Locations
				$locPassed  = $schedulingD->precheck_locations > 0;
				$locChecked = $locPassed || ($schedulingD->precheck_locations === 0 && $allChecked);
				if ($locPassed) {
					$locDetail = '<span style="color:#3c763d;">'.($schedulingD->total_locations_found).' location(s) assigned</span>';
				} elseif ($allChecked) {
					if ($schedulingD->total_locations_missing > 0) {
						$locDetail = '<span style="color:#e67e22;">'.$schedulingD->total_locations_missing.' event(s) not assigned to any room</span>';
					} else {
						$locDetail = '<span style="color:#a94442;">No rooms configured for this convention</span>';
					}
				} else {
					$locDetail = '<em style="color:#aaa;">Not checked yet</em>';
				}

				// Registrations
				$regPassed  = $schedulingD->precheck_registrations > 0;
				$regChecked = $regPassed || ($schedulingD->precheck_registrations === 0 && $allChecked);
				$regDetail  = $regPassed
					? '<span style="color:#3c763d;">'.($schedulingD->total_registrations_found).' registration(s) found</span>'
					: ($allChecked ? '<span style="color:#a94442;">No registrations found for this season</span>' : '<em style="color:#aaa;">Not checked yet</em>');

				// Students
				$stuPassed  = $schedulingD->precheck_students > 0;
				$stuChecked = $stuPassed || ($schedulingD->precheck_students === 0 && $allChecked);
				$stuDetail  = $stuPassed
					? '<span style="color:#3c763d;">'.($schedulingD->total_students_found).' student(s) found</span>'
					: ($allChecked ? '<span style="color:#a94442;">No students found for this convention</span>' : '<em style="color:#aaa;">Not checked yet</em>');
				?>

				<div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
					<?php $renderCard('Events',        'fa-calendar',    $evPassed,  $evChecked,  $evDetail);  ?>
					<?php $renderCard('Locations',     'fa-map-marker',  $locPassed, $locChecked, $locDetail); ?>
					<?php $renderCard('Registrations', 'fa-user-plus',   $regPassed, $regChecked, $regDetail); ?>
					<?php $renderCard('Students',      'fa-graduation-cap', $stuPassed, $stuChecked, $stuDetail); ?>
				</div>

			</div>
			
			
			
			<?php echo $this->Form->create(null, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
					<div class="box-body">
                    <div class="box-footer" style="display:flex; flex-wrap:wrap; align-items:center; gap:8px; justify-content:space-between;">

						<div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
							<?php echo $this->Html->link('<< Back To Seasons', ['controller'=>'conventions', 'action' => 'seasons',$convention_slug], ['class'=>'btn btn-default']); ?>
						</div>

						<div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
							<span style="font-size:12px; color:#999; margin-right:4px;">Pre-check:</span>
							<?php echo $this->Html->link('Re-Check All', ['controller'=>'schedulings', 'action' => 'precheckall',$convention_season_slug], ['class'=>'btn btn-primary', 'confirm' => 'Re-check all items (Events, Locations, Registrations, Students)?']); ?>
							<?php echo $this->Html->link('Reset All Pre-check', ['controller'=>'schedulings', 'action' => 'resetallprecheck',$convention_season_slug], ['class'=>'btn btn-warning', 'confirm' => 'Are you sure you want to reset all pre-checks?']); ?>
						</div>

						<div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center;">
							<span style="font-size:12px; color:#999; margin-right:4px;">Scheduling:</span>
							<?php echo $this->Html->link('Scheduling Wizard', ['controller'=>'schedulings', 'action' => 'wizard',$convention_season_slug], ['class'=>'btn btn-success', 'title'=>'Scheduling Wizard']); ?>
							<?php echo $this->Html->link('View/Start Scheduling', ['controller'=>'schedulings', 'action' => 'schedulecategory',$convention_season_slug], ['class'=>'btn btn-success', 'title'=>'View/Start Scheduling']); ?>
							<?php echo $this->Html->link('Scheduling Tweaks', ['controller'=>'schedulingtweaks', 'action' => 'index',$convention_season_slug], ['class'=>'btn btn-info', 'title'=>'Event Tweaks / Allocation Customization']); ?>
							<?php echo $this->Html->link('Overwrite Timings', ['controller'=>'schedulings', 'action' => 'overwritetimings',$convention_season_slug], ['class'=>'btn btn-warning', 'title'=>'Overwrite Timings']); ?>
							<?php echo $this->Html->link('Reports', ['controller'=>'schedulings', 'action' => 'reports',$convention_season_slug], ['class'=>'btn btn-info', 'title'=>'Generate Reports']); ?>
							<?php
							$totalConflict  = 0;
							if(!empty($schedulingD->conflict_user_ids))
							{
								$totalConflict = $totalConflict + count(explode(",",$schedulingD->conflict_user_ids));
							}
							if(!empty($schedulingD->conflict_user_ids_group))
							{
								$totalConflict = $totalConflict + count(explode(",",$schedulingD->conflict_user_ids_group));
							}
							if($totalConflict>0)
							{
								$linkConflict = "Resolve Conflicts (".$totalConflict." found)";
								echo $this->Html->link($linkConflict, ['controller'=>'schedulings', 'action' => 'resolveconflicts',$convention_season_slug], ['class'=>'btn btn-danger','title'=>'Resolve Conflicts']);
							}
							?>
						</div>

                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
			
			
			
			
          </div>
		  
		  
			
			
    </section>
  </div>