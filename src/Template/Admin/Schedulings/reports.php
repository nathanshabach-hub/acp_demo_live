<style>
.sr-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 18px; padding: 8px 4px; }
.sr-card {
    display: flex; align-items: center; gap: 14px;
    padding: 18px 18px; border-radius: 8px;
    background: #fff; border: 1px solid #e3e6ec;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    text-decoration: none; color: #2c3e50;
    transition: all .15s ease;
}
.sr-card:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.08); border-color: #b8c2cc; text-decoration: none; color: #1a2533; }
.sr-card .sr-ico {
    flex: 0 0 48px; width: 48px; height: 48px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: 20px;
}
.sr-card .sr-body { flex: 1 1 auto; }
.sr-card .sr-title { font-weight: 600; font-size: 15px; line-height: 1.2; }
.sr-card .sr-sub { font-size: 12px; color: #7b8794; margin-top: 4px; }
.sr-card .sr-arrow { color: #b8c2cc; font-size: 18px; }
.sr-section-title { font-size: 13px; text-transform: uppercase; letter-spacing: .06em; color: #7b8794; margin: 14px 6px 6px; font-weight: 600; }
.sr-c-blue   { background: #3c8dbc; }
.sr-c-green  { background: #00a65a; }
.sr-c-purple { background: #605ca8; }
.sr-c-orange { background: #f39c12; }
.sr-c-teal   { background: #39cccc; }
.sr-c-red    { background: #dd4b39; }
.sr-c-navy   { background: #001f3f; }
</style>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Scheduling Reports - [Convention - <?php echo h($conventionSD->Conventions['name']); ?>]&nbsp;&nbsp;&nbsp;&nbsp;
		  [Season Year - <?php echo h($conventionSD->season_year); ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Scheduling Reports </li>
      </ol>
    </section>

    <section class="content">
      <div class="box box-info">
        <div class="box-header with-border">
          <h3 class="box-title"><i class="fa fa-file-text-o"></i> Choose a Report</h3>
          <div class="box-tools pull-right">
            <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to Pre-check', ['controller'=>'schedulings','action'=>'precheck',$convention_season_slug], ['escape'=>false,'class'=>'btn btn-default btn-sm']); ?>
          </div>
        </div>
        <div class="box-body">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <?php
            $renderCard = function($url, $title, $sub, $icon, $colour) {
                $iconHtml = '<span class="sr-ico ' . $colour . '"><i class="fa ' . $icon . '"></i></span>';
                $bodyHtml = '<div class="sr-body"><div class="sr-title">' . h($title) . '</div><div class="sr-sub">' . h($sub) . '</div></div>';
                $arrowHtml = '<span class="sr-arrow"><i class="fa fa-angle-right"></i></span>';
                echo $this->Html->link(
                    $iconHtml . $bodyHtml . $arrowHtml,
                    $url,
                    ['escape' => false, 'class' => 'sr-card', 'title' => $title]
                );
            };
            ?>

            <div class="sr-section-title">People</div>
            <div class="sr-grid">
                <?php $renderCard(['controller'=>'schedulingreports','action'=>'bystudents',$convention_season_slug], 'Report By Students', 'Per-student schedule listing', 'fa-user', 'sr-c-blue'); ?>
                <?php $renderCard(['controller'=>'schedulingreports','action'=>'byschools',$convention_season_slug], 'Report By School', 'Schedules grouped by school', 'fa-graduation-cap', 'sr-c-green'); ?>
                <?php $renderCard(['controller'=>'schedulingreports','action'=>'bysponsors',$convention_season_slug], 'Report By Sponsor', 'Schedules grouped by sponsor', 'fa-handshake-o', 'sr-c-purple'); ?>
            </div>

            <div class="sr-section-title">Activities &amp; Locations</div>
            <div class="sr-grid">
                <?php $renderCard(['controller'=>'schedulingreports','action'=>'byevents',$convention_season_slug], 'Report By Events / Sport', 'Schedules grouped by event', 'fa-flag', 'sr-c-orange'); ?>
                <?php $renderCard(['controller'=>'schedulingreports','action'=>'byrooms',$convention_season_slug], 'Report By Rooms / Location', 'Schedules grouped by room', 'fa-map-marker', 'sr-c-teal'); ?>
            </div>

            <div class="sr-section-title">Programs</div>
            <div class="sr-grid">
                <?php $renderCard(['controller'=>'schedulingreports','action'=>'smallprogram',$convention_season_slug], 'Small Program', 'Compact running order', 'fa-list-alt', 'sr-c-navy'); ?>
                <?php $renderCard(['controller'=>'schedulingreports','action'=>'sportsdraw',$convention_season_slug], 'Sports & Elimination Draw', 'Bracket and draw report', 'fa-trophy', 'sr-c-red'); ?>
            </div>
        </div>
      </div>
    </section>
</div>
  