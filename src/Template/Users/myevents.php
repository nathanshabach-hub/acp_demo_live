<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">My Events</h2>

			<?php if (empty($crstudentevents) || count($crstudentevents) == 0): ?>
				<div class="alert alert-info">No event registrations found for the current season.</div>
			<?php else: ?>
				<div class="table-responsive">
					<table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>#</th>
								<th>Event</th>
								<th>Event ID</th>
								<th>Convention</th>
								<th>Group Name</th>
							</tr>
						</thead>
						<tbody>
							<?php $i = 1; foreach ($crstudentevents as $crsev): ?>
								<tr>
									<td><?php echo $i++; ?></td>
									<td><?php echo h($crsev->Events['event_name'] ?? ''); ?></td>
									<td><?php echo h($crsev->Events['event_id_number'] ?? ''); ?></td>
									<td><?php echo h($crsev->Conventions['name'] ?? ''); ?></td>
									<td><?php echo !empty($crsev->group_name) ? h($crsev->group_name) : '-'; ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			<?php endif; ?>
		</main>
	</div>
</div>
