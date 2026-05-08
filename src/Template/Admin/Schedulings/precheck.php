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
			
			
			
			<div class="container">
				<h2>Scheduling Pre-check</h2>         
				<table class="table table-bordered">
					<tr>
						<th>#</th>
						<th>Pre-Check Status</th>
						<th>Data Found</th>
					</tr>
					
					<tr>
						<td>Events</td>
						<td>
							<?php if($schedulingD->precheck_events>0): ?>
								<i class="fa fa-check" style="color:#3c763d;"></i>
							<?php elseif($schedulingD->precheck_events === 0 && $schedulingD->total_events_found === null): ?>
								<i class="fa fa-times" style="color:#a94442;"></i>
							<?php endif; ?>
						</th>
						<td>
							<?php if($schedulingD->precheck_events>0): ?>
								Total event(s) found: <?php echo $schedulingD->total_events_found; ?>
							<?php elseif($schedulingD->precheck_events === 0 && $schedulingD->total_events_found === null): ?>
								<span class="label label-danger">No schedulable events found for this season</span>
							<?php endif; ?>
						</th>
					</tr>
					
					<tr>
						<td>Locations</td>
						<td>
							<?php if($schedulingD->precheck_locations>0): ?>
								<i class="fa fa-check" style="color:#3c763d;"></i>
							<?php elseif($schedulingD->precheck_locations === 0 && $schedulingD->total_locations_missing !== null): ?>
								<i class="fa fa-times" style="color:#a94442;"></i>
							<?php endif; ?>
						</td>
						<td>
							<?php if($schedulingD->precheck_locations>0): ?>
								Total location(s) found: <?php echo $schedulingD->total_locations_found; ?>
							<?php elseif($schedulingD->precheck_locations === 0 && $schedulingD->total_locations_missing !== null): ?>
								<?php if($schedulingD->total_locations_missing > 0): ?>
									<span class="label label-warning"><?php echo $schedulingD->total_locations_missing; ?> event(s) not assigned to any room</span>
								<?php else: ?>
									<span class="label label-danger">No rooms configured for this convention</span>
								<?php endif; ?>
							<?php endif; ?>
						</td>
					</tr>
					
					<tr>
						<td>Registrations</td>
						<td>
							<?php if($schedulingD->precheck_registrations>0): ?>
								<i class="fa fa-check" style="color:#3c763d;"></i>
							<?php elseif($schedulingD->precheck_registrations === 0 && $schedulingD->total_registrations_found === null): ?>
								<i class="fa fa-times" style="color:#a94442;"></i>
							<?php endif; ?>
						</td>
						<td>
							<?php if($schedulingD->precheck_registrations>0): ?>
								Total registration(s) found: <?php echo $schedulingD->total_registrations_found; ?>
							<?php elseif($schedulingD->precheck_registrations === 0 && $schedulingD->total_registrations_found === null): ?>
								<span class="label label-danger">No registrations found for this season</span>
							<?php endif; ?>
						</td>
					</tr>
					
					
					<tr>
						<td>Students</td>
						<td>
							<?php if($schedulingD->precheck_students>0): ?>
								<i class="fa fa-check" style="color:#3c763d;"></i>
							<?php elseif($schedulingD->precheck_students === 0 && $schedulingD->total_students_found === null): ?>
								<i class="fa fa-times" style="color:#a94442;"></i>
							<?php endif; ?>
						</td>
						<td>
							<?php if($schedulingD->precheck_students>0): ?>
								Total student(s) found: <?php echo $schedulingD->total_students_found; ?>
							<?php elseif($schedulingD->precheck_students === 0 && $schedulingD->total_students_found === null): ?>
								<span class="label label-danger">No students found for this convention</span>
							<?php endif; ?>
						</td>
					</tr>
					
				</table>
			</div>
			
			
			
			<?php echo $this->Form->create(null, ['id'=>'adminForm', 'type' => 'file']); ?>
                <div class="form-horizontal">
					<div class="box-body">
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
						
						<?php
						echo $this->Html->link('<< Back To Seasons', ['controller'=>'conventions', 'action' => 'seasons',$convention_slug], ['class'=>'btn btn-default canlcel_le']);
						
						echo $this->Html->link('Re-Check All', ['controller'=>'schedulings', 'action' => 'precheckall',$convention_season_slug], ['class'=>'btn btn-primary canlcel_le', 'confirm' => 'Re-check all items (Events, Locations, Registrations, Students)?']);
						
						echo $this->Html->link('Reset All Pre-check', ['controller'=>'schedulings', 'action' => 'resetallprecheck',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le', 'confirm' => 'Are you sure you want to reset all pre-checks ?']);
						
						echo $this->Html->link('Scheduling Wizard', ['controller'=>'schedulings', 'action' => 'wizard',$convention_season_slug], ['class'=>'btn btn-success canlcel_le','title'=>'Scheduling Wizard']);
						
						echo $this->Html->link('View/Start Scheduling', ['controller'=>'schedulings', 'action' => 'schedulecategory',$convention_season_slug], ['class'=>'btn btn-success canlcel_le','title'=>'View/Start Scheduling']);
						
						echo $this->Html->link('Scheduling Tweaks', ['controller'=>'schedulingtweaks', 'action' => 'index',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Event Tweaks / Allocation Customization']);
						
						echo $this->Html->link('Overwrite Timings', ['controller'=>'schedulings', 'action' => 'overwritetimings',$convention_season_slug], ['class'=>'btn btn-warning canlcel_le','title'=>'Overwrite Timings']);
						
						echo $this->Html->link('Reports', ['controller'=>'schedulings', 'action' => 'reports',$convention_season_slug], ['class'=>'btn btn-info canlcel_le','title'=>'Generate Reports']);
						
						// to check if there is any conflict
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
							echo $this->Html->link($linkConflict, ['controller'=>'schedulings', 'action' => 'resolveconflicts',$convention_season_slug], ['class'=>'btn btn-danger canlcel_le','title'=>'Resolve Conflicts']);
						}
						
						
						?>
						
						
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
			
			
			
			
          </div>
		  
		  
			
			
    </section>
  </div>