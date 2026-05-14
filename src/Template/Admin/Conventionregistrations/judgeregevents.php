<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>
<?php
use Cake\ORM\TableRegistry;
$EventsTbl = TableRegistry::getTableLocator()->get('Events');
$ConventionseaseventsTbl = TableRegistry::getTableLocator()->get('Conventionseasonevents');

$alreadyChooseEvents = !empty($CRDetails->judges_event_ids) ? explode(',', $CRDetails->judges_event_ids) : [];

// Resolve full event records for the convention/season for richer cards
$convSeasonEventIDs = [0];
if (!empty($CRDetails->conventionseason_id)) {
    $cse = $ConventionseaseventsTbl->find()->where(['Conventionseasonevents.conventionseasons_id' => $CRDetails->conventionseason_id])->all();
    foreach ($cse as $r) { $convSeasonEventIDs[] = $r->event_id; }
}
$inList = implode(',', array_map('intval', $convSeasonEventIDs));
$availableEvents = $EventsTbl->find()->where(["(Events.id IN ($inList))"])->order(['Events.event_id_number' => 'ASC'])->all();

$totalAvail = 0; $totalSelected = count($alreadyChooseEvents);
foreach ($availableEvents as $e) { $totalAvail++; }
?>
<style>
.je-stat-row { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin:0 0 18px; }
.je-stat { background:#fff; border:1px solid #e3e6ec; border-radius:8px; padding:14px 16px; display:flex; align-items:center; gap:12px; }
.je-stat .ico { width:42px; height:42px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:18px; color:#fff; }
.je-stat .ico.b1 { background:#4a90e2; }
.je-stat .ico.b2 { background:#1c7a45; }
.je-stat .ico.b3 { background:#a37a00; }
.je-stat .lbl { font-size:11px; color:#7b8794; text-transform:uppercase; letter-spacing:.5px; }
.je-stat .val { font-size:22px; font-weight:700; color:#2c3e50; line-height:1; }

.je-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; padding:12px 14px; background:#f6f8fb; border:1px solid #e3e6ec; border-bottom:0; border-radius:8px 8px 0 0; }
.je-toolbar .je-search { flex:1 1 240px; position:relative; }
.je-toolbar .je-search input { width:100%; padding:7px 12px 7px 32px; border:1px solid #d3d8e0; border-radius:4px; font-size:13px; }
.je-toolbar .je-search i { position:absolute; left:11px; top:9px; color:#9aa5b1; }
.je-toolbar .btn { font-size:12px; }

.je-grid { background:#fff; border:1px solid #e3e6ec; border-top:0; border-radius:0 0 8px 8px; padding:14px; max-height:520px; overflow-y:auto; }
.je-grid-inner { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:10px; }
.je-event { display:flex; align-items:flex-start; gap:10px; padding:10px 12px; border:1px solid #e3e6ec; border-radius:6px; cursor:pointer; transition:all .12s ease; background:#fff; }
.je-event:hover { border-color:#4a90e2; background:#f9fbfe; }
.je-event input[type=checkbox] { margin-top:3px; transform:scale(1.15); cursor:pointer; }
.je-event .je-num { display:inline-block; padding:2px 8px; background:#eef1f5; color:#5b6770; border-radius:10px; font-size:11px; font-weight:600; margin-right:6px; }
.je-event .je-name { font-size:13px; color:#2c3e50; }
.je-event.is-selected { border-color:#1c7a45; background:#f0f9f4; }
.je-event.is-selected .je-num { background:#1c7a45; color:#fff; }
.je-no-results { padding:30px; text-align:center; color:#7b8794; font-style:italic; }

.je-actions { background:#fff; border:1px solid #e3e6ec; border-radius:8px; padding:14px 16px; margin-top:18px; display:flex; flex-wrap:wrap; align-items:center; gap:14px; justify-content:space-between; }
.je-actions .je-email { display:flex; align-items:center; gap:8px; font-size:13px; color:#2c3e50; cursor:pointer; }
.je-actions .je-email input { transform:scale(1.2); }
.je-actions .je-btns .btn { margin-left:6px; }
.je-count-badge { display:inline-block; min-width:24px; padding:2px 8px; background:#1c7a45; color:#fff; border-radius:10px; font-size:11px; font-weight:700; text-align:center; margin-left:6px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
      <h1>Judge Events</h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> Convention Registrations ', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Judge Events</li>
      </ol>
    </section>

    <section class="content">
        <?php echo $this->element('Admin/Conventionregistrations/registrant_header'); ?>

        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-gavel"></i> Assign Events for this Judge</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>

            <?php echo $this->Form->create($CRDetails, ['id'=>'adminForm', 'type' => 'file']); ?>
            <div class="box-body">

                <div class="je-stat-row">
                    <div class="je-stat">
                        <div class="ico b1"><i class="fa fa-list"></i></div>
                        <div>
                            <div class="lbl">Available Events</div>
                            <div class="val"><?php echo $totalAvail; ?></div>
                        </div>
                    </div>
                    <div class="je-stat">
                        <div class="ico b2"><i class="fa fa-check"></i></div>
                        <div>
                            <div class="lbl">Currently Assigned</div>
                            <div class="val" id="je_selected_count"><?php echo $totalSelected; ?></div>
                        </div>
                    </div>
                    <div class="je-stat">
                        <div class="ico b3"><i class="fa fa-calendar"></i></div>
                        <div>
                            <div class="lbl">Season</div>
                            <div class="val" style="font-size:18px;"><?php echo h($CRDetails->season_year); ?></div>
                        </div>
                    </div>
                </div>

                <div class="je-toolbar">
                    <div class="je-search">
                        <i class="fa fa-search"></i>
                        <input type="text" id="je_filter" placeholder="Search events by name or number...">
                    </div>
                    <button type="button" class="btn btn-default btn-sm" id="je_select_all"><i class="fa fa-check-square-o"></i> Select All</button>
                    <button type="button" class="btn btn-default btn-sm" id="je_select_visible"><i class="fa fa-filter"></i> Select Filtered</button>
                    <button type="button" class="btn btn-default btn-sm" id="je_clear"><i class="fa fa-times"></i> Clear</button>
                </div>

                <div class="je-grid">
                    <div class="je-grid-inner" id="je_grid">
                        <?php if ($totalAvail === 0) { ?>
                            <div class="je-no-results">No events have been added to this convention/season yet.</div>
                        <?php } else { ?>
                            <?php foreach ($availableEvents as $eventD) {
                                $checked = in_array($eventD->id, $alreadyChooseEvents) ? 'checked' : '';
                                $selClass = $checked ? 'is-selected' : '';
                                $searchTxt = strtolower($eventD->event_name . ' ' . $eventD->event_id_number);
                            ?>
                                <label class="je-event <?php echo $selClass; ?>" data-search="<?php echo h($searchTxt); ?>">
                                    <input type="checkbox" name="Conventionregistrations[judges_event_ids][]" value="<?php echo (int)$eventD->id; ?>" class="je-cb" <?php echo $checked; ?>>
                                    <div>
                                        <span class="je-num"><?php echo h($eventD->event_id_number); ?></span>
                                        <span class="je-name"><?php echo h($eventD->event_name); ?></span>
                                    </div>
                                </label>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>

                <div class="je-actions">
                    <label class="je-email">
                        <input type="checkbox" name="send_email_notification" id="send_email_notification" value="1">
                        <i class="fa fa-envelope-o"></i> Send email notification to judge
                    </label>
                    <div class="je-btns">
                        <?php echo $this->Html->link('Cancel', ['controller'=>'conventionregistrations', 'action' => 'index'], ['class'=>'btn btn-default']); ?>
                        <?php echo $this->Form->button('<i class="fa fa-save"></i> Save Assignments', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false, 'escapeTitle' => false]); ?>
                    </div>
                </div>
            </div>
            <?php echo $this->Form->end(); ?>
        </div>
    </section>
</div>

<script>
$(function () {
    var $grid    = $('#je_grid');
    var $events  = $grid.find('.je-event');
    var $count   = $('#je_selected_count');

    function refreshCount () {
        $count.text($grid.find('.je-cb:checked').length);
    }

    $grid.on('change', '.je-cb', function () {
        $(this).closest('.je-event').toggleClass('is-selected', this.checked);
        refreshCount();
    });

    $('#je_filter').on('input', function () {
        var q = $(this).val().toLowerCase().trim();
        $events.each(function () {
            var hit = q === '' || $(this).attr('data-search').indexOf(q) !== -1;
            $(this).toggle(hit);
        });
    });

    $('#je_select_all').on('click', function () {
        $events.find('.je-cb').prop('checked', true);
        $events.addClass('is-selected');
        refreshCount();
    });

    $('#je_select_visible').on('click', function () {
        $events.filter(':visible').find('.je-cb').prop('checked', true);
        $events.filter(':visible').addClass('is-selected');
        refreshCount();
    });

    $('#je_clear').on('click', function () {
        $events.find('.je-cb').prop('checked', false);
        $events.removeClass('is-selected');
        refreshCount();
    });
});
</script>
