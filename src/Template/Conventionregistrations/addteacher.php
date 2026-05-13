<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<div class="d-flex align-items-center justify-content-between mt-4 mb-3">
				<div>
					<h2 class="mb-0">Add Supervisor</h2>
					<p class="text-muted mb-0" style="font-size:0.92rem;">Select one or more supervisors from your school to add to this convention registration.</p>
				</div>
				<?php echo $this->Html->link(
					'&larr; Back to Supervisors',
					['controller' => 'conventionregistrations', 'action' => 'teachers'],
					['escape' => false, 'class' => 'btn btn-secondary btn-sm']
				); ?>
			</div>

			<div class="row justify-content-start">
				<div class="col-lg-6 col-md-8">
					<div class="card shadow-sm border-0">
						<div class="card-header bg-white border-bottom py-3">
							<h5 class="mb-0 fw-semibold">Supervisor Details</h5>
						</div>
						<div class="card-body p-4">

							<?php echo $this->Form->create($conventionregistrationteachers, ['id' => 'addteacher']); ?>

							<div class="mb-4">
								<label for="teacher_id" class="form-label fw-semibold">Supervisors <span class="text-danger">*</span></label>
								<?php echo $this->Form->select(
									'Conventionregistrationteachers.teacher_id',
									$teacherSchoolDD,
									[
										'id'           => 'teacher_id',
										'label'        => false,
										'div'          => false,
										'class'        => 'form-select required',
										'multiple'     => true,
										'autocomplete' => 'off',
									]
								); ?>
								<div class="form-text">Only supervisors not already registered for this convention are listed. You can select multiple supervisors.</div>
							</div>

							<div class="d-flex gap-2">
								<button type="submit" class="btn btn-primary px-4">Add Selected Supervisors</button>
								<?php echo $this->Html->link(
									'Cancel',
									['controller' => 'conventionregistrations', 'action' => 'teachers'],
									['class' => 'btn btn-secondary px-4']
								); ?>
							</div>

							<?php echo $this->Form->end(); ?>

						</div>
					</div>

					<?php if (empty($teacherSchoolDD)): ?>
					<div class="alert alert-info mt-3" role="alert">
						All supervisors from your school have already been added to this convention registration, or no supervisors exist yet.
					</div>
					<?php endif; ?>

				</div>
			</div>

		</main>
	</div>
</div>

<script>
$(document).ready(function () {
	$('#teacher_id').select2({
		placeholder: 'Select one or more supervisors',
		allowClear: true,
		closeOnSelect: false,
		width: '100%'
	});
	$('#addteacher').validate({
		rules: { 'Conventionregistrationteachers[teacher_id][]': { required: true } },
		messages: { 'Conventionregistrationteachers[teacher_id][]': 'Please select at least one supervisor.' },
		errorClass: 'text-danger small',
		errorPlacement: function(error, element) { error.insertAfter(element.next('.select2-container')); }
	});
});
</script>