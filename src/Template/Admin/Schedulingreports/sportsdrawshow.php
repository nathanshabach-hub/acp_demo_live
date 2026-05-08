<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Sports &amp; Elimination Draw
        <?php if ($eventD) { ?> &mdash; <?php echo h($eventD->event_name); ?><?php } ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> Dashboard', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons', ['controller'=>'conventions', 'action'=>'seasons', $convention_slug], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('Sports Draw', ['controller'=>'schedulingreports', 'action'=>'sportsdraw', $convention_season_slug], ['escape'=>false]);?></li>
          <li class="active">Draw</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="admin_search">
                <div class="admin_asearch">
                    <div class="add_new_record">
                        <?php echo $this->Html->link('<i class="fa fa-print"></i> Print / Save PDF', ['controller'=>'schedulingreports', 'action'=>'sportsdrawprint', $convention_season_slug, $event_id], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']); ?>
                        <?php echo $this->Html->link('Back', ['controller'=>'schedulingreports', 'action'=>'sportsdraw', $convention_season_slug], ['class'=>'btn btn-warning']); ?>
                    </div>
                </div>
            </div>

            <div class="m_content" style="padding:16px;">
                <?php echo $this->element('Admin/Schedulingreports/sportsdraw_bracket'); ?>
            </div>
        </div>
    </section>
</div>
