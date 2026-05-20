<?php
$eventName = isset($eventD) && $eventD ? (string)$eventD->event_name : 'Event';
$programTitle = isset($conventionSD) ? trim((string)($conventionSD->Conventions['name'] ?? '')) : '';
$schedCat = isset($schedCat) ? (int)$schedCat : 0;
$bracketData = isset($bracketData) && is_array($bracketData) ? $bracketData : [];
$teamNameMap = isset($teamNameMap) && is_array($teamNameMap) ? $teamNameMap : [];

// Round label helper
function getSportRoundLabel($round, $totalRounds) {
    if ($round === $totalRounds) {
        return 'Final';
    }
    if ($round === $totalRounds - 1 && $totalRounds > 2) {
        return 'Semi Final';
    }
    if ($round === $totalRounds - 2 && $totalRounds > 3) {
        return 'Quarter Final';
    }
    return 'Round ' . $round;
}

function getTeamDisplay($rawName, $teamNameMap, $schedCat) {
    $raw = trim((string)$rawName);
    if ($raw === '') return '<span class="sd-tbd">TBD</span>';
    if ($schedCat === 3) {
        $label = isset($teamNameMap[$raw]) ? h($teamNameMap[$raw]) : h('Team ' . $raw);
        return $label;
    }
    // cat 2: raw is user_id
    $label = isset($teamNameMap[$raw]) ? h($teamNameMap[$raw]) : h('Player ' . $raw);
    return $label;
}

$totalRounds = count($bracketData);
$logoPath = defined('WWW_ROOT') ? WWW_ROOT . 'img/front/accelerate-logo.jpg' : '';
$logoSrc = '';
if ($logoPath !== '' && is_file($logoPath)) {
    $logoData = @file_get_contents($logoPath);
    if ($logoData !== false) {
        $logoSrc = 'data:image/jpeg;base64,' . base64_encode($logoData);
    }
}
?>
<style>
.sd-wrap {
    font-family: 'Segoe UI', Arial, sans-serif;
    color: #1a1a1a;
    background: #f0f0f0;
    padding: 20px;
}
.sd-sheet {
    max-width: 960px;
    margin: 0 auto;
    background: #fff;
    box-shadow: 0 6px 24px rgba(0,0,0,0.14);
    border-radius: 4px;
    overflow: hidden;
}
.sd-header {
    background: #1e3a6e;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 18px 28px 14px;
}
.sd-logo {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.5);
    background: #fff;
    object-fit: cover;
    padding: 0;
    flex-shrink: 0;
}
.sd-header-text .sd-org {
    font-size: 11px;
    letter-spacing: 0.18em;
    text-transform: uppercase;
    opacity: 0.8;
}
.sd-header-text .sd-event {
    font-size: 20px;
    font-weight: 700;
    line-height: 1.15;
    margin-top: 2px;
}
.sd-header-text .sd-convention {
    font-size: 13px;
    opacity: 0.85;
    margin-top: 3px;
}
.sd-body {
    padding: 20px 24px 28px;
}
.sd-round {
    margin-bottom: 20px;
    break-inside: avoid;
    page-break-inside: avoid;
}
.sd-round-heading {
    background: #3758a6;
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 3px;
    margin-bottom: 8px;
}
.sd-round-heading.sd-final {
    background: #8b1a1a;
}
.sd-round-heading.sd-semi {
    background: #5a3a8a;
}
.sd-round-heading.sd-quarter {
    background: #1e6e4e;
}
.sd-bracket-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.sd-bracket-table thead th {
    background: #eef2f9;
    border: 1px solid #c8d0dc;
    padding: 5px 10px;
    text-align: left;
    font-weight: 700;
    font-size: 12px;
    color: #1e3a6e;
}
.sd-bracket-table tbody tr {
    border-bottom: 1px solid #e8e8e8;
}
.sd-bracket-table tbody tr:last-child {
    border-bottom: none;
}
.sd-bracket-table tbody tr.sd-bye {
    background: #f7f7f7;
    color: #999;
    font-style: italic;
}
.sd-bracket-table tbody tr.sd-future {
    background: #fafafa;
    color: #aaa;
}
.sd-bracket-table tbody td {
    padding: 6px 10px;
    border: 1px solid #e8e8e8;
    vertical-align: middle;
}
.sd-match-num {
    width: 60px;
    text-align: center;
    font-weight: 700;
    color: #555;
}
.sd-team-a {
    width: 30%;
    font-weight: 600;
}
.sd-vs {
    width: 36px;
    text-align: center;
    color: #999;
    font-size: 11px;
    font-style: italic;
}
.sd-team-b {
    width: 30%;
    font-weight: 600;
}
.sd-day { width: 90px; color: #555; }
.sd-time { width: 90px; color: #555; }
.sd-location { color: #555; }
.sd-badge-bye {
    background: #e0e0e0;
    color: #888;
    border-radius: 10px;
    font-size: 10px;
    padding: 1px 7px;
    margin-left: 6px;
    font-style: normal;
    font-weight: 700;
}
.sd-tbd {
    color: #bbb;
    font-style: italic;
}
.sd-footer {
    margin-top: 16px;
    text-align: center;
    font-size: 10px;
    color: #aaa;
    letter-spacing: 0.06em;
}
.sd-empty {
    padding: 24px;
    color: #888;
    font-size: 14px;
}
@media print {
    .sd-wrap { background: #fff; padding: 0; }
    .sd-sheet { box-shadow: none; border-radius: 0; max-width: 100%; }
    .sd-round { break-inside: avoid; page-break-inside: avoid; }
}
</style>

<div class="sd-wrap">
    <div class="sd-sheet">
        <div class="sd-header">
            <?php if ($logoSrc !== '') { ?>
                <img src="<?php echo $logoSrc; ?>" alt="Logo" class="sd-logo" />
            <?php } ?>
            <div class="sd-header-text">
                <div class="sd-org">A.C.E. Student Convention</div>
                <div class="sd-event"><?php echo h($eventName); ?></div>
                <div class="sd-convention"><?php echo h($programTitle); ?></div>
            </div>
        </div>

        <div class="sd-body">
            <?php if (empty($bracketData)) { ?>
                <div class="sd-empty">No draw data found for this event. Run the scheduling engine first.</div>
            <?php } else { ?>
                <?php foreach ($bracketData as $roundNum => $matches) { ?>
                    <?php
                    $roundLabel = getSportRoundLabel($roundNum, $totalRounds);
                    $headingClass = 'sd-round-heading';
                    if ($roundLabel === 'Final') $headingClass .= ' sd-final';
                    elseif ($roundLabel === 'Semi Final') $headingClass .= ' sd-semi';
                    elseif ($roundLabel === 'Quarter Final') $headingClass .= ' sd-quarter';
                    ?>
                    <div class="sd-round">
                        <div class="<?php echo $headingClass; ?>"><?php echo h($roundLabel); ?></div>
                        <table class="sd-bracket-table">
                            <thead>
                                <tr>
                                    <th class="sd-match-num">#</th>
                                    <th class="sd-team-a"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
                                    <th class="sd-vs">vs</th>
                                    <th class="sd-team-b"><?php echo $schedCat === 3 ? 'Team / School' : 'Player'; ?></th>
                                    <th class="sd-day">Day</th>
                                    <th class="sd-time">Time</th>
                                    <th class="sd-location">Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($matches as $row) { ?>
                                    <?php
                                    $isBye = (int)$row->is_bye === 1;
                                    $teamAraw = $schedCat === 3 ? (string)$row->group_name : (string)$row->user_id;
                                    $teamBraw = $schedCat === 3 ? (string)$row->group_name_opponent : (string)$row->user_id_opponent;
                                    $isFuture = trim($teamAraw) === '' && trim($teamBraw) === '';
                                    $rowClass = $isBye ? ' sd-bye' : ($isFuture ? ' sd-future' : '');
                                    $startTime = !empty($row->start_time) ? safe_date('g:i a', strtotime((string)$row->start_time)) : '';
                                    $roomName = isset($row->conventionroom) ? (string)$row->conventionroom->room_name : '';
                                    ?>
                                    <tr class="<?php echo $rowClass; ?>">
                                        <td class="sd-match-num"><?php echo h($row->match_number); ?></td>
                                        <td class="sd-team-a">
                                            <?php echo getTeamDisplay($teamAraw, $teamNameMap, $schedCat); ?>
                                            <?php if ($isBye) { ?><span class="sd-badge-bye">BYE</span><?php } ?>
                                        </td>
                                        <td class="sd-vs">vs</td>
                                        <td class="sd-team-b">
                                            <?php if ($isBye) { ?>
                                                <em style="color:#bbb;">— bye —</em>
                                            <?php } else { ?>
                                                <?php echo getTeamDisplay($teamBraw, $teamNameMap, $schedCat); ?>
                                            <?php } ?>
                                        </td>
                                        <td class="sd-day"><?php echo h($row->day); ?></td>
                                        <td class="sd-time"><?php echo h($startTime); ?></td>
                                        <td class="sd-location"><?php echo h($roomName); ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php } ?>

                <div class="sd-footer"><?php echo h($programTitle); ?> &mdash; <?php echo h($eventName); ?> Draw</div>
            <?php } ?>
        </div>
    </div>
</div>
