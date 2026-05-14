<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.cs-toolbar { display:flex; flex-wrap:wrap; align-items:center; gap:10px; padding:14px; background:#f6f8fb; border:1px solid #e3e6ec; border-radius:8px; margin-bottom:16px; }
.cs-toolbar .cs-filter { flex:1 1 240px; min-width:200px; }
.cs-toolbar .cs-filter select { width:100%; }
.cs-toolbar .cs-spacer { flex:1; }
.cs-toolbar .btn { font-size:12px; }
</style>
<div class="content-wrapper">
    <section class="content-header">
      <h1>Manage Seasons <small style="color:#7b8794;">— <?php echo h($conventionD->name); ?></small></h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Manage Seasons</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

            <?php echo $this->Form->create(null, ['id'=>'adminSearch']); ?>
            <div class="cs-toolbar">
                <div class="cs-filter">
                    <?php echo $this->Form->select('Conventionseasons.season_id', $seasonsDD, ['id' => 'season_id', 'label' => false, 'div' => false, 'class' => 'form-control required', 'empty' => 'Filter by season year', 'autocomplete' => 'off']); ?>
                </div>
                <?php echo $this->Form->button('<i class="fa fa-search"></i> Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button', 'escapeTitle'=>false]); ?>
                <?php echo $this->Html->link('<i class="fa fa-times"></i> Clear', ['controller'=>'conventions', 'action'=>'seasons',$slug], ['escape'=>false, 'class'=>'btn btn-default']);?>
                <div class="cs-spacer"></div>
                <?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Season', ['controller'=>'conventions', 'action'=>'addseason',$slug], ['escape'=>false, 'class'=>'btn btn-success']);?>
            </div>
            <?php echo $this->Form->end(); ?>

            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Conventions/seasons"); ?>
            </div>
        </div>
    </section>
</div>
<script>
$(function () {
    if ($.fn.select2) { $('#season_id').select2({ allowClear: true, placeholder: 'Filter by season year' }); }
});
</script>
