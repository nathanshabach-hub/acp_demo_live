<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<style>
  #facebox.breach-facebox .content { width: 460px; padding: 0; }
  .breach-popup-header { background: #f7f7f7; border-bottom: 1px solid #ddd; padding: 14px 20px; font-size: 15px; font-weight: 600; color: #333; border-radius: 4px 4px 0 0; }
  .breach-popup-body { background: #fff; padding: 20px 25px; font-size: 14px; color: #444; line-height: 1.6; border-radius: 0 0 4px 4px; }
  #guideline_breach td, #guideline_breach th { vertical-align: middle !important; }
  .event-num { font-weight: 600; font-size: 13px; }
  .event-name-sub { font-size: 11px; color: #777; margin-top: 2px; }
  .badge-pending { background: #f0ad4e; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; white-space: nowrap; }
  .badge-approved { background: #5cb85c; color: #fff; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; white-space: nowrap; }
</style>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo $this->Url->build("/img/loading.gif"); ?>',
            closeImage: '<?php echo $this->Url->build("/img/close.png"); ?>'
        });
        $(document).on('reveal.facebox', function() {
            $('#facebox').addClass('breach-facebox');
            var left = $(window).width() / 2 - ($('#facebox .popup').outerWidth() / 2);
            $('#facebox').css('left', left < 0 ? 0 : left);
        });
        $(document).on('close.facebox', function() {
            $('#facebox').removeClass('breach-facebox');
        });
    })            
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Events = TableRegistry::getTableLocator()->get('Events');
$this->Judgeevaluations = TableRegistry::getTableLocator()->get('Judgeevaluations');
?>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$eventsubmissions->isEmpty()) { ?> 
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
        <?php echo $this->Form->create(null, ['id'=>'actionFrom', "method" => "Post"]);  ?>
        <section id="no-more-tables" class="lstng-section">
            <div class="topn">
                <div class="topn_left">Guideline Breach</div>
                <div class="topn_right ajshort" id="pagingLinks" align="right">
                    <?php 
                        $this->Paginator->options(array('update' => '#listID', 'url' => ['controller'=>'Eventsubmissions', 'action'=>'guidelinebreach', $separator]));
                        echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                        echo $this->Paginator->prev('« Prev');
                        echo $this->Paginator->numbers();
                        echo $this->Paginator->next('Next »');
                        
                    ?>
                </div>
            </div>   

            <div class="tbl-resp-listing">
                <table id="guideline_breach" class="table table-bordered table-striped table-condensed cf">
                    <thead class="cf ajshort">
                        <tr>
                            <th class="sorting_paging">#ID</th>
                            <th class="sorting_paging">School</th>
							<th class="sorting_paging">Convention</th>
                            <th class="sorting_paging">Event</th>
                            <th class="sorting_paging">Group</th>
                            <th class="sorting_paging">Student</th>
                            <th class="sorting_paging">Judge</th>
						<th class="sorting_paging">Status</th>
						<th class="sorting_paging" style="text-align:center;">Reason</th>
							<th class="action_dvv"><i class=" fa fa-gavel"></i> Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
						$cntrS = 0;
						$breachReasonDivs = '';
						foreach ($eventsubmissions as $datarecord)
						{
							$cntrS++;
						$reasonText = !empty($datarecord->breach_reason) ? h($datarecord->breach_reason) : '<em>No reason provided.</em>';
						$breachReasonDivs .= '<div id="breach-reason-'.$datarecord->id.'" style="display:none;"><div class="breach-popup-header">Breach Reason</div><div class="breach-popup-body">'.$reasonText.'</div></div>';
						?>
                            <tr>
                                <td data-title="#ID"><?php echo $datarecord->id;?></td>
								<td data-title="School"><?php echo $datarecord->Uploadeduser['first_name'].' '.$datarecord->Uploadeduser['last_name'];?></td>
								<td data-title="Convention"><?php echo $datarecord->Conventions['name'];?></td>
							<td data-title="Event">
								<div class="event-num"><?php echo $datarecord->event_id_number;?></div>
								<div class="event-name-sub"><?php echo $datarecord->Events['event_name'];?></div>
							</td>
							<td data-title="Group">
							<?php
							if(!empty($datarecord->group_name)) {
								echo 'Group '.$datarecord->group_name;
							} else {
								echo '<span style="color:#aaa;">Individual</span>';
							}
							?>
							</td>
							<td data-title="Student">
							<?php
							if($datarecord->student_id > 0) {
								echo $datarecord->Students['first_name'].' '.$datarecord->Students['middle_name'].' '.$datarecord->Students['last_name'];
							} else {
								echo '<span style="color:#aaa;">-</span>';
							}
							?>
							</td>
							<td data-title="Judge"><?php echo $datarecord->Judge['first_name'].' '.$datarecord->Judge['last_name']; ?></td>
							<td data-title="Status">
							<?php if($datarecord->guideline_breach == 2): ?>
								<span class="badge-approved">Approved</span>
							<?php else: ?>
								<span class="badge-pending">Pending</span>
							<?php endif; ?>
							</td>
							<td data-title="Reason" style="text-align:center;">
								<?php echo '<a rel="facebox" href="#breach-reason-'.$datarecord->id.'" title="View Breach Reason" class="btn btn-info btn-xs"><i class="fa fa-eye"></i></a>'; ?>
							</td>
                                <td data-title="Action">
								<?php
								if($datarecord->guideline_breach == 1) {
									echo $this->Html->link('<i class="fa fa-check"></i>', ['controller' => 'eventsubmissions', 'action' => 'approveguidelinebreach', $datarecord->slug], ['escape' => false, 'title' => 'Approve', 'class' => 'btn btn-success btn-xs', 'confirm' => 'Are you sure you want to approve this guideline breach?']);
									echo $this->Html->link('<i class="fa fa-times"></i>', ['controller' => 'eventsubmissions', 'action' => 'rejectguidelinebreach', $datarecord->slug], ['escape' => false, 'title' => 'Reject', 'class' => 'btn btn-warning btn-xs', 'confirm' => 'Are you sure you want to reject this guideline breach?']);
								} elseif($datarecord->guideline_breach == 2) {
									echo $this->Html->link('<i class="fa fa-trash"></i>', ['controller' => 'eventsubmissions', 'action' => 'deleteguidelinebreach', $datarecord->slug], ['escape' => false, 'title' => 'Delete', 'class' => 'btn btn-danger btn-xs', 'confirm' => 'Are you sure you want to delete this guideline breach?']);
								} else {
									echo '<span style="color:#aaa;">-</span>';
								}
								?>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php echo $breachReasonDivs; ?>

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

<?php if (isset($conventionregistrationstudents) && !empty($conventionregistrationstudents)) { ?>
<?php foreach ($conventionregistrationstudents as $datarecord) { ?>
    <div id="info<?php echo $datarecord->id; ?>" style="display: none;">
        <!-- Fieldset -->
        <div class="nzwh-wrapper">
            <fieldset class="nzwh">
                <legend class="head_pop">
				<?php echo $datarecord->Students['first_name'];?> <?php echo $datarecord->Students['middle_name'];?> <?php echo $datarecord->Students['last_name'];?> [Total Events: <?php echo count(explode(",",$datarecord->event_ids)); ?>]
                </legend>
                <div class="drt">
					
					<table class="table table-bordered table-striped table-condensed cf">
					
					<?php
					$condStudEvents = array();
					if($datarecord->event_ids != '' && $datarecord->event_ids != NULL)
					{
						$condStudEvents[] = "(Events.id  IN ($datarecord->event_ids) )";
					
					$eventsL = $this->Events->find()->where($condStudEvents)->all();
					?>
					<tr>
						<td>
							Event Number
						</td>
						<td>
							Event Name
						</td>
					</tr>
					<?php
					foreach($eventsL as $eventd)
					{
					?>
					
					<tr>
                        <td><?php echo $eventd->event_id_number; ?></td>
                        <td><?php echo $eventd->event_name; ?></td>
                    </tr>
					<?php
					}
					?>
					
					<?php
					}
					else
					{
					?>
                    <tr colspan="2">
                        <td>Sorry, no event found.</td>
                    </tr>
					<?php
					}
					?>
					</table>
					
					
					
                    
                </div>
            </fieldset>
        </div>
    </div>
<?php } ?>
<?php } ?>

<script>
$(document).ready(function() {
$('#guideline_breach').dataTable({
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
        $('#guideline_breach').dataTable.search(this.value).draw();
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