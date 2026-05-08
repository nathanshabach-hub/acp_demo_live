<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo HTTP_IMAGE ?>/loading.gif',
            closeImage: '<?php echo HTTP_IMAGE ?>/close.png'
        })
    })            
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Evaluationquestions = TableRegistry::getTableLocator()->get('Evaluationquestions');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!empty($evaluationUpdateRows)) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Evaluation Update</div>
                
            </div>   

            <div class="tbl-resp-listing">
                <table id="judge_eval_table" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">Judge</th>
                            <th class="sorting_paging">Registered Events</th>
                            <th class="sorting_paging">Judged / Not Judged</th>
                            <th class="sorting_paging">School</th>
							<th class="sorting_paging">Student</th>
							<th class="sorting_paging">Submitted</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						$cntrS = 0;
						foreach ($evaluationUpdateRows as $row)
						{
							$cntrS++;
							$datarecord = $row['evaluation'];
						?>
                            <tr>
                                <td data-title="Judge"><?php echo h($row['judge_name']); ?></td>
                                <td data-title="Registered Events"><?php echo (int)$row['registered_events']; ?></td>
                                <td data-title="Judged / Not Judged"><?php echo (int)$row['judged_events']; ?> / <?php echo (int)$row['not_judged_events']; ?></td>
                                <td data-title="School"><?php echo h($row['school_name']); ?></td>
                                <td data-title="Student"><?php echo h($row['student_name']); ?></td>
                                <td data-title="Submitted Date"><?php echo !empty($row['submitted_date']) ? date('M d, Y', strtotime($row['submitted_date'])) : '-'; ?></td>
                                <td data-title="Action">
									<?php if(!empty($datarecord)) { ?>
									<a href="#info<?php echo $datarecord->id; ?>" rel="facebox" title="Preview Evaluation" class="btn btn-info btn-xs eyee"><i class="fa fa-eye "></i></a>
									<?php
									echo $this->Html->link('<i class="fa fa-trash-o"></i>', ['controller' => 'judgeevaluations', 'action' => 'removejudgeevaluation',$datarecord->slug], [ 'escape' => false, 'title' => 'Delete', 'class'=>'btn btn-info btn-xs', 'confirm' => 'Are you sure you want to remove this judge evaluation?']);
									?>
									<?php } else { echo '-'; } ?>
									
                                </td>
								
                                
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="search_frm" style="display:none;">
            <button type="button" name="chkRecordId" onclick="checkAll(true);"  class="btn btn-info">Select All</button>
            <button type="button" name="chkRecordId" onclick="checkAll(false);" class="btn btn-info">Unselect All</button>
            <?php
            $arr = array(
                "" => "Action for selected record",
                'Activate' => "Activate",
                'Deactivate' => "Deactivate",
                //'Delete' => "Delete",
            );
            ?>
            <div class="list_sel"><?php echo $this->Form->input('action', ['options' => $arr, 'type'=>'select', 'label'=>false, 'class'=>"small form-control",'id'=>'action']);?></div>
            <button type="submit" class="small btn btn-success btn-cons btn-info" onclick="return ajaxActionFunction();" id="submit_action">OK</button>
        </div>
        <?php 
        if (isset($keyword) && $keyword != '') {
            echo $this->Form->input('Conventionregistrations.keyword', ['label'=>false, 'type'=>'hidden', 'value'=>$keyword]);
        }?>
        <?php echo $this->Form->end(); ?>
    
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<?php foreach ($evaluationUpdateRows as $row) {
	$datarecord = $row['evaluation'];
	if(empty($datarecord)) { continue; }
?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
				
				<?php
				if($datarecord->Eventsubmissions['student_id'] > 0)
				{
					echo 'Student: '.$datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
				}
				else
				if(!empty($datarecord->Eventsubmissions['group_name']))
				{
					echo "Group ".$datarecord->Eventsubmissions['group_name'];
				}
				?>
				
				[Event: <?php echo $datarecord->Events['event_name']; ?> (<?php echo $datarecord->Events['event_id_number']; ?>)]
                </legend>
                <div class="drt">
					
					<?php
					if($datarecord->Events['event_judging_type'] == 'general')
					{
					?>
					<table class="table table-bordered table-striped table-condensed cf">
					
					<tr>
						<td colspan="4">Comments: <?php echo $datarecord->comments ? $datarecord->comments : 'N/A'; ?></td>
					</tr>
					<tr>
						<td>#</td>
						<td>Question</td>
						<td>Max Possible Marks</td>
						<td>Marks Obtained</td>
					</tr>
					<?php
					$cntrQ = 1;
					foreach($datarecord->Judgeevaluationmarks as $judgevalmark)
					{
						$questionD = $this->Evaluationquestions->find()->where(["Evaluationquestions.id" => $judgevalmark->question_id])->first();
					?>
					
					<tr>
                        <td><?php echo $cntrQ; ?></td>
                        <td><?php echo $questionD->question; ?></td>
                        <td><?php echo $judgevalmark->question_marks_possible; ?></td>
                        <td><?php echo $judgevalmark->question_marks_obtained; ?></td>
                    </tr>
					<?php
					$cntrQ++;
					}
					?>
					
					<tr>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                        <td><?php echo $datarecord->total_marks_possible; ?></td>
                        <td><?php echo $datarecord->total_marks_obtained; ?></td>
                    </tr>
					
					</table>
					<?php
					}
					else
					if($datarecord->Events['event_judging_type'] == 'distances')
					{
					?>
					<table class="table table-bordered table-striped table-condensed cf">
					<tr>
						<td>1st Attempt</td>
						<td>2nd Attempt</td>
						<td>3rd Attempt</td>
						<td><b>Best Score</b></td>
					</tr>
					<tr>
						<td><?php echo $datarecord->distance_attempt_1 ?></td>
						<td><?php echo $datarecord->distance_attempt_2 ?></td>
						<td><?php echo $datarecord->distance_attempt_3 ?></td>
						<td><b><?php echo $datarecord->distance_score ?></b></td>
					</tr>
					</table>
					<?php
					}
					else
					if($datarecord->Events['event_judging_type'] == 'scores')
					{
					?>
					<table class="table table-bordered table-striped table-condensed cf">
					<tr>
						<td>Position</td>
						<td>Status</td>
						<td>Score</td>
					</tr>
					<?php
					for($cntrP=1;$cntrP<=9;$cntrP++)
					{
						$propYN = 'pos_'.$cntrP.'_yes_no';
						$propC 	= 'pos_'.$cntrP.'_score';
					?>
					<tr>
						<td><?php echo $cntrP; ?></td>
						<td><?php echo $datarecord->$propYN ? "Yes" : "No"; ?></td>
						<td><?php echo $datarecord->$propC ? $datarecord->$propC : ""; ?></td>
					</tr>
					<?php
					}
					?>
					
					<tr>
						<td colspan="3">Competitors Choice</td>
					</tr>
					<tr>
						<td>X1: <?php echo $datarecord->comp_choice_pos_1; ?></td>
						<td><?php echo $datarecord->comp_choice_pos_1 ? "Yes" : "No"; ?></td>
						<td><?php echo $datarecord->comp_choice_pos_1_score; ?></td>
					</tr>
					<tr>
						<td>X2: <?php echo $datarecord->comp_choice_pos_2; ?></td>
						<td><?php echo $datarecord->comp_choice_pos_2 ? "Yes" : "No"; ?></td>
						<td><?php echo $datarecord->comp_choice_pos_2_score; ?></td>
					</tr>
					<tr>
						<td>X3: <?php echo $datarecord->comp_choice_pos_3; ?></td>
						<td><?php echo $datarecord->comp_choice_pos_3 ? "Yes" : "No"; ?></td>
						<td><?php echo $datarecord->comp_choice_pos_3_score; ?></td>
					</tr>
					<tr>
						<td colspan="3">Total Score: <?php echo $datarecord->all_pos_score; ?></td>
					</tr>
					</table>
					<?php
					}
					else
					if($datarecord->Events['event_judging_type'] == 'soccer_kick')
					{
						$all_kicks = json_decode($datarecord->soccer_kick_all_kicks);
					?>
					<table class="table table-bordered table-striped table-condensed cf">
						<tr>
							<td>Best Score</td>
							<td><?php echo $datarecord->soccer_kick_best_kick; ?>m</td>
							<td></td>
							<td></td>
						</tr>
						<?php
						for($cntrKD=10;$cntrKD<=50;$cntrKD+=5)
						{
						?>
							<tr>
								<td><?php echo $cntrKD; ?>m</td>
								<?php
								for($cntrAtt=1;$cntrAtt<=3;$cntrAtt++)
								{
								?>
								<td>Attempt 1: 
								<?php
								if(in_array($cntrKD.'_'.$cntrAtt,$all_kicks))
								{
									echo '<b>Yes</b>';
								}
								else
								{
									echo 'No';
								}
								?>
								</td>
								<?php
								}
								?>
							</tr>
						<?php
						}
						?>
					</table>
						
					<?php
					}
					else
					if($datarecord->Events['event_judging_type'] == 'spellings')
					{
					?>
					<table class="table table-bordered table-striped table-condensed cf">
						<tr>
							<td>Score</td>
							<td><?php echo $datarecord->spelling_score; ?></td>
						</tr>
					</table>
					<?php
					}
					?>
					
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>

<script>
$(document).ready(function() {
$('#judge_eval_table').dataTable({
    "bPaginate": true,
    //"bInfo": false,
    "bLengthChange": false,
	"pageLength": 100,
	order: [[0, 'asc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#judge_eval_table').dataTable.search(this.value).draw();
    }); */
});
</script>

<!--
<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
-->
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" language="javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">

<style type="text/css">
    .page-link {
        color: #1c2452 !important;
        background-color: #fff !important;
    }

    .active>.page-link,
    .page-link.active {
        background-color: #1c2452 !important;
        border-color: #1c2452 !important;
        color: #fff !important;
    }

    .pagination {
        border-radius: 0rem !important;
    }
</style>