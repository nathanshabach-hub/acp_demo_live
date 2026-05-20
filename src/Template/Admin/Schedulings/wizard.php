<script type="text/javascript">
    $(document).ready(function() {
        $("#schedulingWizardForm").validate();
    });
</script>
<script>
	$(document).ready(function(){
		$('.mdtpicker').mdtimepicker(); //Initializes the time picker
	});
</script>

<?php echo $this->Html->script('jquery/ui/jquery.ui.core.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.widget.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.position.js'); ?>
<?php echo $this->Html->script('jquery/ui/jquery.ui.datepicker.js'); ?>
<?php echo $this->Html->css('themes/ui-lightness/jquery.ui.all.css'); ?>
<script>
    $(function() {
        $( "#start_date" ).datepicker({
            dateFormat: 'yy-mm-dd',
            changeMonth : true,
            changeYear : true,
			minDate: '0d',
            maxDate: '+2y'
        });
    });
</script>

<script>
	function getConventionDays(firstDay, numberOfDays) {
		var weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
		var startIdx = weekDays.indexOf(firstDay);
		if (startIdx === -1 || numberOfDays < 1) {
			return [];
		}

		var days = [];
		for (var i = 0; i < numberOfDays; i++) {
			days.push(weekDays[(startIdx + i) % weekDays.length]);
		}

		return days;
	}

	function getWeekDayFromDateString(dateStr) {
		if (!dateStr) {
			return null;
		}

		var parts = dateStr.split('-');
		if (parts.length !== 3) {
			return null;
		}

		var dateObj = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
		if (isNaN(dateObj.getTime())) {
			return null;
		}

		var weekDays = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
		return weekDays[dateObj.getDay()];
	}

	function refreshSportsDayOptions(allowedDays) {
		var $sportsDay = $('#sports_day');
		if (!$sportsDay.length) {
			return;
		}

		$sportsDay.find('option').each(function() {
			var value = $(this).val();
			if (!value) {
				return;
			}

			if (allowedDays.indexOf(value) === -1) {
				$(this).prop('disabled', true);
			} else {
				$(this).prop('disabled', false);
			}
		});

		if ($sportsDay.val() && allowedDays.indexOf($sportsDay.val()) === -1) {
			$sportsDay.val('');
		}
	}

	function refreshConventionWindowPreview() {
		var startDate = $('#start_date').val();
		var firstDay = $('#first_day').val();
		var numberOfDays = parseInt($('#schedulings-number-of-days').val(), 10);
		var $preview = $('#convention-day-window-preview');
		var $warning = $('#convention-day-window-warning');

		if (!numberOfDays || numberOfDays < 1 || !firstDay) {
			$preview.text('Pick First Day and Number of Days to see the allowed schedule window.');
			$warning.text('');
			refreshSportsDayOptions([]);
			return;
		}

		var allowedDays = getConventionDays(firstDay, numberOfDays);
		$preview.text('Allowed schedule days: ' + allowedDays.join(', '));
		refreshSportsDayOptions(allowedDays);

		var dateWeekDay = getWeekDayFromDateString(startDate);
		if (startDate && dateWeekDay && dateWeekDay !== firstDay) {
			$warning.text('Start Date is ' + dateWeekDay + ' but First Day is ' + firstDay + '. Save will be blocked until these match.');
		} else {
			$warning.text('');
		}
	}

	$(document).ready(function() {
		$('#start_date, #first_day, #schedulings-number-of-days').on('change keyup', refreshConventionWindowPreview);
		refreshConventionWindowPreview();
	});
</script>

<?php
if(!empty($schedulings->start_date) && isset($schedulings->start_date))
{
	$schedulings->start_date = safe_date("Y-m-d", strtotime($schedulings->start_date));
}

if(!empty($schedulings->normal_starting_time) && isset($schedulings->normal_starting_time))
{
	$schedulings->normal_starting_time= safe_date("h:i A", strtotime($schedulings->normal_starting_time));
}
if(!empty($schedulings->normal_finish_time) && isset($schedulings->normal_finish_time))
{
	$schedulings->normal_finish_time= safe_date("h:i A", strtotime($schedulings->normal_finish_time));
}
if(!empty($schedulings->lunch_time_start) && isset($schedulings->lunch_time_start))
{
	$schedulings->lunch_time_start= safe_date("h:i A", strtotime($schedulings->lunch_time_start));
}
if(!empty($schedulings->lunch_time_end) && isset($schedulings->lunch_time_end))
{
	$schedulings->lunch_time_end = safe_date("h:i A", strtotime($schedulings->lunch_time_end));
}

// to check if start on different time on first day
$box_starting_different_time_first_day_yes_no = "none";
if($schedulings->starting_different_time_first_day_yes_no)
{
	$box_starting_different_time_first_day_yes_no = "block";
	
	if(!empty($schedulings->different_first_day_start_time) && isset($schedulings->different_first_day_start_time))
	{
		$schedulings->different_first_day_start_time = safe_date("h:i A", strtotime($schedulings->different_first_day_start_time));
	}
	if(!empty($schedulings->different_first_day_end_time) && isset($schedulings->different_first_day_end_time))
	{
		$schedulings->different_first_day_end_time = safe_date("h:i A", strtotime($schedulings->different_first_day_end_time));
	}
}

$box_judging_breaks_yes_no = "none";
if($schedulings->judging_breaks_yes_no)
{
	$box_judging_breaks_yes_no = "block";
	
	if(!empty($schedulings->judging_breaks_morning_break_starting_time) && isset($schedulings->judging_breaks_morning_break_starting_time))
	{
		$schedulings->judging_breaks_morning_break_starting_time= safe_date("h:i A", strtotime($schedulings->judging_breaks_morning_break_starting_time));
	}
	if(!empty($schedulings->judging_breaks_morning_break_finish_time) && isset($schedulings->judging_breaks_morning_break_finish_time))
	{
		$schedulings->judging_breaks_morning_break_finish_time= safe_date("h:i A", strtotime($schedulings->judging_breaks_morning_break_finish_time));
	}
	if(!empty($schedulings->judging_breaks_afternoon_break_start_time) && isset($schedulings->judging_breaks_afternoon_break_start_time))
	{
		$schedulings->judging_breaks_afternoon_break_start_time= safe_date("h:i A", strtotime($schedulings->judging_breaks_afternoon_break_start_time));
	}
	if(!empty($schedulings->judging_breaks_afternoon_break_finish_time) && isset($schedulings->judging_breaks_afternoon_break_finish_time))
	{
		$schedulings->judging_breaks_afternoon_break_finish_time= safe_date("h:i A", strtotime($schedulings->judging_breaks_afternoon_break_finish_time));
	}
}


$box_sports_day_yes_no = "none";
if($schedulings->sports_day_yes_no)
{
	$box_sports_day_yes_no = "block";
	
	if(!empty($schedulings->sports_day_starting_time) && isset($schedulings->sports_day_starting_time))
	{
		$schedulings->sports_day_starting_time= safe_date("h:i A", strtotime($schedulings->sports_day_starting_time));
	}
	if(!empty($schedulings->sports_day_finish_time) && isset($schedulings->sports_day_finish_time))
	{
		$schedulings->sports_day_finish_time= safe_date("h:i A", strtotime($schedulings->sports_day_finish_time));
	}
}

$box_sports_day_having_events_after_sport_yes_no = "none";

if($schedulings->sports_day_having_events_after_sport_yes_no)
{
	$box_sports_day_having_events_after_sport_yes_no = "block";
	
	if(!empty($schedulings->sports_day_other_starting_time) && isset($schedulings->sports_day_other_starting_time))
	{
		$schedulings->sports_day_other_starting_time= safe_date("h:i A", strtotime($schedulings->sports_day_other_starting_time));
	}
	if(!empty($schedulings->sports_day_other_finish_time) && isset($schedulings->sports_day_other_finish_time))
	{
		$schedulings->sports_day_other_finish_time= safe_date("h:i A", strtotime($schedulings->sports_day_other_finish_time));
	}
}
?>

<style>
	.wizard-shell {
		padding: 8px 6px 4px;
	}
	.wizard-intro {
		border: 1px solid #d9edf7;
		background: #f4fbff;
		border-radius: 6px;
		padding: 12px 14px;
		margin-bottom: 20px;
		color: #1f4f66;
		font-size: 13px;
	}
	.wizard-section-card {
		border: 1px solid #e2e5e8;
		border-left: 5px solid #27ae60;
		border-radius: 6px;
		padding: 16px;
		margin-bottom: 18px;
		background: #fff;
		box-shadow: 0 1px 3px rgba(0,0,0,.06);
	}
	.wizard-section-heading {
		margin: 0 0 14px;
		font-size: 18px;
		font-weight: 700;
		color: #2c3e50;
		display: flex;
		align-items: center;
		gap: 10px;
	}
	.wizard-section-heading i {
		color: #27ae60;
		font-size: 20px;
	}
	.wizard-shell .form-group {
		margin-left: 0;
		margin-right: 0;
		padding-bottom: 4px;
	}
	.wizard-shell .control-label {
		width: 230px;
		text-align: left;
		font-weight: 600;
		color: #394b59;
	}
	.wizard-shell .col-sm-10 {
		width: calc(100% - 230px);
	}
	.wizard-section-hint {
		font-size: 13px;
		line-height: 1.5;
		color: #6c757d;
		margin-bottom: 12px;
	}
	.wizard-preview {
		background: #f7fafc;
		border: 1px dashed #ccd6dd;
		border-radius: 4px;
		padding: 10px 12px;
	}
	.wizard-actions {
		display: flex;
		gap: 8px;
		align-items: center;
	}
	.wizard-section-card {
		border-left: 5px solid #999;
		background: #fff;
		border-radius: 3px;
		padding: 16px 16px 12px 16px;
		margin-bottom: 16px;
		position: relative;
	}
	.wizard-section-card h3 {
		margin: -8px 0 12px 0;
		padding-left: 28px;
		font-size: 15px;
		font-weight: 600;
		color: #2c3e50;
	}
	.wizard-section-card h3 i {
		position: absolute;
		left: 16px;
		font-size: 16px;
		color: #666;
	}
	.wizard-section-card.convention-days {
		border-left-color: #3498db;
	}
	.wizard-section-card.convention-days h3 i {
		color: #3498db;
	}
	.wizard-section-card.times {
		border-left-color: #9b59b6;
	}
	.wizard-section-card.times h3 i {
		color: #9b59b6;
	}
	.wizard-section-card.judging-breaks {
		border-left-color: #e67e22;
	}
	.wizard-section-card.judging-breaks h3 i {
		color: #e67e22;
	}
	.wizard-section-card.sports-day {
		border-left-color: #e74c3c;
	}
	.wizard-section-card.sports-day h3 i {
		color: #e74c3c;
	}
	@media (max-width: 991px) {
		.wizard-shell .control-label,
		.wizard-shell .col-sm-10 {
			width: 100%;
		}
		.wizard-shell .control-label {
			margin-bottom: 4px;
		}
		.wizard-section-heading {
			font-size: 16px;
		}
	}
</style>

<div class="content-wrapper">
    <section class="content-header">
			<h1>
				Scheduling Wizard
		  <small><?php echo h($conventionSD->Conventions['name']); ?> &mdash; <?php echo h($conventionSD->season_year); ?></small>
			</h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Wizard </li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
			<div class="box-header with-border">
				<h3 class="box-title"><i class="fa fa-sliders"></i>&nbsp; Wizard Settings</h3>
			</div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create($schedulings, ['id'=>'schedulingWizardForm', 'type' => 'file', 'autocomplete' => 'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
					<div class="wizard-shell">
						<div class="wizard-intro">
							Configure convention days, daily times, judging breaks, and sports day timing in one place.
						</div>
					
					<div class="wizard-section-card convention-days">
						<h3 class="wizard-section-heading"><i class="fa fa-calendar"></i> Convention Days</h3>
					
						<!-- Convention Days Starts -->
					<div class="form-group">
                      <label class="col-sm-2 control-label"><h3>Convention Days </h3><span class="require"></span></label>
                      <div class="col-sm-10">
                          &nbsp;
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Start Date <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.start_date', ['id'=>'start_date', 'label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required', 'placeholder'=>'Start Date']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">First Day <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->select('Schedulings.first_day', $weekDays, ['id' => 'first_day', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
                      </div>
                    </div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">Number of Days <span class="require">*</span></label>
                      <div class="col-sm-10">
						  <?php echo $this->Form->input('Schedulings.number_of_days', ['id'=>'schedulings-number-of-days', 'label'=>false, 'type'=>'number',  'div'=>false, 'class'=>'form-control required number', 'placeholder'=>'Number of Days']); ?>
                      </div>
                    </div>

					<div class="form-group">
					  <label class="col-sm-2 control-label">Schedule Window</label>
					  <div class="col-sm-10" style="padding-top:7px;">
						  <div class="wizard-preview">
							  <div id="convention-day-window-preview" style="font-weight:600;">Pick First Day and Number of Days to see the allowed schedule window.</div>
							  <div id="convention-day-window-warning" style="color:#b94a48; margin-top:4px;"></div>
						  </div>
					  </div>
					</div>
					<!-- Convention Days Ends -->
				</div>
				
				<div class="wizard-section-card times">
					<h3 class="wizard-section-heading"><i class="fa fa-clock-o"></i> Times</h3>
					
					<!-- Times Starts -->
                      <div class="col-sm-10">
                          &nbsp;
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Normal Starting Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.normal_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Normal Starting Time']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Normal Finish Time <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.normal_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Normal Finish Time']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Lunch Time Start <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.lunch_time_start', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Lunch Time Start']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">Lunch Time End <span class="require">*</span></label>
                      <div class="col-sm-10">
                          <?php echo $this->Form->input('Schedulings.lunch_time_end', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Lunch Time End']); ?>
                      </div>
                    </div>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
							<?php echo $this->Form->checkbox('Schedulings.starting_different_time_first_day_yes_no', ['value'=>'1','id'=>'starting_different_time_first_day_yes_no']); ?>
							We are starting at a different time on the first day
                      </div>
                    </div>
					
					<div id="box_starting_different_time_first_day_yes_no" style="display:<?php echo $box_starting_different_time_first_day_yes_no; ?>;">
						<div class="form-group">
						  <label class="col-sm-2 control-label">First Day Start Time <span class="require">*</span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.different_first_day_start_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'First Day Start Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">First Day End Time <span class="require">*</span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.different_first_day_end_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'First Day End Time']); ?>
						  </div>
						</div>
                    </div>
					<!-- Times Ends -->
				</div>
				
				<div class="wizard-section-card judging-breaks">
					<h3 class="wizard-section-heading"><i class="fa fa-coffee"></i> Judging Breaks</h3>
					<p class="wizard-section-hint">
Check the box if you want to schedule breaks for music and platform judges. We recommend trying to generate the schedule with breaks first and removing them only if necessary.</p>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.judging_breaks_yes_no', ['value'=>'1','id'=>'judging_breaks_yes_no']); ?> 						
						Yes we are having judging breaks
                      </div>
                    </div>
					
					<div id="box_judging_breaks_yes_no" style="display:<?php echo $box_judging_breaks_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Morning Break Starting Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_morning_break_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Morning Break Starting Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Morning Break Finish Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_morning_break_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Morning Break Finish Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Afternoon Break Start Time<span class="require">*</span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.judging_breaks_afternoon_break_start_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Afternoon Break Start Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Afternoon Break Finish Time<span class="require">*</span></label>
						  <div class="col-sm-10">
							<?php echo $this->Form->input('Schedulings.judging_breaks_afternoon_break_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Afternoon Break Finish Time']); ?>
						  </div>
						</div>
					
					</div>
					<!-- Judging Breaks Ends -->
				</div>
				
				<div class="wizard-section-card sports-day">
					<h3 class="wizard-section-heading"><i class="fa fa-futbol-o"></i> Sports Day</h3>
					<p class="wizard-section-hint">
Check the box if you are having sports day. If sports is only part of the day, enable extra events after sports and set those times too.</p>
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.sports_day_yes_no', ['value'=>'1','id'=>'sports_day_yes_no']); ?> 						
						Yes we are having a Sports Day
                      </div>
                    </div>
					
					<div id="box_sports_day_yes_no" style="display:<?php echo $box_sports_day_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Sports Day <span class="require"></span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->select('Schedulings.sports_day', $weekDays, ['id' => 'sports_day', 'label' => false, 'div' => false, 'class' => 'form-control required', 'autocomplete' => 'off', 'empty' => 'Choose']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Starting Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Starting Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Finish Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Finish Time']); ?>
						  </div>
						</div>
					
                    </div>
					
					
                    <div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10">
						<?php echo $this->Form->checkbox('Schedulings.sports_day_having_events_after_sport_yes_no', ['value'=>'1','id'=>'sports_day_having_events_after_sport_yes_no']); ?> 
						We are having more events after sport
                      </div>
                    </div>
					
					<div id="box_sports_day_having_events_after_sport_yes_no" style="display:<?php echo $box_sports_day_having_events_after_sport_yes_no; ?>;">
					
						<div class="form-group">
						  <label class="col-sm-2 control-label">Starting Time <span class="require"></span></label>
						  <div class="col-sm-10">
								<?php echo $this->Form->input('Schedulings.sports_day_other_starting_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Starting Time']); ?>
						  </div>
						</div>
						<div class="form-group">
						  <label class="col-sm-2 control-label">Finish Time <span class="require"></span></label>
						  <div class="col-sm-10">
							  <?php echo $this->Form->input('Schedulings.sports_day_other_finish_time', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control required mdtpicker', 'placeholder'=>'Finish Time']); ?>
						  </div>
						</div>
					
					</div>
					
					<div class="form-group">
                      <label class="col-sm-2 control-label">&nbsp;</label>
                      <div class="col-sm-10" style="color:red;">
							Don't forget to allow travel time between the sports venue and the convention site.
                      </div>
                    </div>
					<!-- Sports Day Ends -->
					
					
					
					
					
					
                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->input('Schedulings.id', ['label'=>false, 'type'=>'hidden']); ?>
						<div class="wizard-actions">
							<?php echo $this->Form->button('<i class="fa fa-save"></i> Save Wizard Settings', ['type'=>'submit', 'escapeTitle'=>false, 'class' => 'btn btn-info', 'div'=>false]); ?>
							<?php echo $this->Html->link('<i class="fa fa-times"></i> Cancel', ['controller'=>'schedulings', 'action' => 'precheck', $convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default']); ?>
						</div>
                    </div>
					</div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
  </div>

<script type="text/javascript">
	$(document).ready(function() {
		function bindToggle(checkboxSelector, boxSelector) {
			function refresh() {
				if ($(checkboxSelector).prop('checked')) {
					$(boxSelector).show();
				} else {
					$(boxSelector).hide();
				}
			}

			$(checkboxSelector).on('change', refresh);
			refresh();
		}

		bindToggle('#starting_different_time_first_day_yes_no', '#box_starting_different_time_first_day_yes_no');
		bindToggle('#judging_breaks_yes_no', '#box_judging_breaks_yes_no');
		bindToggle('#sports_day_yes_no', '#box_sports_day_yes_no');
		bindToggle('#sports_day_having_events_after_sport_yes_no', '#box_sports_day_having_events_after_sport_yes_no');
	});
</script>

  