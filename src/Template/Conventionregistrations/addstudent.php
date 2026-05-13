<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<div class="d-flex align-items-center justify-content-between mt-4 mb-3">
				<div>
					<h2 class="mb-0">Add Students</h2>
					<p class="text-muted mb-0" style="font-size:0.92rem;">Select one or more students and assign a supervisor in one step.</p>
				</div>
				<?php echo $this->Html->link(
					'&larr; Back to Students',
					['controller' => 'conventionregistrations', 'action' => 'students'],
					['escape' => false, 'class' => 'btn btn-secondary btn-sm']
				); ?>
			</div>

			<div class="row justify-content-start">
				<div class="col-lg-7 col-md-10">
					<div class="card shadow-sm border-0">
						<div class="card-header bg-white border-bottom py-3">
							<h5 class="mb-0 fw-semibold">Student Assignment Details</h5>
						</div>
						<div class="card-body p-4">

							<?php echo $this->Form->create($conventionregistrationstudents, ['id' => 'addstudent']); ?>

							<div class="mb-4">
								<label for="student_id" class="form-label fw-semibold">Students <span class="text-danger">*</span></label>
								<?php echo $this->Form->select(
									'Conventionregistrationstudents.student_id',
									$studentSchoolDD,
									[
										'id' => 'student_id',
										'label' => false,
										'div' => false,
										'class' => 'form-select required',
										'multiple' => true,
										'autocomplete' => 'off',
									]
								); ?>
								<div class="form-text">Only students not already registered are shown. You can select multiple students.</div>
							</div>

							<div class="mb-4">
								<label for="teacher_parent_id" class="form-label fw-semibold">Supervisor <span class="text-danger">*</span></label>
								<?php echo $this->Form->select(
									'Conventionregistrationstudents.teacher_parent_id',
									$teacherDropDownData,
									[
										'id' => 'teacher_parent_id',
										'label' => false,
										'div' => false,
										'class' => 'form-select required',
										'autocomplete' => 'off',
										'empty' => 'Select a supervisor',
									]
								); ?>
								<div class="form-text">Each selected student will be assigned to this supervisor.</div>
							</div>

							<div class="d-flex gap-2">
								<button type="submit" class="btn btn-primary px-4">Add Selected Students</button>
								<?php echo $this->Html->link(
									'Cancel',
									['controller' => 'conventionregistrations', 'action' => 'students'],
									['class' => 'btn btn-secondary px-4']
								); ?>
							</div>

							<?php echo $this->Form->end(); ?>
						</div>
					</div>

					<?php if (empty($studentSchoolDD)): ?>
					<div class="alert alert-info mt-3" role="alert">
						All students from your school are already added to this convention registration, or no students exist yet.
					</div>
					<?php endif; ?>

					<?php if (empty($teacherDropDownData)): ?>
					<div class="alert alert-warning mt-3" role="alert">
						No supervisors are available yet. Please add a supervisor first before adding students.
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

	$('#teacher_parent_id').select2({
		placeholder: 'Select a supervisor',
		allowClear: true,
		width: '100%'
	});

	$('#addstudent').validate({
		rules: {
			'Conventionregistrationstudents[student_id][]': { required: true },
			'Conventionregistrationstudents[teacher_parent_id]': { required: true }
		},
		messages: {
			'Conventionregistrationstudents[student_id][]': 'Please select at least one student.',
			'Conventionregistrationstudents[teacher_parent_id]': 'Please select a supervisor.'
		},
		errorClass: 'text-danger small',
		errorPlacement: function(error, element) {
			error.insertAfter(element.next('.select2-container'));
		}
	});
});
</script>