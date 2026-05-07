<?php
/* Scheduling Tweaks – index.php
   Tweaks: A = pinned day, B = pinned room, C = pinned start time */
?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>
            Scheduling Tweaks – [<?php echo h($conventionSD->Conventions['name']); ?>]
            &nbsp; [Season Year: <?php echo h($conventionSD->season_year); ?>]
        </h1>
        <ol class="breadcrumb">
            <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard',
                ['controller'=>'admins','action'=>'dashboard'], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Conventions',
                ['controller'=>'conventions','action'=>'index'], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Seasons',
                ['controller'=>'conventions','action'=>'seasons',$convention_slug], ['escape'=>false]); ?></li>
            <li><?php echo $this->Html->link('Pre-check',
                ['controller'=>'schedulings','action'=>'precheck',$convention_season_slug], ['escape'=>false]); ?></li>
            <li class="active">Scheduling Tweaks</li>
        </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Event Tweaks</h3>
                <div class="box-tools pull-right">
                    <?php echo $this->Html->link(
                        '<i class="fa fa-clock-o"></i> Room Availability Windows',
                        ['controller'=>'schedulingtweaks','action'=>'roomavailability',$convention_season_slug],
                        ['class'=>'btn btn-sm btn-default', 'escape'=>false]
                    ); ?>
                </div>
            </div>
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div class="box-body">
                <p class="text-muted">
                    Use these controls to restrict or pin events before running scheduling.
                    Changes here take effect the <strong>next time</strong> scheduling is generated.
                </p>
                <p>
                    <strong>A – Pinned Day:</strong> Schedule this event only on the chosen day.<br>
                    <strong>B – Pinned Room:</strong> Always assign this event to the chosen room.<br>
                    <strong>C – Pinned Start Time:</strong> Force this event's block to begin at a set time.
                </p>

                <?php if (empty($eventsForTweaks)): ?>
                    <div class="alert alert-warning">No schedulable events found for this convention season.</div>
                <?php else: ?>

                    <!-- Build allowed convention days from wizard config -->
                    <?php
                    $allowedDays = [];
                    if ($schedulingD && $schedulingD->first_day && $schedulingD->number_of_days > 0) {
                        $weekArr  = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];
                        $keyStart = array_search($schedulingD->first_day, $weekArr);
                        if ($keyStart !== false) {
                            for ($d = 0; $d < $schedulingD->number_of_days; $d++) {
                                $allowedDays[] = $weekArr[($keyStart + $d) % count($weekArr)];
                            }
                        }
                    }
                    ?>

                    <table class="table table-bordered table-hover table-striped" style="font-size:13px;">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th style="width:30%">Event</th>
                                <th style="width:15%">A – Pinned Day</th>
                                <th style="width:20%">B – Pinned Room</th>
                                <th style="width:15%">C – Start Time</th>
                                <th style="width:10%">Active Tweaks</th>
                                <th style="width:5%">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php $idx = 1; foreach ($eventsForTweaks as $ev): ?>
                            <?php $tw = $tweaksMap[$ev->id] ?? null; ?>
                            <tr>
                                <td><?php echo $idx++; ?></td>
                                <td>
                                    <strong><?php echo h($ev->event_name); ?></strong><br>
                                    <small class="text-muted"><?php echo h($ev->event_id_number); ?>
                                    &nbsp;|&nbsp; <?php echo h($ev->event_kind_id); ?></small>
                                </td>
                                <td colspan="5">
                                    <!-- Inline form for this event -->
                                    <?php echo $this->Form->create(null, [
                                        'url' => ['controller'=>'schedulingtweaks','action'=>'save',$convention_season_slug],
                                        'method' => 'post',
                                    ]); ?>
                                    <?php echo $this->Form->hidden('event_id', ['value' => $ev->id]); ?>
                                    <div class="row">
                                        <!-- A: Pinned Day -->
                                        <div class="col-xs-3">
                                            <?php echo $this->Form->select('pinned_day',
                                                array_merge(['' => '-- Any day --'], array_combine($allowedDays, $allowedDays)),
                                                [
                                                    'class'   => 'form-control input-sm',
                                                    'value'   => $tw->pinned_day ?? '',
                                                    'empty'   => false,
                                                ]
                                            ); ?>
                                        </div>
                                        <!-- B: Pinned Room -->
                                        <div class="col-xs-4">
                                            <?php
                                            $roomOptions = ['' => '-- Auto assign --'];
                                            foreach ($rooms as $rm) {
                                                $roomOptions[$rm->id] = h($rm->room_name);
                                            }
                                            echo $this->Form->select('pinned_room_id',
                                                $roomOptions,
                                                [
                                                    'class' => 'form-control input-sm',
                                                    'value' => $tw->pinned_room_id ?? '',
                                                    'empty' => false,
                                                ]
                                            ); ?>
                                        </div>
                                        <!-- C: Pinned Start Time -->
                                        <div class="col-xs-3">
                                            <?php echo $this->Form->text('pinned_start_time', [
                                                'class'       => 'form-control input-sm mdtpicker',
                                                'placeholder' => 'hh:mm (24hr)',
                                                'value'       => ($tw && $tw->pinned_start_time)
                                                    ? date('H:i', strtotime($tw->pinned_start_time)) : '',
                                            ]); ?>
                                        </div>
                                        <!-- Save button -->
                                        <div class="col-xs-2">
                                            <?php echo $this->Form->button('Save', [
                                                'class' => 'btn btn-sm btn-primary'
                                            ]); ?>
                                        </div>
                                    </div>
                                    <?php echo $this->Form->end(); ?>
                                </td>
                            </tr>
                            <!-- Summary row: show what tweaks are active -->
                            <tr>
                                <td colspan="2"></td>
                                <td>
                                    <?php echo ($tw && $tw->pinned_day)
                                        ? '<span class="label label-info">' . h($tw->pinned_day) . '</span>'
                                        : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td>
                                    <?php
                                    if ($tw && $tw->pinned_room_id) {
                                        $pinnedRoomName = '?';
                                        foreach ($rooms as $rm) {
                                            if ($rm->id == $tw->pinned_room_id) {
                                                $pinnedRoomName = $rm->room_name;
                                                break;
                                            }
                                        }
                                        echo '<span class="label label-info">' . h($pinnedRoomName) . '</span>';
                                    } else {
                                        echo '<span class="text-muted">—</span>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php echo ($tw && $tw->pinned_start_time)
                                        ? '<span class="label label-info">' . date('H:i', strtotime($tw->pinned_start_time)) . '</span>'
                                        : '<span class="text-muted">—</span>'; ?>
                                </td>
                                <td>
                                    <?php
                                    $hasAny = $tw && ($tw->pinned_day || $tw->pinned_room_id || $tw->pinned_start_time);
                                    echo $hasAny
                                        ? '<span class="label label-warning">Active</span>'
                                        : '<span class="text-muted">None</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($tw): ?>
                                        <?php echo $this->Html->link(
                                            '<i class="fa fa-times"></i> Clear',
                                            ['controller'=>'schedulingtweaks','action'=>'clear',$convention_season_slug,$ev->id],
                                            [
                                                'class'   => 'btn btn-xs btn-danger',
                                                'escape'  => false,
                                                'confirm' => 'Clear all tweaks for ' . $ev->event_name . '?',
                                            ]
                                        ); ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div><!-- /.box-body -->

            <div class="box-footer">
                <?php echo $this->Html->link(
                    '<i class="fa fa-arrow-left"></i> Back to Pre-check',
                    ['controller'=>'schedulings','action'=>'precheck',$convention_season_slug],
                    ['class'=>'btn btn-default', 'escape'=>false]
                ); ?>
            </div>
        </div>
    </section>
</div>
