<style>
.cs-empty { padding:40px; text-align:center; color:#7b8794; background:#f9fafc; border:1px dashed #d3d8e0; border-radius:8px; }
.cs-empty i { display:block; font-size:36px; margin-bottom:10px; color:#b8c2cc; }

.cs-paging { display:flex; justify-content:space-between; align-items:center; padding:8px 4px 14px; }
.cs-paging .cs-count { font-weight:600; color:#2c3e50; }

.cs-card { background:#fff; border:1px solid #e3e6ec; border-radius:8px; margin-bottom:18px; overflow:hidden; box-shadow:0 1px 2px rgba(0,0,0,0.03); }
.cs-card-head { display:flex; flex-wrap:wrap; align-items:center; gap:12px; padding:14px 18px; background:linear-gradient(180deg, #fbfcfe 0%, #f3f6fa 100%); border-bottom:1px solid #e3e6ec; }
.cs-card-head .cs-year { font-size:22px; font-weight:700; color:#1c2452; }
.cs-card-head .cs-id { font-size:11px; color:#9aa5b1; }
.cs-card-head .cs-spacer { flex:1; }
.cs-badge { display:inline-flex; align-items:center; gap:5px; padding:4px 10px; border-radius:12px; font-size:11px; font-weight:600; }
.cs-badge.b-open    { background:#e6f6ed; color:#1c7a45; }
.cs-badge.b-locked  { background:#fff0e6; color:#a04500; }
.cs-badge.b-released{ background:#e8f0ff; color:#2451a3; }
.cs-badge.b-hidden  { background:#f0f2f5; color:#5b6770; }

.cs-meta { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px 18px; padding:14px 18px; border-bottom:1px solid #eef1f5; background:#fff; }
.cs-meta .m { display:flex; flex-direction:column; gap:2px; }
.cs-meta .m .lbl { font-size:11px; color:#7b8794; text-transform:uppercase; letter-spacing:.4px; }
.cs-meta .m .val { font-size:14px; color:#2c3e50; font-weight:600; }
.cs-meta .m .val.fee { color:#1c7a45; }

.cs-actions { padding:14px 18px; }
.cs-actions .grp { display:flex; flex-wrap:wrap; align-items:center; gap:8px; padding:8px 0; border-top:1px dashed #eef1f5; }
.cs-actions .grp:first-child { border-top:0; padding-top:0; }
.cs-actions .grp-label { font-size:11px; font-weight:700; color:#7b8794; text-transform:uppercase; letter-spacing:.5px; min-width:90px; }
.cs-actions .btn-act { display:inline-flex; align-items:center; gap:6px; padding:5px 10px; font-size:12px; border-radius:4px; border:1px solid transparent; text-decoration:none; transition:all .12s ease; }
.cs-actions .btn-act i { font-size:12px; }
.cs-actions .btn-act:hover { transform:translateY(-1px); box-shadow:0 2px 4px rgba(0,0,0,0.08); }
.cs-actions .a-info    { background:#e8f0ff; color:#2451a3; }
.cs-actions .a-warn    { background:#fff3d6; color:#8a6100; }
.cs-actions .a-success { background:#e6f6ed; color:#1c7a45; }
.cs-actions .a-primary { background:#ede4ff; color:#5024a3; }
.cs-actions .a-danger  { background:#ffe1e1; color:#a52a2a; margin-left:auto; }
</style>

<div class="admin_loader" id="loaderID"><?php echo $this->Html->image('loader_large_blue.gif');?></div>
<?php if (!$convseasons->isEmpty()) { ?>
    <div class="panel-body">
        <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

        <div class="cs-paging">
            <div class="cs-count"><i class="fa fa-calendar"></i> Seasons</div>
            <div class="ajshort" id="pagingLinks">
                <?php
                    $this->Paginator->options(['update' => '#listID', 'url' => ['controller'=>'conventions', 'action'=>'seasons', $slug, $separator]]);
                    echo $this->Paginator->counter('{{page}} of {{pages}} &nbsp;');
                    echo $this->Paginator->prev('« Prev');
                    echo $this->Paginator->numbers();
                    echo $this->Paginator->next('Next »');
                ?>
            </div>
        </div>

        <?php foreach ($convseasons as $datarecord) {
            $subOpen   = ($datarecord->submissions_open ?? 0) == 1;
            $resReleased = ($datarecord->results_release ?? 0) == 1;
        ?>
        <div class="cs-card">
            <div class="cs-card-head">
                <div>
                    <div class="cs-year"><?php echo h($datarecord->season_year); ?></div>
                    <div class="cs-id">DB ID #<?php echo (int)$datarecord->id; ?></div>
                </div>
                <div class="cs-spacer"></div>
                <?php if ($subOpen) { ?>
                    <span class="cs-badge b-open"><i class="fa fa-unlock"></i> Submissions Open</span>
                <?php } else { ?>
                    <span class="cs-badge b-locked"><i class="fa fa-lock"></i> Submissions Locked</span>
                <?php } ?>
                <?php if ($resReleased) { ?>
                    <span class="cs-badge b-released"><i class="fa fa-eye"></i> Results Released</span>
                <?php } else { ?>
                    <span class="cs-badge b-hidden"><i class="fa fa-eye-slash"></i> Results Hidden</span>
                <?php } ?>
            </div>

            <div class="cs-meta">
                <div class="m">
                    <span class="lbl">Reg. Start</span>
                    <span class="val"><?php echo date('M d, Y', strtotime($datarecord->registration_start_date)); ?></span>
                </div>
                <div class="m">
                    <span class="lbl">Reg. End</span>
                    <span class="val"><?php echo date('M d, Y', strtotime($datarecord->registration_end_date)); ?></span>
                </div>
                <div class="m">
                    <span class="lbl">Student (<?php echo CURR; ?>)</span>
                    <span class="val fee"><?php echo number_format($datarecord->student_registration_fees, 2); ?></span>
                </div>
                <div class="m">
                    <span class="lbl">Non-competitor</span>
                    <span class="val fee"><?php echo number_format($datarecord->non_competitor_registration_fees, 2); ?></span>
                </div>
                <div class="m">
                    <span class="lbl">Non-affiliate</span>
                    <span class="val fee"><?php echo number_format($datarecord->non_affiliate_registration_fees, 2); ?></span>
                </div>
                <div class="m">
                    <span class="lbl">Supervisor</span>
                    <span class="val fee"><?php echo number_format($datarecord->supervisor_registration_fees, 2); ?></span>
                </div>
            </div>

            <div class="cs-actions">
                <div class="grp">
                    <span class="grp-label">Setup</span>
                    <?php echo $this->Html->link('<i class="fa fa-pencil"></i> Prices', ['controller'=>'conventions','action'=>'changeprices',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-info','title'=>'Change Prices']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-puzzle-piece"></i> Events', ['controller'=>'conventions','action'=>'events',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-info','title'=>'Manage Events']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-registered"></i> Room Events', ['controller'=>'conventions','action'=>'roomevents',$datarecord->slug], ['escape'=>false,'class'=>'btn-act a-primary','title'=>'Room Events']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-clock-o"></i> Scheduling', ['controller'=>'schedulings','action'=>'precheck',$datarecord->slug], ['escape'=>false,'class'=>'btn-act a-primary','title'=>'Scheduling Pre-check']); ?>
                </div>

                <div class="grp">
                    <span class="grp-label">Results</span>
                    <?php echo $this->Html->link('<i class="fa fa-circle"></i> Division Pts', ['controller'=>'results','action'=>'points',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-warn','title'=>'Division Points']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-bullseye"></i> Overall Pts', ['controller'=>'results','action'=>'overallpoints',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-warn','title'=>'Overall Points']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-trophy"></i> Division Winners', ['controller'=>'results','action'=>'divisionwinners',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-warn','title'=>'Division Winners']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-mortar-board"></i> Results List', ['controller'=>'results','action'=>'overallpositions',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-info','title'=>'Results List']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-star"></i> Scripture Award', ['controller'=>'conventions','action'=>'scriptureawardslist',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-warn','title'=>'Scripture Award']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-heart"></i> Heart Events', ['controller'=>'heartevents','action'=>'listheartevents',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-primary','title'=>'Events of the Heart Students']); ?>
                </div>

                <div class="grp">
                    <span class="grp-label">Operations</span>
                    <?php echo $this->Html->link('<i class="fa fa-user-secret"></i> Judges', ['controller'=>'conventions','action'=>'judges',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-info','title'=>'Judges List']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-gavel"></i> Broken Records', ['controller'=>'conventions','action'=>'brokenrecordcertificate',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-primary','title'=>'Broken Record Certificate']); ?>
                    <?php echo $this->Html->link('<i class="fa fa-certificate"></i> Certificates', ['controller'=>'conventions','action'=>'certificates',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-primary','title'=>'Certificates']); ?>
                    <?php if (!$resReleased) { ?>
                        <?php echo $this->Html->link('<i class="fa fa-eye"></i> Release Results', ['controller'=>'conventions','action'=>'seasonresultrelease',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-success','title'=>'Release Result','confirm'=>'Are you sure you want to release results?']); ?>
                    <?php } else { ?>
                        <?php echo $this->Html->link('<i class="fa fa-eye-slash"></i> Hide Results', ['controller'=>'conventions','action'=>'seasonresultreleasestop',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-warn','title'=>'Stop Release Result','confirm'=>'Are you sure you want to stop release results?']); ?>
                    <?php } ?>
                    <?php if ($subOpen) { ?>
                        <?php echo $this->Html->link('<i class="fa fa-lock"></i> Lock Submissions', ['controller'=>'conventions','action'=>'locksubmissions',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-warn','title'=>'Lock Submissions (currently open)','confirm'=>'Are you sure you want to lock submissions? Users will not be able to submit or upload.']); ?>
                    <?php } else { ?>
                        <?php echo $this->Html->link('<i class="fa fa-unlock"></i> Unlock Submissions', ['controller'=>'conventions','action'=>'unlocksubmissions',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-success','title'=>'Unlock Submissions (currently locked)','confirm'=>'Are you sure you want to unlock submissions?']); ?>
                    <?php } ?>
                    <?php echo $this->Html->link('<i class="fa fa-trash-o"></i> Delete', ['controller'=>'conventions','action'=>'deleteconventionsseason',$datarecord->slug,$slug], ['escape'=>false,'class'=>'btn-act a-danger','title'=>'Delete Season','confirm'=>'Are you sure you want to delete this season?']); ?>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
<?php } else { ?>
    <div id="listingJS" style="display: none;" class="alert alert-success alert-block fade in"></div>
    <div class="cs-empty"><i class="fa fa-calendar-o"></i>No seasons found for this convention.</div>
<?php } ?>
