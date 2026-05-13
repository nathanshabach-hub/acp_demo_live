<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<div class="d-flex align-items-center justify-content-between mt-4 mb-3">
				<div>
					<h2 class="mb-0">Add Student Events</h2>
					<p class="text-muted mb-0" style="font-size:0.92rem;">Assign one or more events to one or more students in a single update.</p>
				</div>
				<?php echo $this->Html->link(
					'&larr; Back to Student Events',
					['controller' => 'conventionregistrations', 'action' => 'studentevents'],
					['escape' => false, 'class' => 'btn btn-secondary btn-sm']
				); ?>
			</div>

			<div class="row justify-content-start">
				<div class="col-lg-8 col-md-10">
					<div class="card shadow-sm border-0">
						<div class="card-header bg-white border-bottom py-3">
							<h5 class="mb-0 fw-semibold">Event Assignment Details</h5>
						</div>
						<div class="card-body p-4">
							<?php echo $this->Form->create($conventionregistrationstudents, ['id' => 'addstudentevent']); ?>

							<div class="mb-4">
								<label for="student_id" class="form-label fw-semibold">Students <span class="text-danger">*</span></label>
								<?php echo $this->Form->select(
									'Conventionregistrationstudents.student_id',
									$studentSchoolDD,
									['id' => 'student_id', 'label' => false, 'div' => false, 'class' => 'form-select required', 'autocomplete' => 'off', 'multiple' => true]
								); ?>
								<div class="form-text">Only students without assigned events are listed. You can select multiple students.</div>
							</div>

							<div class="mb-4">
								<label for="event_ids" class="form-label fw-semibold">Events <span class="text-danger">*</span></label>
								<?php echo $this->Form->select(
									'Conventionregistrationstudents.event_ids',
									$eventNameIDDD,
									['id' => 'event_ids', 'label' => false, 'div' => false, 'class' => 'form-select required', 'autocomplete' => 'off', 'multiple' => true]
								); ?>
								<div class="form-text">Each selected event will be checked against every selected student's age and gender rules.</div>
							</div>

							<div class="d-flex gap-2">
								<button type="submit" class="btn btn-primary px-4">Assign Selected Events</button>
								<?php echo $this->Html->link(
									'Cancel',
									['controller' => 'conventionregistrations', 'action' => 'studentevents'],
									['class' => 'btn btn-secondary px-4']
								); ?>
							</div>

							<?php echo $this->Form->end(); ?>
						</div>
					</div>

					<?php if (empty($studentSchoolDD)): ?>
					<div class="alert alert-info mt-3" role="alert">
						No eligible students found for event assignment. Students may already have event assignments.
					</div>
					<?php endif; ?>

					<?php if (empty($eventNameIDDD)): ?>
					<div class="alert alert-warning mt-3" role="alert">
						No events are available for this convention season.
					</div>
					<?php endif; ?>
				</div>
			</div>

		</main>
	</div>
</div>

<script>
$(document).ready(function () {
	$('#student_id').select2({
		placeholder: 'Select one or more students',
		allowClear: true,
		closeOnSelect: false,
		width: '100%'
	});

	$('#event_ids').select2({
		placeholder: 'Select one or more events',
		allowClear: true,
		closeOnSelect: false,
		width: '100%'
	});

	$('#addstudentevent').validate({
		rules: {
			'Conventionregistrationstudents[student_id][]': { required: true },
			'Conventionregistrationstudents[event_ids][]': { required: true }
		},
		messages: {
			'Conventionregistrationstudents[student_id][]': 'Please select at least one student.',
			'Conventionregistrationstudents[event_ids][]': 'Please select at least one event.'
		},
		errorClass: 'text-danger small',
		errorPlacement: function(error, element) {
			error.insertAfter(element.next('.select2-container'));
		}
	});
});
</script>