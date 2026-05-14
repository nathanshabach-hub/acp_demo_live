<?php
use Cake\ORM\TableRegistry;
$convName = ''; $seasonYear = '';
if (!empty($sess_admin_header_season_id) && $sess_admin_header_season_id > 0) {
    $cs = TableRegistry::getTableLocator()->get('Conventionseasons')
        ->find()->where(['Conventionseasons.id' => $sess_admin_header_season_id])
        ->contain(['Conventions'])->first();
    if ($cs) {
        $convName  = $cs->Convention['name'] ?? ($cs->convention['name'] ?? '');
        $seasonYear = $cs->season_year ?? '';
    }
}
$seasonMode = !empty($sess_admin_header_season_id) && $sess_admin_header_season_id > 0;

// Build people-distribution data for pie chart
if ($seasonMode) {
    $pieData = [
        ['name' => 'Students',      'y' => (int)($total_students ?? 0),         'color' => '#1c2452'],
        ['name' => 'Supervisors',   'y' => (int)($total_teachers_parents ?? 0), 'color' => '#3498db'],
        ['name' => 'Schools',       'y' => (int)($total_schools ?? 0),          'color' => '#2980b9'],
        ['name' => 'Judges',        'y' => (int)($total_judges ?? 0),           'color' => '#1c7a45'],
    ];
} else {
    $pieData = [
        ['name' => 'Students',      'y' => (int)($total_students ?? 0),         'color' => '#1c2452'],
        ['name' => 'Supervisors',   'y' => (int)($total_teachers_parents ?? 0), 'color' => '#3498db'],
        ['name' => 'Schools',       'y' => (int)($total_schools ?? 0),          'color' => '#2980b9'],
        ['name' => 'Judges',        'y' => (int)($total_judges ?? 0),           'color' => '#1c7a45'],
    ];
}
$pieJson = json_encode($pieData);
?>
<style>
.dash-hero { background:linear-gradient(135deg, #1c2452 0%, #2c3e75 60%, #3b5998 100%); color:#fff; border-radius:10px; padding:20px 24px; margin-bottom:20px; display:flex; flex-wrap:wrap; align-items:center; gap:18px; box-shadow:0 4px 14px rgba(28,36,82,0.18); }
.dash-hero .dh-icon { width:50px; height:50px; border-radius:12px; background:rgba(255,255,255,0.12); display:flex; align-items:center; justify-content:center; font-size:22px; }
.dash-hero h2 { margin:0 0 4px; font-size:20px; font-weight:700; }
.dash-hero .dh-sub { font-size:13px; opacity:.85; }
.dash-hero .dh-spacer { flex:1; }
.dash-hero .dh-mode { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:14px; background:rgba(255,255,255,0.16); font-size:12px; font-weight:600; }

.dash-layout { display:block; }

.dash-section { margin-bottom:20px; }
.dash-section-title { display:flex; align-items:center; gap:10px; margin:0 0 10px; font-size:12px; font-weight:700; color:#5b6770; text-transform:uppercase; letter-spacing:.6px; }
.dash-section-title:before { content:''; width:4px; height:16px; background:#1c2452; border-radius:2px; }
.dash-section-title .ct { margin-left:auto; font-size:11px; color:#9aa5b1; font-weight:600; letter-spacing:0; text-transform:none; }

.dash-grid { display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; }
.dash-tile { background:#fff; border:1px solid #e3e6ec; border-radius:10px; padding:22px 24px 36px; position:relative; overflow:hidden; transition:all .15s ease; display:flex; flex-direction:column; justify-content:space-between; min-height:140px; text-decoration:none; color:inherit; }
.dash-tile:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(0,0,0,0.09); border-color:#c7d0db; text-decoration:none; color:inherit; }
.dash-tile:focus { outline:none; border-color:#3498db; }
.dash-tile .dt-row { display:flex; justify-content:space-between; align-items:center; gap:14px; }
.dash-tile .dt-num { font-size:38px; font-weight:700; color:#1c2452; line-height:1.05; letter-spacing:-0.5px; }
.dash-tile .dt-num.empty { color:#b8c2cc; }
.dash-tile .dt-label { font-size:13px; color:#5b6770; margin-top:6px; font-weight:600; }
.dash-tile .dt-icon { width:52px; height:52px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; color:#fff; flex-shrink:0; box-shadow:0 4px 10px rgba(0,0,0,0.08); }
.dash-tile .dt-arrow { position:absolute; right:14px; bottom:12px; font-size:12px; color:#c7d0db; transition:all .15s ease; }
.dash-tile:hover .dt-arrow { color:#1c2452; transform:translateX(2px); }
.dash-tile .dt-accent { position:absolute; left:0; top:0; bottom:0; width:4px; }

.dt-c-students  .dt-icon { background:#1c2452; } .dt-c-students  .dt-accent { background:#1c2452; }
.dt-c-supers    .dt-icon { background:#3498db; } .dt-c-supers    .dt-accent { background:#3498db; }
.dt-c-schools   .dt-icon { background:#2980b9; } .dt-c-schools   .dt-accent { background:#2980b9; }
.dt-c-judges    .dt-icon { background:#1c7a45; } .dt-c-judges    .dt-accent { background:#1c7a45; }
.dt-c-judging   .dt-icon { background:#7a8d2c; } .dt-c-judging   .dt-accent { background:#7a8d2c; }
.dt-c-eval      .dt-icon { background:#a52a2a; } .dt-c-eval      .dt-accent { background:#a52a2a; }
.dt-c-events    .dt-icon { background:#d4a017; } .dt-c-events    .dt-accent { background:#d4a017; }
.dt-c-tx        .dt-icon { background:#5e8c3a; } .dt-c-tx        .dt-accent { background:#5e8c3a; }
.dt-c-running   .dt-icon { background:#5024a3; } .dt-c-running   .dt-accent { background:#5024a3; }
.dt-c-seasons   .dt-icon { background:#c0392b; } .dt-c-seasons   .dt-accent { background:#c0392b; }
.dt-c-conv      .dt-icon { background:#16a085; } .dt-c-conv      .dt-accent { background:#16a085; }
.dt-c-div       .dt-icon { background:#0f9d8a; } .dt-c-div       .dt-accent { background:#0f9d8a; }
.dt-c-reg       .dt-icon { background:#7a8d2c; } .dt-c-reg       .dt-accent { background:#7a8d2c; }

.dash-side .panel { background:#fff; border:1px solid #e3e6ec; border-radius:8px; margin-bottom:16px; overflow:hidden; }
.dash-side .panel-h { padding:12px 16px; border-bottom:1px solid #eef1f5; font-size:12px; font-weight:700; color:#1c2452; text-transform:uppercase; letter-spacing:.6px; display:flex; align-items:center; gap:8px; }
.dash-side .panel-h i { color:#5b6770; }
.dash-side .panel-b { padding:12px 16px; }
.dash-side .qa-list { display:flex; flex-direction:column; gap:6px; }
.dash-side .qa { display:flex; align-items:center; gap:10px; padding:8px 10px; border-radius:6px; border:1px solid transparent; color:#2c3e50; text-decoration:none; font-size:13px; font-weight:500; transition:all .12s ease; }
.dash-side .qa:hover { background:#f6f8fb; border-color:#e3e6ec; color:#1c2452; text-decoration:none; }
.dash-side .qa i { width:24px; text-align:center; color:#5b6770; }
.dash-side .qa:hover i { color:#1c2452; }

.dash-side .summary-list { display:flex; flex-direction:column; gap:10px; }
.dash-side .summary-row { display:flex; justify-content:space-between; align-items:center; font-size:13px; }
.dash-side .summary-row .lbl { color:#5b6770; display:flex; align-items:center; gap:8px; }
.dash-side .summary-row .lbl .dot { width:10px; height:10px; border-radius:50%; }
.dash-side .summary-row .val { font-weight:700; color:#1c2452; }

#peopleChart { width:100%; height:200px; }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <h1>Dashboard</h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
            <li class="active">Dashboard</li>
        </ol>
    </section>

    <section class="content">
        <div class="dash-hero">
            <div class="dh-icon"><i class="fa fa-tachometer"></i></div>
            <div>
                <h2><?php echo $seasonMode ? h($convName) . ' &mdash; ' . h($seasonYear) : 'Admin Overview'; ?></h2>
                <div class="dh-sub">
                    <?php if ($seasonMode) { ?>
                        Showing metrics for the selected convention season.
                    <?php } else { ?>
                        Global stats across all conventions and seasons. Use the season selector in the header to focus on a specific season.
                    <?php } ?>
                </div>
            </div>
            <div class="dh-spacer"></div>
            <span class="dh-mode">
                <i class="fa <?php echo $seasonMode ? 'fa-filter' : 'fa-globe'; ?>"></i>
                <?php echo $seasonMode ? 'Season Filter Active' : 'All-time'; ?>
            </span>
        </div>

        <?php
        $tile = function ($num, $label, $icon, $colour, $url, $isEmpty = false) {
            $emptyCls = $isEmpty ? ' empty' : '';
            $href = $this->Url->build($url);
            ob_start(); ?>
            <a href="<?php echo $href; ?>" class="dash-tile dt-c-<?php echo $colour; ?>">
                <span class="dt-accent"></span>
                <div class="dt-row">
                    <div>
                        <div class="dt-num<?php echo $emptyCls; ?>"><?php echo $num; ?></div>
                        <div class="dt-label"><?php echo h($label); ?></div>
                    </div>
                    <div class="dt-icon"><i class="fa <?php echo $icon; ?>"></i></div>
                </div>
                <i class="fa fa-arrow-right dt-arrow"></i>
            </a>
            <?php return ob_get_clean();
        };
        ?>

        <div class="dash-layout">
            <div class="dash-main">
                <?php if ($seasonMode) { ?>

                    <div class="dash-section">
                        <div class="dash-section-title">People <span class="ct"><?php echo (int)$total_students + (int)$total_teachers_parents + (int)$total_schools + (int)$total_judges; ?> total</span></div>
                        <div class="dash-grid">
                            <?php echo $tile($total_students ?: '0', 'Students', 'fa-group', 'students', ['controller'=>'conventionregistrationstudents','action'=>'allstudents'], !$total_students); ?>
                            <?php echo $tile($total_teachers_parents ?: '0', 'Supervisors', 'fa-user-secret', 'supers', ['controller'=>'conventionregistrationteachers','action'=>'allteachers'], !$total_teachers_parents); ?>
                            <?php echo $tile($total_schools ?: '0', 'Schools', 'fa-bank', 'schools', ['controller'=>'conventionregistrations','action'=>'allschools'], !$total_schools); ?>
                            <?php echo $tile($total_judges ?: '0', 'Judges', 'fa-bookmark', 'judges', ['controller'=>'conventionregistrations','action'=>'alljudges'], !$total_judges); ?>
                        </div>
                    </div>

                    <div class="dash-section">
                        <div class="dash-section-title">Judging &amp; Events</div>
                        <div class="dash-grid">
                            <?php echo $tile('—', 'Judging List', 'fa-check-square-o', 'judging', ['controller'=>'conventionregistrations','action'=>'judginglist']); ?>
                            <?php echo $tile($total_events_judged ?? '0', 'Evaluations', 'fa-gavel', 'eval', ['controller'=>'judgeevaluations','action'=>'index'], empty($total_events_judged)); ?>
                            <?php echo $tile($total_conv_seas_events ?: '0', 'Total Events', 'fa-puzzle-piece', 'events', ['controller'=>'conventionseasonevents','action'=>'allevents'], !$total_conv_seas_events); ?>
                            <?php echo $tile($total_running_list ?? '0', 'Running List', 'fa-list-ol', 'running', ['controller'=>'admins','action'=>'runninglist'], empty($total_running_list)); ?>
                        </div>
                    </div>

                    <div class="dash-section">
                        <div class="dash-section-title">Finance</div>
                        <div class="dash-grid">
                            <?php echo $tile($total_transactions ?: '0', 'Transactions', 'fa-dollar', 'tx', ['controller'=>'transactions','action'=>'index'], !$total_transactions); ?>
                        </div>
                    </div>

                <?php } else { ?>

                    <div class="dash-section">
                        <div class="dash-section-title">Catalog</div>
                        <div class="dash-grid">
                            <?php echo $tile($total_seasons ?: '0', 'Seasons', 'fa-calendar', 'seasons', ['controller'=>'seasons','action'=>'index'], !$total_seasons); ?>
                            <?php echo $tile($total_conventions ?: '0', 'Conventions', 'fa-bars', 'conv', ['controller'=>'conventions','action'=>'index'], !$total_conventions); ?>
                            <?php echo $tile($total_events ?: '0', 'Global Events', 'fa-puzzle-piece', 'events', ['controller'=>'events','action'=>'index'], !$total_events); ?>
                            <?php echo $tile($total_divisions ?: '0', 'Divisions', 'fa-tasks', 'div', ['controller'=>'divisions','action'=>'index'], !$total_divisions); ?>
                        </div>
                    </div>

                    <div class="dash-section">
                        <div class="dash-section-title">People <span class="ct"><?php echo (int)$total_students + (int)$total_teachers_parents + (int)$total_schools + (int)$total_judges; ?> total</span></div>
                        <div class="dash-grid">
                            <?php echo $tile($total_schools ?: '0', 'Schools', 'fa-bank', 'schools', ['controller'=>'users','action'=>'index'], !$total_schools); ?>
                            <?php echo $tile($total_teachers_parents ?: '0', 'Supervisors', 'fa-user-secret', 'supers', ['controller'=>'users','action'=>'teachers'], !$total_teachers_parents); ?>
                            <?php echo $tile($total_judges ?: '0', 'Judges', 'fa-bookmark', 'judges', ['controller'=>'users','action'=>'judges'], !$total_judges); ?>
                            <?php echo $tile($total_students ?: '0', 'Students', 'fa-group', 'students', ['controller'=>'users','action'=>'students'], !$total_students); ?>
                        </div>
                    </div>

                    <div class="dash-section">
                        <div class="dash-section-title">Activity</div>
                        <div class="dash-grid">
                            <?php echo $tile($total_registrations ?: '0', 'Registrations', 'fa-newspaper-o', 'reg', ['controller'=>'conventionregistrations','action'=>'index'], !$total_registrations); ?>
                            <?php echo $tile($total_transactions ?: '0', 'Transactions', 'fa-dollar', 'tx', ['controller'=>'transactions','action'=>'index'], !$total_transactions); ?>
                        </div>
                    </div>

                <?php } ?>
            </div>
        </div>
    </section>
</div>
