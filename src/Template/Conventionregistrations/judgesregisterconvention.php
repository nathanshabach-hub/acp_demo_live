<style>
.jrc-table th { background: #f4f6f9; font-size: 13px; }
.jrc-table td { font-size: 13px; vertical-align: middle; }
.jrc-assigned { font-size: 12px; color: #555; }
.jrc-slot-empty { color: #ccc; font-style: italic; font-size: 12px; }
.jrc-badge-you { display:inline-block; background:#d5f5e3; color:#1e8449; font-size:11px; padding:1px 7px; border-radius:10px; font-weight:700; margin-left:4px; }
.jrc-table tr:hover td { background: #f9fbff; }
.jrc-table input[type=checkbox] { width:18px; height:18px; cursor:pointer; }
</style>
<script type="text/javascript">
	$(document).ready(function () {
		$("#judgeregister").validate();
		// Make the whole row clickable
		$('#jrc-event-tbody tr').on('click', function(e) {
			if($(e.target).is('input')) return;
			var cb = $(this).find('input[type=checkbox]');
			cb.prop('checked', !cb.prop('checked'));
		});
	});
</script>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="mt-3">Register For Convention :: <?php echo $conventionD->name; ?></h2>

			<div class="dashboard-form">
				<h2 class="form-title">Season: <?php echo $convSeasonD->season_year; ?></h2>
				<?php echo $this->Form->create($conventionregistrations, ['id' => 'judgeregister', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<p class="text-muted" style="font-size:13px; margin-bottom:10px;">
						Tick the events you are available to judge. Not all events will be allocated to you &mdash; events are assigned on a needs basis, taking workload into consideration.
					</p>
					<?php if (empty($eventsList)) { ?>
						<div class="alert alert-warning">No events found for this season.</div>
					<?php } else { ?>
					<div class="table-responsive">
					<table class="table table-bordered table-hover jrc-table">
						<thead>
							<tr>
								<th style="width:40px; text-align:center;">
									<input type="checkbox" id="jrc-select-all" title="Select / deselect all" style="width:16px;height:16px;cursor:pointer;">
								</th>
								<th style="width:80px;">Code</th>
								<th>Event Name</th>
								<th style="width:155px;">Judge 1</th>
								<th style="width:155px;">Judge 2</th>
								<th style="width:155px;">Judge 3</th>
							</tr>
						</thead>
						<tbody id="jrc-event-tbody">
						<?php
						$loggedUserId = (int)$this->request->getSession()->read('user_id');
						foreach($eventsList as $ev):
							$eid = (int)$ev->id;
							$checked = isset($alreadySelectedIds[$eid]) ? ' checked' : '';
							$panel = isset($seasonAssignments[$eid]) ? $seasonAssignments[$eid] : ['judge1'=>null,'judge2'=>null,'judge3'=>null];
							$slots = [];
							foreach(['judge1','judge2','judge3'] as $slot) {
								$uid = (int)$panel[$slot];
								if($uid > 0) {
									$name = isset($judgeNamesById[$uid]) ? h($judgeNamesById[$uid]) : 'Judge #'.$uid;
									$badge = ($uid === $loggedUserId) ? ' <span class="jrc-badge-you">you</span>' : '';
									$slots[] = '<span class="jrc-assigned">'.$name.$badge.'</span>';
								} else {
									$slots[] = '<span class="jrc-slot-empty">&mdash;</span>';
								}
							}
						?>
							<tr style="cursor:pointer;">
								<td style="text-align:center;">
									<input type="checkbox" name="Conventionregistrations[judges_event_ids][]" value="<?php echo $eid; ?>"<?php echo $checked; ?>>
								</td>
								<td><?php echo h($ev->event_id_number); ?></td>
								<td><?php echo h($ev->event_name); ?></td>
								<td><?php echo $slots[0]; ?></td>
								<td><?php echo $slots[1]; ?></td>
								<td><?php echo $slots[2]; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					</div>
					<?php } ?>
				</div>

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary">Save</button>
					<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'myregistrations'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>

<script>
$(document).ready(function() {
	$('#jrc-select-all').on('change', function() {
		$('#jrc-event-tbody input[type=checkbox]').prop('checked', this.checked);
	});
	$('#jrc-event-tbody tr').on('click', function(e) {
		if($(e.target).is('input')) return;
		var cb = $(this).find('input[type=checkbox]');
		cb.prop('checked', !cb.prop('checked'));
	});
});
</script>
