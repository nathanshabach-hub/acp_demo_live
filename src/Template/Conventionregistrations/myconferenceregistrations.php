<?php

$this->Html->css('/../js/css/print.css', ['inline' => false]);

?>

<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>

			<!-- Section 1: Conference Registrations -->
			<div class="teachers-top-heading">
				<span>Conference Registrations</span>
			</div>
			<div class="m_content">
			<?php if(!$conferenceregistrations->isEmpty()): ?>
				<div class="panel-body">
					<section id="no-more-tables" class="lstng-section">
						<div class="tbl-resp-listing">
							<table class="table table-bordered table-striped table-condensed cf">
								<thead class="cf ajshort">
									<tr>
										<th>Conference</th>
										<th>Season Year</th>
										<th>Registration Date</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($conferenceregistrations as $reg): ?>
										<tr>
											<td><?php echo h($reg->convention->name ?? $reg->Conventions->name); ?></td>
											<td><?php echo h($reg->season_year); ?></td>
											<td><?php echo date('M d, Y', strtotime($reg->created)); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</section>
				</div>
			<?php else: ?>
				<div class="admin_no_record">You have not yet registered for any conference in this season <?php echo $seasonD->season_year; ?>.</div>
			<?php endif; ?>
			</div>

			<hr>

			<!-- Section 2: New Conferences to Register -->
			<div class="teachers-top-heading">
				<span>New Conferences to Register</span>
			</div>
			<div class="m_content">
			<?php if(!$remainingconferences->isEmpty()): ?>
				<div class="panel-body">
					<section id="no-more-tables" class="lstng-section">
						<div class="tbl-resp-listing">
							<table class="table table-bordered table-striped table-condensed cf">
								<thead class="cf ajshort">
									<tr>
										<th>Conference</th>
										<th>Season Year</th>
										<th>Register Now</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($remainingconferences as $conference): ?>
										<tr>
											<td><?php echo h($conference->name); ?></td>
											<td><?php echo h($seasonD->season_year); ?></td>
											<td><?php echo $this->Html->link('Register', ['controller' => 'conventionregistrations', 'action' => 'registerconventionwizard', $conference->slug, $seasonD->id], ['escape' => false, 'class' => 'btn btn-primary']); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</section>
				</div>
			<?php else: ?>
				<div class="admin_no_record">None of the conferences remained to register for season <?php echo $seasonD->season_year; ?>.</div>
			<?php endif; ?>
			</div>

			<!-- Section 3: Past Conference Registrations -->
			<?php if(!$pastConferenceRegistrations->isEmpty()): ?>
			<hr>
			<div class="teachers-top-heading">
				<span>Past Conference Registrations</span>
			</div>
			<div class="m_content">
				<div class="panel-body">
					<section id="no-more-tables" class="lstng-section">
						<div class="tbl-resp-listing">
							<table class="table table-bordered table-striped table-condensed cf">
								<thead class="cf ajshort">
									<tr>
										<th>Conference</th>
										<th>Season Year</th>
										<th>View Details</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach($pastConferenceRegistrations as $pastReg): ?>
										<tr>
											<td><?php echo h($pastReg->convention->name ?? $pastReg->Conventions->name); ?></td>
											<td><?php echo h($pastReg->season_year); ?></td>
											<td><?php echo $this->Html->link('View Details', ['action' => 'pastregistrationdetails', $pastReg->slug], ['class' => 'btn btn-primary']); ?></td>
										</tr>
									<?php endforeach; ?>
								</tbody>
							</table>
						</div>
					</section>
				</div>
			</div>
			<?php endif; ?>

		</main>
	</div>
</div>
