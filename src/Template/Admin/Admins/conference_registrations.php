<?php
$roleColors = [
    'Principal'     => ['#1c2452', '#eef2ff'],
    'Administrator' => ['#8e44ad', '#f3e8fa'],
    'Supervisor'    => ['#2980b9', '#e3f1fb'],
    'Monitor'       => ['#16a085', '#e3f7f2'],
    'Other'         => ['#7f8c8d', '#ecf0f1'],
];
?>
<style>
.cr-hero {
    background: linear-gradient(135deg, #1c2452 0%, #3c4cad 100%);
    color: #fff; border-radius: 8px; padding: 22px 28px; margin-bottom: 22px;
    display: flex; align-items: center; gap: 22px;
    box-shadow: 0 3px 12px rgba(28,36,82,.15);
}
.cr-hero-icon { width: 58px; height: 58px; border-radius: 50%; background: rgba(255,255,255,.13); display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
.cr-hero h1 { margin: 0 0 4px; font-size: 22px; font-weight: 700; color: #fff; }
.cr-hero p  { margin: 0; font-size: 13px; color: #d6ddf5; }

.cr-kpi-row { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 18px; }
.cr-kpi {
    flex: 1; min-width: 150px; background: #fff; border-radius: 8px;
    border-top: 3px solid #1c2452; padding: 14px 16px;
    box-shadow: 0 1px 4px rgba(0,0,0,.06); cursor: pointer;
    transition: transform .12s, box-shadow .12s;
}
.cr-kpi:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,.1); }
.cr-kpi.active { box-shadow: 0 0 0 2px #1c2452, 0 4px 12px rgba(28,36,82,.2); }
.cr-kpi .row1 { display: flex; align-items: center; gap: 10px; }
.cr-kpi .ico { width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; }
.cr-kpi .num { font-size: 22px; font-weight: 700; color: #222; line-height: 1; }
.cr-kpi .lbl { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: .4px; margin-top: 6px; font-weight: 600; }

.cr-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.06); overflow: hidden; }
.cr-card-head { display: flex; align-items: center; gap: 10px; padding: 14px 20px; border-bottom: 1px solid #eef0f5; background: #fafbfd; flex-wrap: wrap; }
.cr-card-head h4 { margin: 0; font-size: 15px; font-weight: 700; color: #1c2452; }
.cr-card-head .right { margin-left: auto; }
.cr-search { padding: 5px 10px; border: 1px solid #d0d6e8; border-radius: 5px; font-size: 13px; min-width: 230px; }

.cr-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.cr-table th { background: #f5f7fb; color: #555; font-size: 11px; text-transform: uppercase; padding: 11px 14px; border-bottom: 2px solid #e0e4f0; text-align: left; letter-spacing: .4px; }
.cr-table td { padding: 11px 14px; border-bottom: 1px solid #f0f3f8; vertical-align: middle; }
.cr-table tr:hover td { background: #fafbfe; }
.att-name { font-weight: 600; color: #222; }
.att-email { font-size: 12px; color: #888; }
.role-pill { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .3px; }
.diet-pill { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 11px; background: #fff5e0; color: #b9770e; }
.diet-none { background: #f0f3f8; color: #888; }
.empty-state { text-align: center; padding: 60px 20px; color: #aaa; }
.empty-state i { font-size: 44px; margin-bottom: 12px; display: block; color: #ddd; }
</style>

<div class="content-wrapper">
    <section class="content-header" style="padding-bottom:0;">
        <ol class="breadcrumb" style="float:none;position:static;display:inline-block;margin:0 0 14px;background:#fff;border:1px solid #e0e4f0;border-radius:6px;padding:8px 14px;top:auto;right:auto;">
            <li><a href="/admin/admins/conference" style="color:#1c2452;"><i class="fa fa-university"></i> Conference Portal</a></li>
            <li class="active" style="color:#666;">Total Registrations</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->Flash->render(); ?>

        <div class="cr-hero">
            <div class="cr-hero-icon"><i class="fa fa-users"></i></div>
            <div style="flex:1;">
                <h1>Total Registrations</h1>
                <p>Every Principal, Administrator, Supervisor, Monitor and Other registered for an Educators' Conference.</p>
            </div>
        </div>

        <div class="cr-kpi-row">
            <div class="cr-kpi active" data-role="" onclick="crSetRole(this, '')">
                <div class="row1">
                    <div class="ico" style="background:#eef2ff;color:#1c2452;"><i class="fa fa-users"></i></div>
                    <div class="num"><?php echo (int)$totalAttendees; ?></div>
                </div>
                <div class="lbl">All Attendees</div>
            </div>
            <?php foreach (['Principal','Administrator','Supervisor','Monitor','Other'] as $role):
                $col = $roleColors[$role];
            ?>
            <div class="cr-kpi" data-role="<?php echo $role; ?>" onclick="crSetRole(this, '<?php echo $role; ?>')" style="border-top-color:<?php echo $col[0]; ?>;">
                <div class="row1">
                    <div class="ico" style="background:<?php echo $col[1]; ?>;color:<?php echo $col[0]; ?>;">
                        <i class="fa fa-user"></i>
                    </div>
                    <div class="num"><?php echo (int)($roleCounts[$role] ?? 0); ?></div>
                </div>
                <div class="lbl"><?php echo $role; ?>s</div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="cr-card">
            <div class="cr-card-head">
                <h4><i class="fa fa-list"></i> Registered Attendees</h4>
                <div class="right">
                    <input type="text" id="cr-filter" class="cr-search" placeholder="Search name, email, school, conference…" oninput="crFilter()">
                </div>
            </div>
            <?php if ($totalAttendees > 0): ?>
            <table class="cr-table" id="cr-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Attendee</th>
                        <th>Role</th>
                        <th>School / Registered By</th>
                        <th>Conference</th>
                        <th>Year</th>
                        <th>Dietary</th>
                        <th>Both Days</th>
                        <th>Registered</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; foreach ($attendees as $row):
                        $i++;
                        $a = $row['attendee'];
                        $reg = $row['registration'];
                        $teacher = $a->Teachers ?? null;
                        $tName = $teacher ? trim(($teacher['first_name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) : '';
                        $tEmail = $teacher ? ($teacher['email_address'] ?? '') : '';

                        $admin = $reg->Users ?? null;
                        $adminName = $admin ? trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? '')) : '';
                        $adminEmail = $admin ? ($admin['email_address'] ?? '') : '';

                        $confName = $reg->Conventions['name'] ?? '—';
                        $confSlug = $reg->Conventions['slug'] ?? null;

                        $role = $a->attendee_role ?: 'Other';
                        if (!isset($roleColors[$role])) $role = 'Other';
                        $rc = $roleColors[$role];

                        $diet = $a->dietary_needs ?: 'None';
                        $searchBlob = strtolower(h(implode(' ', [$tName, $tEmail, $adminName, $adminEmail, $confName, $role])));
                    ?>
                    <tr class="cr-row" data-role="<?php echo h($role); ?>" data-search="<?php echo $searchBlob; ?>">
                        <td style="color:#aaa;"><?php echo $i; ?></td>
                        <td>
                            <div class="att-name"><?php echo h($tName ?: '—'); ?></div>
                            <?php if ($tEmail): ?><div class="att-email"><?php echo h($tEmail); ?></div><?php endif; ?>
                        </td>
                        <td><span class="role-pill" style="background:<?php echo $rc[1]; ?>;color:<?php echo $rc[0]; ?>;"><?php echo h($role); ?></span></td>
                        <td>
                            <div class="att-name" style="font-weight:500;"><?php echo h($adminName ?: '—'); ?></div>
                            <?php if ($adminEmail): ?><div class="att-email"><?php echo h($adminEmail); ?></div><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($confSlug): ?>
                                <?php echo $this->Html->link(h($confName), ['action' => 'viewConference', $confSlug], ['style' => 'color:#1c2452;font-weight:600;']); ?>
                            <?php else: ?>
                                <?php echo h($confName); ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo h($reg->season_year); ?></td>
                        <td><span class="diet-pill <?php echo $diet === 'None' ? 'diet-none' : ''; ?>"><?php echo h($diet); ?></span></td>
                        <td><?php echo $a->attending_both_days ? '<i class="fa fa-check text-success"></i> Yes' : '<i class="fa fa-times text-muted"></i> No'; ?></td>
                        <td style="color:#666;font-size:12.5px;"><?php echo !empty($reg->created) ? date('d M Y', strtotime($reg->created)) : '—'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty-state">
                <i class="fa fa-inbox"></i>
                <div style="font-size:15px;">No attendees registered yet.</div>
                <div style="font-size:13px;margin-top:6px;">Once schools register supervisors and other attendees, they'll appear here.</div>
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<script>
var crCurrentRole = '';
function crSetRole(el, role) {
    crCurrentRole = role;
    document.querySelectorAll('.cr-kpi').forEach(function(k){ k.classList.remove('active'); });
    el.classList.add('active');
    crApply();
}
function crFilter() { crApply(); }
function crApply() {
    var q = (document.getElementById('cr-filter').value || '').trim().toLowerCase();
    document.querySelectorAll('#cr-table .cr-row').forEach(function(tr) {
        var matchRole = (crCurrentRole === '' || tr.getAttribute('data-role') === crCurrentRole);
        var matchText = (q === '' || (tr.getAttribute('data-search') || '').indexOf(q) !== -1);
        tr.style.display = (matchRole && matchText) ? '' : 'none';
    });
}
</script>
