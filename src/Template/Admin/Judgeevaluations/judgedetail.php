<?php echo $this->Html->script('facebox.js'); ?>
<?php echo $this->Html->css('facebox.css'); ?>
<style>
    .judge-detail-wrap { background: #fff; border: 1px solid #ddd; padding: 15px; }
  #judge_detail_table td, #judge_detail_table th { vertical-align: middle !important; }
  .detail-header { font-weight: 600; font-size: 16px; margin-bottom: 20px; }
  .event-row-judged { background-color: #f0f8f5; }
  .event-row-unjudged { background-color: #fef8f5; }
  .event-name { font-weight: 600; font-size: 13px; }
  .status-badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: 600; }
  .status-judged { background: #5cb85c; color: #fff; }
  .status-unjudged { background: #d9534f; color: #fff; }
  .eye-icon-badge { display: inline-block; background: #337ab7; color: #fff; padding: 4px 8px; border-radius: 3px; margin: 2px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; }
  .eye-icon-badge:hover { background: #286090; text-decoration: none; }
  .back-button { margin-bottom: 15px; }
    #judge_detail_table { table-layout: fixed; width: 100%; }
  #judge_detail_table thead { display: table-header-group; }
  #judge_detail_table tbody { display: table-row-group; }
  #judge_detail_table tr { display: table-row; }
  #judge_detail_table td, #judge_detail_table th { display: table-cell; padding: 8px; border: 1px solid #ddd; }
    #judge_detail_table th:nth-child(1), #judge_detail_table td:nth-child(1) { width: 45%; }
    #judge_detail_table th:nth-child(2), #judge_detail_table td:nth-child(2) { width: 15%; }
    #judge_detail_table th:nth-child(3), #judge_detail_table td:nth-child(3) { width: 40%; }
    #judge_detail_table td:nth-child(3) { white-space: normal; overflow-wrap: anywhere; }
</style>
<script type="text/javascript">
    $(document).ready(function ($) {
        $('.close_image').hide();
        $('a[rel*=facebox]').facebox({
            loadingImage: '<?php echo $this->Url->build("/img/loading.gif"); ?>',
            closeImage: '<?php echo $this->Url->build("/img/close.png"); ?>'
        })
    })
</script>
<?php
use Cake\ORM\TableRegistry;
$this->Evaluationquestions = TableRegistry::getTableLocator()->get('Evaluationquestions');
?>
<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>

<div class="content-wrapper">
    <section class="content">
        <div class="box box-info">
            <div class="panel-body judge-detail-wrap">
                <div class="back-button">
                    <a href="<?php echo $this->Url->build(['action' => 'index']); ?>" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Judge Evaluations
                    </a>
                </div>
                
                <div class="detail-header">
                    <i class="fa fa-user-circle"></i> <?php echo h($judge_name); ?> - Event Evaluations
                </div>
                
                <?php if (!empty($eventDetails)) { ?>
                    <table id="judge_detail_table" class="table table-bordered table-striped table-condensed cf">
                        <thead class="cf ajshort">
                            <tr>
                                <th class="sorting_paging">Event Name</th>
                                <th class="sorting_paging">Status</th>
                                <th class="sorting_paging" style="width: 100px;"><i class="fa fa-gavel"></i> Evaluations</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventDetails as $event) { ?>
                                <?php
                                $eventIdNumberPadded = str_pad((string)$event['id_number'], 3, '0', STR_PAD_LEFT);
                                $placeRankingEventNumbers = ['001', '002', '051', '052', '109', '110', '139', '140', '169', '170', '174', '175', '177', '209', '210', '239', '240', '269', '270', '274', '275', '277'];
                                $isPlaceRankingEvent = stripos((string)$event['name'], 'Futsal') !== false || in_array($eventIdNumberPadded, $placeRankingEventNumbers, true);
                                ?>
                                <tr class="<?php echo $event['is_judged'] ? 'event-row-judged' : 'event-row-unjudged'; ?>">
                                    <td data-title="Event Name">
                                        <span class="event-name"><?php echo h($event['name']); ?></span>
                                    </td>
                                    <td data-title="Status">
                                        <?php if($event['is_judged']): ?>
                                            <span class="status-badge status-judged">
                                                <i class="fa fa-check"></i> Judged
                                            </span>
                                        <?php else: ?>
                                            <span class="status-badge status-unjudged">
                                                <i class="fa fa-times"></i> Not Judged
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td data-title="Evaluations">
                                        <?php if(!empty($event['evaluations'])): ?>
                                            <?php foreach($event['evaluations'] as $eval): ?>
                                                <a href="#info<?php echo $eval->id; ?>" rel="facebox" title="View Evaluation" class="eye-icon-badge">
                                                    <i class="fa fa-eye"></i> <?php echo h($eval->Students['first_name'] ?? 'Eval'); ?>
                                                </a>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <span style="color:#aaa;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                <?php } else { ?>
                    <div class="admin_no_record">No events found for this judge.</div>
                <?php } ?>
            </div>
        </div>
    </section>
</div>

<?php foreach ($allEvaluations as $datarecord) { ?>
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
                    $eventIdNumberPadded = str_pad((string)$datarecord->Events['event_id_number'], 3, '0', STR_PAD_LEFT);
                    $placeRankingEventNumbers = ['001', '002', '051', '052', '109', '110', '139', '140', '169', '170', '174', '175', '177', '209', '210', '239', '240', '269', '270', '274', '275', '277'];
                    $isPlaceRankingEvent = stripos((string)$datarecord->Events['event_name'], 'Futsal') !== false || in_array($eventIdNumberPadded, $placeRankingEventNumbers, true);
                    if($isPlaceRankingEvent)
                    {
                        $placeValue = $datarecord->all_pos_score;
                        if(($placeValue === null || $placeValue === '') && isset($datarecord->place) && $datarecord->place !== null && $datarecord->place !== '') {
                            $placeValue = $datarecord->place;
                        }
                        $totalPlaces = !empty($datarecord->total_marks_possible) ? $datarecord->total_marks_possible : 4;
                    ?>
                    <table class="table table-bordered table-striped table-condensed cf">
                    <tr>
                        <td colspan="3">Comments: <?php echo $datarecord->comments ? $datarecord->comments : 'N/A'; ?></td>
                    </tr>
                    <tr>
                        <td>Place</td>
                        <td>Total Places</td>
                        <td>Note</td>
                    </tr>
                    <tr>
                        <td><b><?php echo ($placeValue !== null && $placeValue !== '') ? $placeValue : 'N/A'; ?></b></td>
                        <td><?php echo $totalPlaces; ?></td>
                        <td>1 is best, 4 is lowest</td>
                    </tr>
                    </table>
                    <?php
                    }
                    else
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
                            if($all_kicks)
                            {
                                $i = 1;
                                foreach($all_kicks as $kick)
                                {
                                    echo '<tr><td>Kick '.$i.'</td><td>'.$kick.'m</td><td></td><td></td></tr>';
                                    $i++;
                                }
                            }
                        ?>
                    </table>
                    <?php
                    }
                    else
                    if($datarecord->Events['event_judging_type'] == 'basketball')
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
                        <td><?php echo $datarecord->basketball_attempt_1 ?></td>
                        <td><?php echo $datarecord->basketball_attempt_2 ?></td>
                        <td><?php echo $datarecord->basketball_attempt_3 ?></td>
                        <td><b><?php echo $datarecord->basketball_score ?></b></td>
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
