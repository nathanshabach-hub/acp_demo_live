<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">My Schedule</h2>

			<?php if (!$convRegStudent): ?>
				<div class="alert alert-info">No convention registration found for the current season.</div>
			<?php elseif (empty($schedulingTimingsList) || count($schedulingTimingsList) == 0): ?>
				<div class="alert alert-info">No schedule has been published for you yet.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>Day</th>
								<th>Start Time</th>
								<th>Event</th>
								<th>Location</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($schedulingTimingsList as $timing): ?>
								<tr>
									<td><?php echo h($timing->day); ?></td>
									<td><?php echo $timing->start_time ? safe_date('h:i A', $timing->start_time) : '-'; ?></td>
									<td>
										<?php echo h($timing->Events['event_name'] ?? ''); ?>
										<?php if (!empty($timing->Events['event_id_number'])): ?>
											(<?php echo h($timing->Events['event_id_number']); ?>)
										<?php endif; ?>
										<?php if ($timing->is_bye == 1): ?>
											<span class="badge bg-secondary">BYE</span>
										<?php endif; ?>
									</td>
									<td><?php echo h($timing->Conventionrooms['room_name'] ?? '-'); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</main>
	</div>
</div>
