<?php
$activeSeason = null;
$totalSeasons = $seasons->count();
foreach ($seasons as $s) { if (!$activeSeason) $activeSeason = $s; }
$totalRegistrations = $registrations->count();
?>
<style>
.vc-hero {
    background: linear-gradient(135deg, #1c2452 0%, #3c4cad 100%);
    color: #fff;
    border-radius: 8px;
    padding: 24px 28px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 22px;
    box-shadow: 0 3px 12px rgba(28,36,82,.15);
}
.vc-hero-icon {
    width: 64px; height: 64px;
    border-radius: 50%;
    background: rgba(255,255,255,.13);
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; flex-shrink: 0;
}
.vc-hero h1 { margin: 0 0 6px; font-size: 24px; font-weight: 700; color: #fff; }
.vc-hero .meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px; color: #d6ddf5; }
.vc-hero .meta span { display: inline-flex; align-items: center; gap: 5px; }
.vc-hero .hero-actions { margin-left: auto; display: flex; gap: 8px; flex-shrink: 0; }
.vc-hero .hero-actions .btn {
    background: rgba(255,255,255,.15);
    border: 1px solid rgba(255,255,255,.3);
    color: #fff;
}
.vc-hero .hero-actions .btn:hover { background: rgba(255,255,255,.25); color: #fff; }
.vc-hero .status-pill {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 600;
}
.vc-hero .status-pill.active   { background: #d4edda; color: #155724; }
.vc-hero .status-pill.inactive { background: #f1f1f1; color: #777; }

/* KPI tiles */
.vc-kpi-row { display: flex; gap: 14px; flex-wrap: wrap; margin-bottom: 22px; }
.vc-kpi {
    flex: 1; min-width: 180px;
    background: #fff;
    border-radius: 8px;
    border-left: 4px solid #1c2452;
    padding: 16px 20px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
    display: flex; align-items: center; gap: 14px;
}
.vc-kpi .ico {
    width: 44px; height: 44px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    background: #eef2ff; color: #1c2452;
}
.vc-kpi .num { font-size: 22px; font-weight: 700; color: #222; line-height: 1; }
.vc-kpi .lbl { font-size: 12px; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-top: 4px; }
.vc-kpi.green  { border-color: #28a745; } .vc-kpi.green  .ico { background: #e6f7e9; color: #28a745; }
.vc-kpi.amber  { border-color: #f39c12; } .vc-kpi.amber  .ico { background: #fff5e0; color: #f39c12; }
.vc-kpi.purple { border-color: #8e44ad; } .vc-kpi.purple .ico { background: #f3e8fa; color: #8e44ad; }

/* Cards */
.vc-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); margin-bottom: 22px; overflow: hidden; }
.vc-card-head {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 20px;
    border-bottom: 1px solid #eef0f5;
    background: #fafbfd;
}
.vc-card-head h4 { margin: 0; font-size: 15px; font-weight: 700; color: #1c2452; }
.vc-card-head h4 i { color: #1c2452; margin-right: 6px; }
.vc-card-head .right { margin-left: auto; font-size: 12px; color: #888; }
.vc-card-body { padding: 18px 20px; }
.vc-detail-row { display: flex; padding: 8px 0; border-bottom: 1px solid #f4f5f9; font-size: 14px; }
.vc-detail-row:last-child { border-bottom: none; }
.vc-detail-row .lbl { width: 38%; color: #888; font-size: 12px; text-transform: uppercase; letter-spacing: .3px; padding-top: 2px; font-weight: 600; }
.vc-detail-row .val { color: #222; flex: 1; }

.vc-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.vc-table th { background: #f5f7fb; color: #555; font-size: 11px; text-transform: uppercase; padding: 10px 14px; border-bottom: 2px solid #e0e4f0; text-align: left; letter-spacing: .4px; }
.vc-table td { padding: 10px 14px; border-bottom: 1px solid #f0f3f8; vertical-align: middle; }
.vc-table tr:hover td { background: #fafbfe; }
.vc-table .school-name { font-weight: 600; color: #222; }
.vc-table .school-email { font-size: 12px; color: #888; }
.fee-pill {
    display: inline-block;
    background: #eaf7ea; color: #1e6e1e;
    border: 1px solid #b8e0b8;
    border-radius: 14px;
    padding: 2px 11px;
    font-size: 12px; font-weight: 600;
}
.exp-toggle { background: none; border: none; color: #1c2452; cursor: pointer; font-size: 13px; padding: 0; }
.exp-toggle:hover { text-decoration: underline; }
.att-sub { background: #fafbfe; }
.att-sub td { padding: 0 14px 12px; }
.att-mini { width: 100%; border-collapse: collapse; font-size: 12.5px; margin: 4px 0 8px 30px; max-width: calc(100% - 30px); border-left: 3px solid #e0e4f0; }
.att-mini th { background: #eef2ff; color: #1c2452; padding: 6px 10px; font-weight: 600; text-align: left; font-size: 11px; text-transform: uppercase; }
.att-mini td { padding: 6px 10px; border-bottom: 1px solid #eef0f5; }
.att-count-badge {
    display: inline-block; background: #1c2452; color: #fff;
    border-radius: 10px; padding: 1px 8px;
    font-size: 11px; font-weight: 600; margin-left: 6px;
}
.empty-state { text-align: center; padding: 40px 20px; color: #aaa; }
.empty-state i { font-size: 36px; margin-bottom: 10px; display: block; color: #ddd; }
</style>

<div class="content-wrapper">
    <section class="content-header" style="padding-bottom:0;">
        <ol class="breadcrumb" style="float:none;position:static;display:inline-block;margin:0 0 14px;background:#fff;border:1px solid #e0e4f0;border-radius:6px;padding:8px 14px;top:auto;right:auto;">
            <li><a href="/admin/admins/conference" style="color:#1c2452;"><i class="fa fa-university"></i> Conference Portal</a></li>
            <li><a href="/admin/admins/list-conferences" style="color:#1c2452;">All Conferences</a></li>
            <li class="active" style="color:#666;"><?php echo h($convention->name); ?></li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->Flash->render(); ?>

        <!-- Hero -->
        <div class="vc-hero">
            <div class="vc-hero-icon"><i class="fa fa-university"></i></div>
            <div style="flex:1;">
                <h1><?php echo h($convention->name); ?></h1>
                <div class="meta">
                    <?php if (!empty($convention->address)): ?>
                    <span><i class="fa fa-map-marker"></i> <?php echo h($convention->address); ?></span>
                    <?php endif; ?>
                    <span>
                        <?php if ($convention->status == 1): ?>
                            <span class="status-pill active"><i class="fa fa-circle"></i> Active</span>
                        <?php else: ?>
                            <span class="status-pill inactive">Inactive</span>
                        <?php endif; ?>
                    </span>
                    <?php if ($activeSeason): ?>
                    <span><i class="fa fa-calendar"></i> Latest Year: <?php echo h($activeSeason->season_year); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-actions">
                <?php if (!empty($convention->google_map_link)): ?>
                <a href="<?php echo h($convention->google_map_link); ?>" target="_blank" class="btn btn-sm">
                    <i class="fa fa-map"></i> Map
                </a>
                <?php endif; ?>
                <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back', ['action' => 'listConferences'], ['escape' => false, 'class' => 'btn btn-sm']); ?>
            </div>
        </div>

        <!-- KPI tiles -->
        <div class="vc-kpi-row">
            <div class="vc-kpi">
                <div class="ico"><i class="fa fa-graduation-cap"></i></div>
                <div>
                    <div class="num"><?php echo $totalRegistrations; ?></div>
                    <div class="lbl">Schools Registered</div>
                </div>
            </div>
            <div class="vc-kpi green">
                <div class="ico"><i class="fa fa-users"></i></div>
                <div>
                    <div class="num"><?php echo $totalAttendees; ?></div>
                    <div class="lbl">Total Attendees</div>
                </div>
            </div>
            <div class="vc-kpi amber">
                <div class="ico"><i class="fa fa-calendar-check-o"></i></div>
                <div>
                    <div class="num"><?php echo $totalSeasons; ?></div>
                    <div class="lbl">Conference Year(s)</div>
                </div>
            </div>
            <?php if ($activeSeason && !empty($activeSeason->student_registration_fees)):
                $curr = !empty($activeSeason->currency) ? $activeSeason->currency : 'FJD';
            ?>
            <div class="vc-kpi purple">
                <div class="ico"><i class="fa fa-tag"></i></div>
                <div>
                    <div class="num" style="font-size:18px;"><?php echo $curr . ' ' . number_format($activeSeason->student_registration_fees, 0); ?></div>
                    <div class="lbl">Per Delegate</div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- LEFT — details + years -->
            <div class="col-md-5">
                <div class="vc-card">
                    <div class="vc-card-head"><h4><i class="fa fa-info-circle"></i> Conference Details</h4></div>
                    <div class="vc-card-body">
                        <div class="vc-detail-row">
                            <span class="lbl">Name</span>
                            <span class="val"><strong><?php echo h($convention->name); ?></strong></span>
                        </div>
                        <div class="vc-detail-row">
                            <span class="lbl">Venue</span>
                            <span class="val"><?php echo h($convention->address ?: '—'); ?></span>
                        </div>
                        <?php if (!empty($convention->google_map_link)): ?>
                        <div class="vc-detail-row">
                            <span class="lbl">Map</span>
                            <span class="val"><a href="<?php echo h($convention->google_map_link); ?>" target="_blank" rel="noopener noreferrer">View on Google Maps <i class="fa fa-external-link"></i></a></span>
                        </div>
                        <?php endif; ?>
                        <div class="vc-detail-row">
                            <span class="lbl">Status</span>
                            <span class="val">
                                <?php if ($convention->status == 1): ?>
                                    <span class="label label-success">Active</span>
                                <?php else: ?>
                                    <span class="label label-default">Inactive</span>
                                <?php endif; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="vc-card">
                    <div class="vc-card-head">
                        <h4><i class="fa fa-calendar"></i> Conference Years</h4>
                        <span class="right"><?php echo $totalSeasons; ?> year<?php echo $totalSeasons==1?'':'s'; ?></span>
                    </div>
                    <?php if (!$seasons->isEmpty()): ?>
                    <table class="vc-table">
                        <thead>
                            <tr>
                                <th>Year</th>
                                <th>Registration Window</th>
                                <th>Fee</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($seasons as $cs):
                                $curr = !empty($cs->currency) ? $cs->currency : 'FJD';
                                $hasOpen = !empty($cs->registration_start_date) && $cs->registration_start_date !== '0000-00-00';
                                $hasEnd  = !empty($cs->registration_end_date)   && $cs->registration_end_date   !== '0000-00-00';
                            ?>
                            <tr>
                                <td><strong><?php echo h($cs->season_year); ?></strong></td>
                                <td>
                                    <?php if ($hasOpen && $hasEnd): ?>
                                        <span style="font-size:12px;">
                                            <?php echo safe_date('d M', strtotime($cs->registration_start_date)); ?>
                                            &rarr;
                                            <?php echo safe_date('d M Y', strtotime($cs->registration_end_date)); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:12px;">No window set</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($cs->student_registration_fees)): ?>
                                        <span class="fee-pill"><?php echo $curr . ' ' . number_format($cs->student_registration_fees, 2); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state"><i class="fa fa-calendar-o"></i> No years configured yet.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIGHT — registered schools -->
            <div class="col-md-7">
                <div class="vc-card">
                    <div class="vc-card-head">
                        <h4><i class="fa fa-graduation-cap"></i> Registered Schools</h4>
                        <span class="right"><?php echo $totalRegistrations; ?> registration<?php echo $totalRegistrations==1?'':'s'; ?></span>
                    </div>
                    <?php if (!$registrations->isEmpty()): ?>
                    <table class="vc-table">
                        <thead>
                            <tr>
                                <th>School / Admin</th>
                                <th>Year</th>
                                <th>Registered</th>
                                <th>Attendees</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $reg):
                                $adminName = '';
                                $adminEmail = '';
                                if (!empty($reg->Users)) {
                                    $adminName = trim(($reg->Users['first_name'] ?? '') . ' ' . ($reg->Users['last_name'] ?? ''));
                                    $adminEmail = $reg->Users['email_address'] ?? '';
                                }
                                $atts = $attendeesByReg[$reg->id] ?? [];
                                $attCount = count($atts);
                            ?>
                            <tr>
                                <td>
                                    <div class="school-name"><?php echo h($adminName ?: 'User #' . $reg->user_id); ?></div>
                                    <?php if ($adminEmail): ?><div class="school-email"><?php echo h($adminEmail); ?></div><?php endif; ?>
                                </td>
                                <td><?php echo h($reg->season_year); ?></td>
                                <td><?php echo !empty($reg->created) ? safe_date('d M Y', strtotime($reg->created)) : '—'; ?></td>
                                <td>
                                    <?php if ($attCount > 0): ?>
                                        <button type="button" class="exp-toggle" onclick="vcToggleAtt(<?php echo $reg->id; ?>)">
                                            <i class="fa fa-users"></i> <?php echo $attCount; ?>
                                            <i class="fa fa-caret-down" id="vc-caret-<?php echo $reg->id; ?>"></i>
                                        </button>
                                    <?php else: ?>
                                        <span class="text-muted" style="font-size:12px;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($reg->status == 1): ?>
                                        <span class="label label-success">Active</span>
                                    <?php else: ?>
                                        <span class="label label-warning">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php if ($attCount > 0): ?>
                            <tr class="att-sub" id="vc-att-<?php echo $reg->id; ?>" style="display:none;">
                                <td colspan="5">
                                    <table class="att-mini">
                                        <thead>
                                            <tr><th>Name</th><th>Email</th><th>Role</th><th>Dietary</th><th>Both Days</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($atts as $a):
                                                $teacher = $a->Teachers ?? null;
                                                $tName = $teacher ? trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) : '—';
                                                $tEmail = $teacher ? ($teacher['email_address'] ?? '') : '';
                                            ?>
                                            <tr>
                                                <td><?php echo h($tName ?: '—'); ?></td>
                                                <td style="color:#666;"><?php echo h($tEmail); ?></td>
                                                <td><?php echo h($a->attendee_role ?: '—'); ?></td>
                                                <td><?php echo h($a->dietary_needs ?: 'None'); ?></td>
                                                <td><?php echo $a->attending_both_days ? '<i class="fa fa-check text-success"></i> Yes' : '<i class="fa fa-times text-muted"></i> No'; ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div class="empty-state"><i class="fa fa-inbox"></i> No registrations yet for this conference.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function vcToggleAtt(id) {
    var row = document.getElementById('vc-att-' + id);
    var caret = document.getElementById('vc-caret-' + id);
    if (!row) return;
    var open = row.style.display !== 'none';
    row.style.display = open ? 'none' : 'table-row';
    if (caret) {
        caret.classList.toggle('fa-caret-down', open);
        caret.classList.toggle('fa-caret-up', !open);
    }
}
</script>
