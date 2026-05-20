<script type="text/javascript">
$(document).ready(function () {
	$("#editstudent").validate();
});
</script>
<style>
	.edit-student-header a,
	.edit-student-actions a {
		color: #343a3d !important;
	}

	.edit-student-actions a:hover {
		color: #fff !important;
	}
</style>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message"><?php echo $this->Flash->render() ?></div>

			<div class="d-flex align-items-center justify-content-between mt-4 mb-3 edit-student-header">
				<h2 class="mb-0">Edit Student</h2>
				<?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to Students', ['controller' => 'users', 'action' => 'students'], ['escape' => false, 'class' => 'btn btn-sm btn-outline-secondary']); ?>
			</div>

			<div class="card shadow-sm border-0 mb-5" style="border-radius: 12px; overflow: hidden;">
				<div class="card-header py-3" style="background: linear-gradient(135deg, #1c2452, #78A4C8); border: none;">
					<h5 class="mb-0" style="color: #fff; font-size: 16px;">
						<i class="fa fa-user-edit me-2"></i> Student Information
					</h5>
				</div>
				<div class="card-body p-4">
					<?php echo $this->Form->create($users, ['id' => 'editstudent', 'type' => 'file']); ?>

					<div class="row g-3 mb-3">
						<div class="col-md-4">
							<label class="form-label fw-semibold">First Name <span style="color:#d00;">*</span></label>
							<?php echo $this->Form->input('Users.first_name', ['label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'First Name']); ?>
						</div>
						<div class="col-md-4">
							<label class="form-label fw-semibold">Middle Name</label>
							<?php echo $this->Form->input('Users.middle_name', ['label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control', 'placeholder' => 'Middle Name (optional)']); ?>
						</div>
						<div class="col-md-4">
							<label class="form-label fw-semibold">Last Name <span style="color:#d00;">*</span></label>
							<?php echo $this->Form->input('Users.last_name', ['label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control required', 'placeholder' => 'Last Name']); ?>
						</div>
					</div>

					<div class="row g-3 mb-4">
						<div class="col-md-6">
							<label class="form-label fw-semibold">Birth Year <span style="color:#d00;">*</span></label>
							<?php echo $this->Form->select('Users.birth_year', $birthYearDD, ['id' => 'birth_year', 'label' => false, 'div' => false, 'class' => 'form-select required', 'autocomplete' => 'off', 'empty' => '— Select Year —']); ?>
						</div>
						<div class="col-md-6">
							<label class="form-label fw-semibold">Gender <span style="color:#d00;">*</span></label>
							<?php echo $this->Form->select('Users.gender', $genderDD, ['id' => 'gender', 'label' => false, 'div' => false, 'class' => 'form-select required', 'autocomplete' => 'off', 'empty' => '— Select Gender —']); ?>
						</div>
					</div>

					<hr class="my-3">

					<div class="d-flex gap-2 edit-student-actions">
						<button type="submit" class="btn btn-primary px-4">
							<i class="fa fa-save me-1"></i> Save Student
						</button>
						<?php echo $this->Html->link('<i class="fa fa-times me-1"></i> Cancel', ['controller' => 'users', 'action' => 'students'], ['escape' => false, 'class' => 'btn btn-outline-secondary px-4']); ?>
					</div>

					<?php echo $this->Form->end(); ?>
				</div>
			</div>
			
		</main>
	</div>
</div>