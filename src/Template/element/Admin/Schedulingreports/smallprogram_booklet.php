<?php
$hasRows = isset($programDays) && count($programDays) > 0;
$programTitle = trim((string)($conventionSD->Conventions['name'] ?? 'Student Convention'));
$programSeason = trim((string)($conventionSD->season_year ?? ''));
$programVenue = trim((string)($conventionSD->Conventions['address'] ?? ''));
$smallProgramNotes = isset($smallProgramNotes) && is_array($smallProgramNotes) ? $smallProgramNotes : array();
$introEntriesRaw = trim((string)($smallProgramNotes['intro_entries'] ?? ''));
$introEntries = array();
if ($introEntriesRaw !== '') {
    foreach (preg_split('/\r\n|\r|\n/', $introEntriesRaw) as $line) {
        $line = trim((string)$line);
        if ($line === '') {
            continue;
        }
        $parts = explode('|', $line, 2);
        $introEntries[] = array(
            'time' => trim((string)($parts[0] ?? '')),
            'text' => trim((string)($parts[1] ?? $parts[0] ?? '')),
        );
    }
}
$dinnerBanner = trim((string)($smallProgramNotes['dinner_banner'] ?? ''));
$eveningRallyTime = trim((string)($smallProgramNotes['evening_rally_time'] ?? ''));
$eveningRallyLabel = trim((string)($smallProgramNotes['evening_rally_label'] ?? ''));
$offsiteNote = trim((string)($smallProgramNotes['offsite_note'] ?? ''));
$footerNote = trim((string)($smallProgramNotes['footer_note'] ?? ''));
$logoPath = WWW_ROOT . 'img/front/accelerate-logo.jpg';
$logoSrc = '';
if (is_file($logoPath)) {
    $logoData = @file_get_contents($logoPath);
    if ($logoData !== false) {
        $logoSrc = 'data:image/jpeg;base64,'.base64_encode($logoData);
    }
}

$lunchBanner = '';
if (!empty($schedulingD->lunch_time_start) && !empty($schedulingD->lunch_time_end)) {
    $lunchBanner = 'LUNCH '.date('g:i a', strtotime((string)$schedulingD->lunch_time_start)).' - '.date('g:i a', strtotime((string)$schedulingD->lunch_time_end));
}
?>

<style>
/* ===== SMALL PROGRAM BOOKLET ===== */
.sp-wrap {
    background: #d8d8d8;
    padding: 24px 16px;
    font-family: 'Segoe UI', Arial, Helvetica, sans-serif;
}
.sp-sheet {
    max-width: 860px;
    margin: 0 auto;
    background: #fff;
    color: #1a1a1a;
    box-shadow: 0 8px 32px rgba(0,0,0,0.18);
    border-radius: 4px;
    overflow: hidden;
}

/* --- HEADER --- */
.sp-header {
    background: #1e3a6e;
    color: #fff;
    padding: 22px 32px 16px;
    display: flex;
    align-items: center;
    gap: 24px;
}
.sp-header-logo {
    flex-shrink: 0;
}
.sp-logo-img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.6);
    object-fit: cover;
    background: #fff;
    padding: 0;
}
.sp-header-title {
    flex: 1;
    text-align: center;
}
.sp-header-title .sp-org {
    font-size: 13px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    opacity: 0.85;
    margin-bottom: 2px;
}
.sp-header-title .sp-convention {
    font-size: 22px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    line-height: 1.1;
}
.sp-header-title .sp-name {
    font-size: 17px;
    font-weight: 600;
    margin-top: 3px;
}
.sp-header-title .sp-dates {
    font-size: 14px;
    margin-top: 4px;
    opacity: 0.9;
}
.sp-header-title .sp-venue {
    font-size: 13px;
    margin-top: 3px;
    color: #a8d08d;
    font-weight: 600;
    letter-spacing: 0.06em;
}

/* --- BODY --- */
.sp-body {
    padding: 20px 28px 24px;
}

/* --- INTRO SECTION --- */
.sp-intro {
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e0e0e0;
}
.sp-intro-heading {
    font-size: 16px;
    font-weight: 700;
    color: #1e3a6e;
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.sp-intro-row {
    display: flex;
    gap: 0;
    padding: 3px 0;
    font-size: 14px;
    border-bottom: 1px solid #f0f0f0;
}
.sp-intro-row:last-child {
    border-bottom: none;
}
.sp-intro-time {
    width: 155px;
    flex-shrink: 0;
    color: #444;
    font-weight: 600;
}
.sp-intro-text {
    flex: 1;
}

/* --- DAY SECTION --- */
.sp-day {
    margin-top: 18px;
}
.sp-day-heading {
    background: #1e3a6e;
    color: #fff;
    font-size: 15px;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 3px;
    margin-bottom: 10px;
}

/* --- SESSION BLOCK --- */
.sp-session {
    margin-bottom: 12px;
}
.sp-session-bar {
    display: flex;
    align-items: baseline;
    gap: 10px;
    background: #eef2f9;
    border-left: 4px solid #3758a6;
    padding: 5px 10px;
    margin-bottom: 8px;
    border-radius: 0 3px 3px 0;
}
.sp-session-time {
    font-size: 13px;
    font-weight: 700;
    color: #1e3a6e;
    white-space: nowrap;
}
.sp-session-label {
    font-size: 12px;
    color: #555;
    text-transform: uppercase;
    letter-spacing: 0.07em;
}

/* --- ROOM CARDS GRID --- */
.sp-rooms {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.sp-room-card {
    flex: 1 1 160px;
    min-width: 130px;
    border: 1px solid #c8d0dc;
    border-radius: 4px;
    overflow: hidden;
    break-inside: avoid;
    page-break-inside: avoid;
}
.sp-room-card-header {
    background: #3758a6;
    color: #fff;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 4px 8px;
    text-align: center;
}
.sp-room-card-events {
    padding: 5px 8px 4px;
    list-style: none;
    margin: 0;
    font-size: 11.5px;
    line-height: 1.35;
}
.sp-room-card-events li {
    padding: 1px 0;
    border-bottom: 1px solid #f2f2f2;
}
.sp-room-card-events li:last-child {
    border-bottom: none;
}

/* --- BANNERS --- */
.sp-banner {
    margin: 14px 0 10px;
    text-align: center;
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    padding: 7px 16px;
    border-radius: 30px;
    background: #1e3a6e;
    color: #fff;
}
.sp-banner.sp-dinner {
    background: #5a3a8a;
}

/* --- EVENING ROW --- */
.sp-evening-row {
    display: flex;
    gap: 0;
    padding: 4px 0;
    font-size: 14px;
    margin-top: 4px;
}
.sp-evening-time {
    width: 155px;
    flex-shrink: 0;
    font-weight: 700;
    color: #5a3a8a;
}
.sp-evening-label {
    flex: 1;
    font-weight: 600;
}

/* --- DIVIDER --- */
.sp-divider {
    border: none;
    border-top: 1px dashed #bbb;
    margin: 10px 0 8px;
}

/* --- LEGEND --- */
.sp-legend {
    margin-top: 16px;
    padding-top: 10px;
    border-top: 2px solid #e0e0e0;
    display: flex;
    flex-wrap: wrap;
    gap: 6px 18px;
    font-size: 11.5px;
    color: #444;
}
.sp-legend-item {
    white-space: nowrap;
}
.sp-legend-key {
    font-weight: 700;
    color: #1e3a6e;
}

/* --- OFFSITE NOTE --- */
.sp-offsite-note {
    margin-top: 10px;
    font-size: 12px;
    font-style: italic;
    color: #0077c8;
    font-weight: 700;
}

/* --- FOOTER --- */
.sp-footer {
    margin-top: 12px;
    padding-top: 8px;
    border-top: 1px solid #e8e8e8;
    text-align: center;
    font-size: 11px;
    color: #888;
    letter-spacing: 0.08em;
}

.sp-empty {
    padding: 32px 0;
    font-size: 15px;
    color: #888;
}

@media print {
    .sp-wrap {
        background: #fff;
        padding: 0;
    }
    .sp-sheet {
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
    }
    .sp-room-card {
        break-inside: avoid;
        page-break-inside: avoid;
    }
    /* Each day after the first gets its own page (Sunday+Monday share page 1) */
    .sp-day + .sp-day {
        break-before: page;
        page-break-before: always;
    }
}
</style>

<div class="sp-wrap">
    <div class="sp-sheet">

        <!-- HEADER -->
        <div class="sp-header">
            <?php if ($logoSrc !== '') { ?>
                <div class="sp-header-logo">
                    <img src="<?php echo $logoSrc; ?>" alt="Accelerate" class="sp-logo-img" />
                </div>
            <?php } ?>
            <div class="sp-header-title">
                <div class="sp-org">A.C.E.</div>
                <div class="sp-convention">Student Convention</div>
                <div class="sp-name"><?php echo h($programTitle); ?></div>
                <?php if ($programDateRangeLabel !== '') { ?>
                    <div class="sp-dates"><?php echo h($programDateRangeLabel); ?></div>
                <?php } ?>
                <?php if ($programVenue !== '') { ?>
                    <div class="sp-venue"><?php echo h($programVenue); ?></div>
                <?php } ?>
            </div>
        </div>

        <!-- BODY -->
        <div class="sp-body">

            <?php if ($hasRows) { ?>

                <?php if (count($introEntries)) { ?>
                    <div class="sp-intro">
                        <div class="sp-intro-heading"><?php echo h((string)($smallProgramNotes['intro_day_label'] ?? '')); ?></div>
                        <?php foreach ($introEntries as $entry) { ?>
                            <div class="sp-intro-row">
                                <div class="sp-intro-time"><?php echo h($entry['time']); ?></div>
                                <div class="sp-intro-text"><?php echo h($entry['text']); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } ?>

                <?php foreach ($programDays as $dayIndex => $dayData) { ?>
                    <div class="sp-day">
                        <div class="sp-day-heading">
                            <?php
                            $dateHeading = trim((string)($dayData['dateLabel'] ?? ''));
                            echo h($dateHeading !== '' ? $dateHeading : $dayData['dayLabel']);
                            ?>
                        </div>

                        <?php foreach ($dayData['sessions'] as $sessionData) { ?>
                            <?php
                            $sessionStart  = !empty($sessionData['startRaw'])  ? date('g:i a', strtotime($sessionData['startRaw']))  : '';
                            $sessionFinish = !empty($sessionData['finishRaw']) ? date('g:i a', strtotime($sessionData['finishRaw'])) : '';
                            $sessionRange  = trim($sessionStart . ($sessionFinish !== '' ? ' – ' . $sessionFinish : ''));
                            ?>

                            <div class="sp-session">
                                <div class="sp-session-bar">
                                    <span class="sp-session-time"><?php echo h($sessionRange); ?></span>
                                    <span class="sp-session-label"><?php echo h($sessionData['title']); ?></span>
                                </div>

                                <div class="sp-rooms">
                                    <?php foreach ($sessionData['rooms'] as $roomName => $eventNames) { ?>
                                        <div class="sp-room-card">
                                            <div class="sp-room-card-header"><?php echo h($roomName); ?></div>
                                            <ul class="sp-room-card-events">
                                                <?php foreach ($eventNames as $eventName) { ?>
                                                    <li><?php echo h($eventName); ?></li>
                                                <?php } ?>
                                            </ul>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <?php if ($sessionData['key'] === 'morning' && $lunchBanner !== '') { ?>
                                <div class="sp-banner"><?php echo h($lunchBanner); ?></div>
                            <?php } ?>

                            <?php if ($sessionData['key'] === 'afternoon' && $dinnerBanner !== '') { ?>
                                <div class="sp-banner sp-dinner"><?php echo h($dinnerBanner); ?></div>
                                <?php if ($eveningRallyTime !== '' || $eveningRallyLabel !== '') { ?>
                                    <div class="sp-evening-row">
                                        <div class="sp-evening-time"><?php echo h($eveningRallyTime); ?></div>
                                        <div class="sp-evening-label"><?php echo h($eveningRallyLabel); ?></div>
                                    </div>
                                <?php } ?>
                            <?php } ?>

                        <?php } ?>

                        <?php if ($dayIndex === array_key_last($programDays)) { ?>
                            <div class="sp-legend">
                                <span class="sp-legend-item"><span class="sp-legend-key">M</span> – Male</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">F</span> – Female</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">U14</span> – Under 14</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">U16</span> – Under 16</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">U17</span> – Under 17</span>
                                <span class="sp-legend-item"><span class="sp-legend-key">O</span> – Open</span>
                            </div>
                            <?php if ($offsiteNote !== '') { ?>
                                <div class="sp-offsite-note"><?php echo h($offsiteNote); ?></div>
                            <?php } ?>
                            <?php if ($footerNote !== '') { ?>
                                <div class="sp-footer" style="color:#444;letter-spacing:0.02em;"><?php echo h($footerNote); ?></div>
                            <?php } ?>
                            <div class="sp-footer"><?php echo h($programTitle); ?> A.C.E. Student Convention<?php if ($programSeason !== '') { ?> &mdash; <?php echo h($programSeason); ?><?php } ?></div>
                        <?php } ?>

                    </div>
                <?php } ?>

            <?php } else { ?>
                <div class="sp-empty">No scheduling rows found for this season. Generate schedules first, then open Small Program again.</div>
            <?php } ?>

        </div><!-- /.sp-body -->
    </div><!-- /.sp-sheet -->
</div><!-- /.sp-wrap -->
