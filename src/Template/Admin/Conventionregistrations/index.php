<?php echo $this->Html->script('ajax-pagging.js'); ?>
<style>
.cr-toolbar { display:flex; flex-wrap:wrap; gap:10px; padding:14px 16px; background:#f6f8fb; border-bottom:1px solid #e3e6ec; align-items:center; }
.cr-toolbar .cr-tb-field { flex:1 1 220px; min-width:180px; }
.cr-toolbar .cr-tb-field label { display:block; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#7b8794; margin:0 0 4px 2px; }
.cr-toolbar .cr-tb-actions { display:flex; gap:6px; align-items:flex-end; }
.cr-stat-row { display:flex; flex-wrap:wrap; gap:10px; padding:14px 16px 0; }
.cr-stat { flex:1 1 160px; background:#fff; border:1px solid #e3e6ec; border-radius:6px; padding:12px 14px; display:flex; align-items:center; gap:12px; }
.cr-stat .cr-ico { width:38px; height:38px; border-radius:50%; color:#fff; display:inline-flex; align-items:center; justify-content:center; font-size:16px; }
.cr-stat .cr-num { font-size:20px; font-weight:700; line-height:1; }
.cr-stat .cr-lbl { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#7b8794; margin-top:3px; }
</style>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Manage Convention Registrations
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
          <li class="active"> Convention Registrations List </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

            <div class="cr-toolbar">
                <?php echo $this->Form->create(null, ['id'=>'adminSearch', 'type'=>'get']); ?>
                    <div style="display:flex; flex-wrap:wrap; gap:12px; width:100%; align-items:flex-end;">
                        <div class="cr-tb-field">
                            <label>Convention</label>
                            <?php echo $this->Form->select('Conventionregistrations.convention_id', $conventionsDD, ['id' => 'convention_id', 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'empty' => 'All Conventions']); ?>
                        </div>
                        <div class="cr-tb-field">
                            <label>Season</label>
                            <?php echo $this->Form->select('Conventionregistrations.season_year', $seasonsDD, ['id' => 'season_year', 'label' => false, 'div' => false, 'class' => 'form-control', 'autocomplete' => 'off', 'empty' => 'All Seasons']); ?>
                        </div>
                        <div class="cr-tb-actions">
                            <?php echo $this->Form->button('<i class="fa fa-search"></i> Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button', 'escapeTitle'=>false]); ?>
                            <?php echo $this->Html->link('<i class="fa fa-times"></i> Clear', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false, 'class'=>'btn btn-default']);?>
                        </div>
                    </div>
                <?php echo $this->Form->end(); ?>
            </div>

            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Conventionregistrations/index"); ?>
            </div>

        </div>
    </section>
</div>
<script>
$(document).ready(function(){
    $('#convention_id').select2({width:'100%'});
    $('#season_year').select2({width:'100%'});
});
</script>
