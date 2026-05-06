<?php $ev = !empty($cse->Events) ? $cse->Events : null; ?>
<style>
@page {
    margin: 12mm 15mm 12mm 15mm;
}
body { font-family: Arial, sans-serif; color: #000; margin: 0; padding: 0; }
.page-wrap { max-width: 900px; margin: 14px auto; padding: 0 10px; }
.print-head { border-bottom: 2px solid #000; margin-bottom: 10px; padding-bottom: 8px; }
.print-head h2 { margin: 0 0 6px; font-size: 22px; }
.meta-row { display: flex; gap: 24px; flex-wrap: wrap; font-size: 13px; }
.running-table { width: 100%; border-collapse: collapse; margin-top: 12px; }
.running-table th, .running-table td { border: 1px solid #000; padding: 5px 6px; font-size: 12px; }
.running-table th { text-align: left; background: #f3f3f3; }
.blank-box { display: inline-block; width: 24px; height: 18px; border: 1px solid #000; }
.toolbar { margin-bottom: 10px; }
.event-block { margin-bottom: 18px; }
@media print { .toolbar { display: none; } .page-wrap { margin: 0; padding: 0; max-width: 100%; } }
</style>

<div class="toolbar">
    <button onclick="window.print();" class="btn btn-primary btn-sm"><i class="fa fa-print"></i> Print</button>
    &nbsp;
    <a href="javascript:window.close();" class="btn btn-default btn-sm">Close</a>
</div>
<div class="page-wrap">
<?php if (!empty($heats)): ?>
    <?php foreach ($heats as $heatIndex => $heatRows): ?>
        <?php
        $heatNumber  = $heatIndex + 1;
        $formatLabel = $isHeated ? "Heat {$heatNumber} of {$totalHeats}" : 'FINAL';
        $heatEntries = count($heatRows);
        $needsPageBreak = $heatIndex > 0;
        ?>
        <div class="event-block"<?php echo $needsPageBreak ? ' style="page-break-before: always;"' : ''; ?>>
            <div class="print-head">
                <h2><?php echo h($ev ? $ev->event_name : '—'); ?> (<?php echo h($ev ? $ev->event_id_number : '—'); ?>)</h2>
                <div class="meta-row">
                    <div><strong>Qualifying Time:</strong> <?php echo h($qualifyingTime); ?></div>
                    <div><strong>No. of Entries:</strong> <?php echo h($entriesCount); ?></div>
                    <div><strong>Format:</strong> <?php echo h($formatLabel); ?></div>
                </div>
            </div>
            <table class="running-table">
                <thead>
                    <tr>
                        <th style="width:70px;">Lane</th>
                        <th>Name</th>
                        <th style="width:110px;">Year of Birth</th>
                        <th>School</th>
                        <th style="width:100px;">Time</th>
                        <th style="width:100px;">Place</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($heatEntries > 0): ?>
                        <?php foreach ($heatRows as $submission): ?>
                            <?php
                            $studentName = 'N/A';
                            $birthYear   = '';
                            $schoolName  = 'N/A';

                            if (!empty($submission->student_id) && !empty($submission->Students)) {
                                $studentName = trim(
                                    (isset($submission->Students['first_name']) ? $submission->Students['first_name'] : '') . ' ' .
                                    (isset($submission->Students['middle_name']) ? $submission->Students['middle_name'] : '') . ' ' .
                                    (isset($submission->Students['last_name']) ? $submission->Students['last_name'] : '')
                                );
                                $birthYear = isset($submission->Students['birth_year']) ? (string)$submission->Students['birth_year'] : '';
                            }

                            if (!empty($submission->Users)) {
                                $schoolName = isset($submission->Users['first_name']) ? $submission->Users['first_name'] : 'N/A';
                            }

                            $isGroupEvent = !empty($submission->event_id) && !empty($ev) &&
                                           (int)($ev->group_event_yes_no ?? 0) === 1;

                            if ($studentName === 'N/A') {
                                if (!empty($submission->group_name)) {
                                    $studentName = is_numeric(trim((string)$submission->group_name)) && $schoolName !== 'N/A'
                                        ? $schoolName . ' Team'
                                        : $submission->group_name;
                                } elseif ($schoolName !== 'N/A') {
                                    $studentName = $schoolName . ' Team';
                                }
                            }
                            ?>
                            <tr>
                                <td><span class="blank-box"></span></td>
                                <td><?php echo h($studentName); ?></td>
                                <td><?php echo h($birthYear); ?></td>
                                <td><?php echo h($schoolName); ?></td>
                                <td></td>
                                <td></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No entries found for this event.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p>No entries found for this event.</p>
<?php endif; ?>
</div>

<script>
window.onload = function () { window.print(); };
</script>
