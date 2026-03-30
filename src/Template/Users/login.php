<script type="text/javascript">
	$(document).ready(function () {
		$("#login_frm").validate();

		// toggle login fields based on user type
		function toggleLoginFields() {
			var type = $('#user_type').val();
			if (type === 'Student') {
				$('#login-email-block').hide().find('input').removeClass('required email');
				$('#login-password-block').hide().find('input').removeClass('required');
				$('#login-student-code-block').show().find('input').addClass('required');
				$('#login-lastname-block').show().find('input').addClass('required');
			} else {
				$('#login-email-block').show().find('input').addClass('required email');
				$('#login-password-block').show().find('input').addClass('required');
				$('#login-student-code-block').hide().find('input').removeClass('required');
				$('#login-lastname-block').hide().find('input').removeClass('required');
			}
		}

		$('#user_type').on('change', toggleLoginFields);
		toggleLoginFields();
	});
</script>

<section class="position-relative">
	<div class=" left-element w-25">
		<?php echo $this->Html->image('front/left-element.png'); ?>
	</div>
	<div class=" ryt-element w-25">
		<?php echo $this->Html->image('front/ryt-element.png'); ?>
	</div>
	<div class="container">
		<div class="row center-section align-items-center">
			<?php echo $this->element('Homes/home_left_content'); ?>


			<div class="col-lg-6 ">
				<div class="ryt-box bg-white w-100">
					<div class="ryt-box-text form-group">
						<div class="ersu_message">
							<?php echo $this->Flash->render() ?>
						</div>
						<?php echo $this->Form->create($users ?? null, ['id' => 'login_frm', 'type' => 'file']); ?>
						<h2 class="mb-4">Login</h2>
						<div>
							<div class="lables">
								<span class="col-4">Choose Type</span>
								<div class="w-100">
									<?php echo $this->Form->select('Users.user_type', $loginUserTypes, ['id' => 'user_type', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
								</div>
							</div>

							<!-- Standard login fields (School / Supervisor / Judge) -->
							<div id="login-email-block" class="lables">
								<span class="col-4">Email Address</span>
								<?php echo $this->Form->input('Users.email_address', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control required email', 'placeholder' => 'Email Address']); ?>
							</div>

							<div id="login-password-block" class="lables">
								<span class="col-4">Password</span>
								<?php echo $this->Form->input('Users.password', ['label' => false, 'type' => 'password', 'div' => false, 'class' => "form-control required", 'placeholder' => 'Password']); ?>
							</div>

							<!-- Student login fields -->
							<div id="login-student-code-block" class="lables" style="display:none;">
								<span class="col-4">Student Code</span>
								<?php echo $this->Form->input('Users.student_code', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control', 'placeholder' => 'Student Code (e.g. STUAB12)']); ?>
							</div>

							<div id="login-lastname-block" class="lables" style="display:none;">
								<span class="col-4">Last Name (first 4 letters)</span>
								<?php echo $this->Form->input('Users.last_name_prefix', ['label' => false, 'type' => 'text', 'autocomplete' => 'off', 'div' => false, 'class' => 'form-control', 'placeholder' => 'First 4 letters of last name', 'maxlength' => 4]); ?>
							</div>

							<div class="btns col-8 float-end">
								<span class=" mb-3 w-100">
									<?php echo $this->Html->link('Forgot Password?', ['controller' => 'users', 'action' => 'forgotpassword'], ['escape' => false, 'class' => 'text-primary ms-1']); ?>
								</span>
								<button type="submit" class="btn btn-secondary px-3 ms-1">Submit</button>
							</div>
						</div>
						<?php echo $this->Form->end(); ?>
					</div>
				</div>
			</div>


		</div>
	</div>
</section>