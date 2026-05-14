<?php echo $this->Html->script('ajax-pagging.js'); ?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>Event Submissions</h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-newspaper-o"></i> Convention Registrations ', ['controller'=>'conventionregistrations', 'action'=>'index'], ['escape'=>false]);?></li>
          <li class="active">Event Submissions</li>
      </ol>
    </section>

    <section class="content">
        <?php echo $this->element('Admin/Conventionregistrations/registrant_header'); ?>
        <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title"><i class="fa fa-database"></i> Event Submissions</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Eventsubmissions/index"); ?>
            </div>
        </div>
    </section>
</div>
