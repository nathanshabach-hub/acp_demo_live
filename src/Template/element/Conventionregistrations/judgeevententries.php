<?php
use Cake\ORM\TableRegistry;
$this->Users 				= TableRegistry::getTableLocator()->get('Users');
$this->Judgeevaluations 	= TableRegistry::getTableLocator()->get('Judgeevaluations');
$this->Crstudentevents 		= TableRegistry::getTableLocator()->get('Crstudentevents');
$eventIdNumber = str_pad((string)$eventD->event_id_number, 3, "0", STR_PAD_LEFT);
$isBulkSpellingEvent = in_array($eventIdNumber, ['003', '053'], true);
$placeRankingEventNumbers = ['001', '002', '051', '052', '109', '110', '139', '140', '169', '170', '174', '175', '177', '209', '210', '239', '240', '269', '270', '274', '275', '277'];
$isPlaceRankingEvent = stripos((string)$eventD->event_name, 'Futsal') !== false || in_array($eventIdNumber, $placeRankingEventNumbers, true);
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <div class="panel-body">
		<div class="judge-entries-toolbar">
			<div>
				<div class="judge-entries-title"><?php echo $eventD->event_name; ?> (<?php echo $eventD->event_id_number; ?>)</div>
				<div class="judge-entries-subtitle">Total entries: <?php echo count($eventsubmissions); ?><?php if($isBulkSpellingEvent) { ?> · Enter scores inline, then save all at once<?php } ?><?php if($isPlaceRankingEvent) { ?> · Enter placings inline<?php } ?></div>
			</div>
			<?php if($isBulkSpellingEvent || $isPlaceRankingEvent) { ?>
				<div class="judge-entries-toolbar-actions">
					<button type="submit" form="actionFrom" class="btn btn-primary btn-sm judge-save-all-btn"><?php echo $isPlaceRankingEvent ? 'Save All Placings' : 'Save All Spelling Scores'; ?></button>
				</div>
			<?php } ?>
		</div>
        
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
             

            <div class="tbl-resp-listing">
				<table id="group_events_table" class="table table-striped table-bordered judge-entries-table" style="width:100%">
					<thead>
						<tr>
							<th class="sorting_paging col-student">
							<?php
							if($eventD->group_event_yes_no == 1)
							{
								echo 'Group';
							}
							else
							{
								echo "Student Name";
							}
							?>
							</th>
							<th class="sorting_paging col-school">School</th>
							<th class="sorting_paging col-context">Context</th>
							<th class="sorting_paging col-file">Submitted File</th>
							<th class="sorting_paging col-date">Submission Date</th>
							<th class="sorting_paging col-breach">Guideline Breach</th>
							<th class="sorting_paging col-command">Command Performance</th>
							<th class="sorting_paging col-completed">Completed</th>
							<th class="sorting_paging col-score"><?php echo $isPlaceRankingEvent ? 'Place' : 'Score'; ?></th>
							<th class="sorting_paging col-action">Judges Evaluation</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($eventsubmissions as $datarecord) { ?>
						<tr>
						<td data-title="Student Name / Group">
						
						<?php
						if($eventD->group_event_yes_no == 1)
						{
							echo $datarecord->group_name.'<br>';
							
							// now fetch all name of students of this group
							$condAllUGroup = array();
							$condAllUGroup[] = "(Crstudentevents.conventionregistration_id = '".$datarecord->conventionregistration_id."' AND Crstudentevents.event_id = '".$datarecord->event_id."' AND Crstudentevents.group_name = '".$datarecord->group_name."')";
							
							$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->limit(4)->all();
							
							$arrNG = array();
							foreach($listAllUGroup as $datamembgroup)
							{
								$nameV =  $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name'];
								$arrNG[] = $nameV;
							}
							
							echo '('.implode(", ",$arrNG).')';
							
						?>
							<a title="View all members of group" href="#" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#exampleModal_<?php echo $datarecord->id; ?>" style="width:45px;">
								  <i class="fa fa-eye"></i>
							</a>
								
							<div class="modal fade" id="exampleModal_<?php echo $datarecord->id; ?>" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
							  <div class="modal-dialog">
								<div class="modal-content">
								
								  <div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Group <?php echo $datarecord->group_name; ?></h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								  </div>
								  
								  <div class="modal-body">
									
									<table class="table">
										<thead>
											<tr>
												<th scope="col">#</th>
												<th scope="col">Name</th>
											</tr>
										</thead>
										<tbody>
											<?php
											// now fetch all students of this group
											$listAllUGroup = $this->Crstudentevents->find()->where($condAllUGroup)->contain(["Students"])->all();
											$cntrMM = 1;
											foreach($listAllUGroup as $datamembgroup)
											{
											?>
											<tr>
												<td scope="row"><?php echo $cntrMM; ?></td>
												<td colspan="2"><?php echo $datamembgroup->Students['first_name'].' '.$datamembgroup->Students['middle_name'].' '.$datamembgroup->Students['last_name']; ?></td>
											</tr>
											<?php
											$cntrMM++;
											}
											?>
										</tbody>
									</table>
									
								  </div>
								
								</div>
							  </div>
							</div>
							
						<?php
						}
						else
						{
							echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
						}
						?>
						</td>
						<td data-title="School" class="col-school"><?php echo $datarecord->Users['first_name']; ?></td>
						<td data-title="Context" class="col-context"><?php echo ($datarecord->context_box) ? $datarecord->context_box : "N/A"; ?></td>
						<td data-title="Submitted File" class="col-file">
						<?php
							$imgToShow = $datarecord->mediafile_file_system_name;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<a target="_blank" title="'.$datarecord->Events['upload_type'].'" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->report;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Report" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->score_sheet;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Score Sheet" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						
						<?php
							$imgToShow = $datarecord->additional_documents;
							if(file_exists(UPLOAD_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow) && !empty($imgToShow))
							{
								echo '<br /><a style="color:#000;" target="_blank" title="Additional Documents" href="'.DISPLAY_EVENTS_SUBMISSION_DOCUMENT_PATH.$imgToShow.'"><i class="fa fa-cloud-download"></i></a>';
							}
						?>
						</td>
						<td data-title="Submission Date" class="col-date"><?php echo date('M d, Y', strtotime($datarecord->created)); ?></td>
						
						<td data-title="Guideline Breach" class="col-breach">
							<?php
							if($datarecord->guideline_breach == 0)
							{
								echo '<button type="button" class="btn btn-info btn-mark-breach" data-slug="'.h($datarecord->slug).'" data-bs-toggle="modal" data-bs-target="#markBreachModal">Mark Breach</button>';
							}
							else
							if($datarecord->guideline_breach == 1)
							{
								echo 'Awaiting action from admin';
							}
							else
							if($datarecord->guideline_breach == 2)
							{
								echo 'Approved By Admin';
							}
							?>
						</td>
						
						<td data-title="Command Performance" class="col-command">
							<?php
							if($datarecord->guideline_breach == 0)
							{
								if($datarecord->command_performance == 1)
								{
									echo 'Yes';
								}
								else
								{
									echo $this->Html->link('Mark Command', ['controller' => 'judgeevaluations', 'action' => 'markcommand',$datarecord->slug], [ 'escape' => false, 'title' => 'Mark Command Performance', 'class'=>'', 'confirm' => 'Are you sure you want to mark this entry as command performance?', 'class' => 'btn btn-success']);
								}
							}
							?>
						</td>
						
						<td data-title="Completed" class="col-completed">
							<?php
							// to check if this entry judged by this judge or not
							$condCheckJudging = array();
							$condCheckJudging[] = "(Judgeevaluations.eventsubmission_id = '".$datarecord->id."')";
							$condCheckJudging[] = "(Judgeevaluations.uploaded_by_user_id = '".$this->request->getSession()->read("user_id")."')";
							$checkJudging = $this->Judgeevaluations->find()->where($condCheckJudging)->count();
							if($checkJudging>0)
							{
								echo '<i title="Entry judged by you" class="fa fa-check-circle"></i>';
							}
							?>
						</td>
						
						<td data-title="Score" class="col-score">
						<?php
						// to get score by judge
						$getScores = $this->Judgeevaluations->find()->where($condCheckJudging)->first();
						if(!empty($getScores) && $getScores->did_not_attend == 0)
						{
							if($isBulkSpellingEvent)
							{
								echo ($getScores->spelling_score !== null) ? $getScores->spelling_score : 'N/A';
							}
							else if($isPlaceRankingEvent)
							{
								echo ($getScores->all_pos_score !== null) ? $getScores->all_pos_score : 'N/A';
							}
							else
							{
								echo "$getScores->total_marks_obtained/$getScores->total_marks_possible";
							}
						}
						else if(empty($getScores))
						{
							echo 'N/A';
						}
						else
						{
							echo 'Did not attend';
						}
						?>
						</td>
						
						
						
						<td data-title="Action" class="col-action">
						<?php
						$primaryActionHtml = '';
						if($isBulkSpellingEvent && $datarecord->guideline_breach != 2)
						{
							$primaryActionHtml .= '<div class="judge-action-inline">';
							$primaryActionHtml .= '<input type="hidden" name="spelling_submission_ids[]" value="'.(int)$datarecord->id.'" />';
							$primaryActionHtml .= '<input type="number" class="form-control judge-score-input" name="spelling_score_'.(int)$datarecord->id.'" min="0" max="50" value="'.h($getScores->spelling_score ?? '').'" placeholder="Score" />';
							$primaryActionHtml .= '</div>';
						}
						else if($isPlaceRankingEvent && $datarecord->guideline_breach != 2)
						{
							$primaryActionHtml .= '<div class="judge-action-inline">';
							$primaryActionHtml .= '<input type="hidden" name="place_submission_ids[]" value="'.(int)$datarecord->id.'" />';
							$primaryActionHtml .= '<input type="number" class="form-control judge-score-input" name="place_score_'.(int)$datarecord->id.'" min="1" max="4" value="'.h($getScores->all_pos_score ?? '').'" placeholder="Placed" />';
							$primaryActionHtml .= '</div>';
						}
						else
						if($datarecord->guideline_breach != 2)
						{
							$primaryActionHtml .= $this->Html->link('<i class="fa fa-street-view"></i>', ['controller' => 'judgeevaluations', 'action' => 'addnew',$conv_reg_slug,$datarecord->slug], [ 'escape' => false, 'title' => 'Submit Evaluation', 'class'=>'', 'confirm' => 'Are you sure you want to submit evaluation for this entry?']);
						}
						
						// Mark entry as did not attend, for individual event only
						//if($eventD->group_event_yes_no == 0)
						//{
							$didNotAttendHtml = $this->Html->link('<i class="fa fa-times-circle-o"></i>', ['controller' => 'judgeevaluations', 'action' => 'markdidnotattend',$conv_reg_slug,$datarecord->slug], [ 'escape' => false, 'title' => 'Mark As Did Not Attend', 'class'=>'', 'confirm' => 'Are you sure you want to mark this submission as did not attend?']);
						//}

						echo '<div class="judge-action-cell">';
						echo '<div class="judge-action-left">'.$primaryActionHtml.'</div>';
						echo '<div class="judge-action-right">'.$didNotAttendHtml.'</div>';
						echo '</div>';
						
						?>
						</td>
					</tr>
						<?php } ?>
					</tbody>
				</table>
				<?php if($isBulkSpellingEvent || $isPlaceRankingEvent) { ?>
				<div class="judge-entries-sticky-footer">
					<div class="judge-entries-footer-note"><?php echo $isPlaceRankingEvent ? 'Allowed range: 1 to 4.' : 'Allowed range: 0 to 50'; ?></div>
					<button type="submit" class="btn btn-primary judge-save-all-btn"><?php echo $isPlaceRankingEvent ? 'Save All Placings' : 'Save All Spelling Scores'; ?></button>
				</div>
				<?php } ?>
            </div>
        </section>

         
        
        <?php echo $this->Form->end(); ?>
    
    </div>

<!-- Mark Breach Modal -->
<div class="modal fade" id="markBreachModal" tabindex="-1" aria-labelledby="markBreachModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="markBreachModalLabel">Mark Guideline Breach</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <?php echo $this->Form->create(null, ['id' => 'markBreachForm', 'method' => 'post', 'url' => ['controller' => 'judgeevaluations', 'action' => 'markbreach', 'PLACEHOLDER']]); ?>
      <div class="modal-body">
        <p>Please explain why you are marking this entry as a guideline breach:</p>
        <textarea name="breach_reason" id="breach_reason" class="form-control" rows="4" required placeholder="Enter reason for breach..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Submit Breach</button>
      </div>
      <?php echo $this->Form->end(); ?>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  var markBreachModal = document.getElementById('markBreachModal');
  if (markBreachModal) {
    markBreachModal.addEventListener('show.bs.modal', function(e) {
      var btn = e.relatedTarget;
      var slug = btn.getAttribute('data-slug');
      var form = document.getElementById('markBreachForm');
      form.action = form.action.replace('PLACEHOLDER', slug);
    });
    markBreachModal.addEventListener('hide.bs.modal', function() {
      var form = document.getElementById('markBreachForm');
      form.action = form.action.replace(/\/[^\/]+$/, '/PLACEHOLDER');
      document.getElementById('breach_reason').value = '';
    });
  }
});
</script>

<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="admin_no_record">No record found.</div>
<?php }
?>

<script>
$(document).ready(function() {
$('#group_events_table').dataTable({
    //"bPaginate": false,
    "bLengthChange": false,
	"pageLength": 50,
	order: [[0, 'asc']],
    //"bFilter": true,
    //"bInfo": false,
    //"bAutoWidth": false
	});
	/* $('#searchInput').on('keyup', function() {
        $('#group_events_table').dataTable.search(this.value).draw();
    }); */
});
</script>

<script type="text/javascript" language="javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
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

<style type="text/css">
	.judge-entries-toolbar {
		align-items: center;
		background: #f8fafc;
		border: 1px solid #e5e7eb;
		border-radius: 10px;
		display: flex;
		justify-content: space-between;
		gap: 16px;
		margin-bottom: 14px;
		padding: 14px 16px;
	}

	.judge-entries-title {
		color: #1f2937;
		font-size: 20px;
		font-weight: 700;
		line-height: 1.2;
	}

	.judge-entries-subtitle {
		color: #6b7280;
		font-size: 13px;
		margin-top: 4px;
	}

	.judge-entries-table {
		table-layout: fixed;
	}

	.judge-entries-table th,
	.judge-entries-table td {
		vertical-align: middle !important;
		word-break: break-word;
	}

	.judge-entries-table .col-student,
	.judge-entries-table .col-school {
		width: 16%;
	}

	.judge-entries-table .col-context {
		width: 9%;
	}

	.judge-entries-table .col-file {
		width: 8%;
	}

	.judge-entries-table .col-date {
		width: 10%;
	}

	.judge-entries-table .col-breach {
		width: 9%;
	}

	.judge-entries-table .col-command {
		width: 10%;
	}

	.judge-entries-table .col-completed {
		width: 7%;
		text-align: center;
	}

	.judge-entries-table .col-score {
		width: 7%;
		text-align: center;
	}

	.judge-entries-table .col-action {
		width: 16%;
	}

	.judge-action-inline {
		align-items: center;
		display: flex;
		gap: 8px;
	}

	.judge-action-cell {
		align-items: center;
		display: flex;
		gap: 8px;
		justify-content: space-between;
	}

	.judge-action-left {
		display: flex;
		align-items: center;
		gap: 8px;
		min-width: 0;
	}

	.judge-action-right {
		display: flex;
		align-items: center;
		margin-left: auto;
	}

	.judge-score-input {
		max-width: 110px;
		min-width: 110px;
	}

	.judge-score-hint {
		color: #6b7280;
		font-size: 12px;
		white-space: nowrap;
	}

	.judge-entries-sticky-footer {
		align-items: center;
		background: #ffffff;
		border-top: 1px solid #e5e7eb;
		bottom: 0;
		box-shadow: 0 -4px 18px rgba(15, 23, 42, 0.08);
		display: flex;
		gap: 12px;
		justify-content: space-between;
		margin-top: 14px;
		padding: 12px 4px 4px;
		position: sticky;
		z-index: 5;
	}

	.judge-entries-footer-note {
		color: #6b7280;
		font-size: 13px;
	}

	.judge-save-all-btn {
		min-width: 190px;
	}

	@media (max-width: 992px) {
		.judge-entries-table .col-context,
		.judge-entries-table .col-file {
			display: none;
		}

		.judge-entries-table .col-student,
		.judge-entries-table .col-school {
			width: 22%;
		}

		.judge-entries-table .col-action {
			width: 20%;
		}
	}

	@media (max-width: 768px) {
		.judge-entries-toolbar,
		.judge-entries-sticky-footer {
			align-items: flex-start;
			flex-direction: column;
		}

		.judge-entries-table .col-breach,
		.judge-entries-table .col-command {
			display: none;
		}

		.judge-entries-table .col-student,
		.judge-entries-table .col-school {
			width: auto;
		}

		.judge-entries-table .col-action {
			width: auto;
		}
	}
</style>

<style>
.moretext {display: none;}
</style>





