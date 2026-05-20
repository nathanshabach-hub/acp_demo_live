<?php
// Reusable header strip showing the registrant context for sub-pages
// of a Convention Registration. Expects $CRDetails entity in scope.
if (!isset($CRDetails)) { return; }
$cr = $CRDetails;
$user = $cr->Users ?? null;
$type = $user->user_type ?? null;
$typePill = 'cr-pill-other'; $typeLbl = 'N/A';
if ($type === 'School')         { $typePill = 'cr-pill-school'; $typeLbl = 'School'; }
elseif ($type === 'Judge')      { $typePill = 'cr-pill-judge';  $typeLbl = 'Judge'; }
elseif ($type === 'Teacher_Parent') { $typePill = 'cr-pill-tp'; $typeLbl = 'Teacher / Parent'; }

$statusMeta = [
    1 => ['label' => 'Active',   'class' => 'cr-status-active'],
    2 => ['label' => 'Pending',  'class' => 'cr-status-pending'],
    0 => ['label' => 'Inactive', 'class' => 'cr-status-inactive'],
];
$sm = $statusMeta[$cr->status ?? -1] ?? ['label' => 'Unknown', 'class' => 'cr-status-inactive'];
$displayName = trim((string)($user->first_name ?? '') . ' ' . (string)($user->last_name ?? ''));
if ($type === 'School') { $displayName = (string)($user->first_name ?? ''); }
?>
<style>
.crh-card { display:flex; flex-wrap:wrap; gap:18px; padding:16px 18px; background:#fff; border:1px solid #e3e6ec; border-radius:6px; margin:14px 0 18px; }
.crh-block { flex:1 1 200px; min-width:180px; }
.crh-block .crh-lbl { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#7b8794; margin-bottom:3px; font-weight:600; }
.crh-block .crh-val { font-size:14px; color:#2c3e50; font-weight:600; line-height:1.3; }
.crh-block .crh-sub { font-size:12px; color:#7b8794; margin-top:2px; }
.crh-back { margin-bottom:6px; }
.cr-pill { display:inline-block; padding:2px 9px; border-radius:11px; font-size:11px; font-weight:600; line-height:1.6; }
.cr-pill-school { background:#e1f3ff; color:#1f6f9b; }
.cr-pill-judge  { background:#fff1d6; color:#a06600; }
.cr-pill-tp     { background:#ece6ff; color:#5037a7; }
.cr-pill-other  { background:#eceff1; color:#5b6770; }
.cr-status { display:inline-flex; align-items:center; gap:5px; padding:2px 9px; border-radius:11px; font-size:11px; font-weight:600; }
.cr-status .dot { width:7px; height:7px; border-radius:50%; }
.cr-status-active   { background:#e1f5ec; color:#1c7a45; }
.cr-status-active .dot { background:#1c7a45; }
.cr-status-pending  { background:#fff5e1; color:#9a6500; }
.cr-status-pending .dot { background:#d99100; }
.cr-status-inactive { background:#fdecec; color:#a52a2a; }
.cr-status-inactive .dot { background:#a52a2a; }
.cr-table { font-size:13px; }
.cr-table thead th { background:#f6f8fb; color:#34495e; font-weight:600; border-bottom:2px solid #d8dde4 !important; white-space:nowrap; }
.cr-table tbody td { vertical-align: middle !important; }
.cr-empty { padding:38px 18px; text-align:center; color:#7b8794; }
.cr-empty i { font-size:34px; color:#cbd2d9; display:block; margin-bottom:8px; }
</style>
<div class="crh-back">
    <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to Registrations', ['controller'=>'conventionregistrations','action'=>'index'], ['escape'=>false,'class'=>'btn btn-default btn-sm']); ?>
</div>
<div class="crh-card">
    <div class="crh-block">
        <div class="crh-lbl">Convention</div>
        <div class="crh-val"><?php echo h($cr->Conventions->name ?? '—'); ?></div>
        <div class="crh-sub">Season Year: <?php echo h($cr->season_year ?? '—'); ?></div>
    </div>
    <div class="crh-block">
        <div class="crh-lbl">Registrant</div>
        <div class="crh-val"><?php echo h($displayName !== '' ? $displayName : '—'); ?></div>
        <?php if (!empty($user->email_address)) { ?>
            <div class="crh-sub"><i class="fa fa-envelope-o"></i> <?php echo h($user->email_address); ?></div>
        <?php } ?>
    </div>
    <div class="crh-block">
        <div class="crh-lbl">Type</div>
        <div class="crh-val"><span class="cr-pill <?php echo $typePill; ?>"><?php echo h($typeLbl); ?></span></div>
    </div>
    <div class="crh-block">
        <div class="crh-lbl">Status</div>
        <div class="crh-val"><span class="cr-status <?php echo $sm['class']; ?>"><span class="dot"></span><?php echo h($sm['label']); ?></span></div>
        <?php if (!empty($cr->created)) { ?>
            <div class="crh-sub">Registered <?php echo safe_date('M d, Y', strtotime($cr->created)); ?></div>
        <?php } ?>
    </div>
</div>
