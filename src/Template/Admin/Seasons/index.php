<?php echo $this->Html->script('ajax-pagging.js'); ?>
<?php
$isConferenceScope = ($this->request->getQuery('scope') === 'conference');
$indexUrl = ['controller' => 'seasons', 'action' => 'index'];
$addUrl = ['controller' => 'seasons', 'action' => 'add'];
if ($isConferenceScope) {
    $indexUrl['?'] = ['scope' => 'conference'];
    $addUrl['?'] = ['scope' => 'conference'];
}
?>
<div class="content-wrapper">
    <section class="content-header">
      <h1>
                 <?php echo $isConferenceScope ? 'Manage Conference Years' : 'Manage Seasons'; ?>
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', array('controller'=>'admins', 'action'=>'dashboard'), array('escape'=>false));?></li>
                    <li class="active"> <?php echo $isConferenceScope ? 'Conference Years List' : 'Seasons List'; ?> </li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>
            <div class="admin_search">
                <?php echo $this->Form->create(Null, ['id'=>'adminSearch']); ?>
                    <div class="form-group align_box dtpickr_inputs">
                       <span class="hints">Search by Season Year</span>
                       <span class="hint">
                           <?php echo $this->Form->input('Seasons.keyword', ['label'=>false, 'type'=>'text',  'div'=>false, 'class'=>'form-control', 'placeholder'=>'Search by Season Year']); ?>
                       </span>
                      
                       <div class="admin_asearch">
                            <div class="ad_s ajshort"> <?php echo $this->Form->button('Search', ['class'=>'btn btn-info admin_ajax_search', 'type'=>'button']); ?></div>
                            <div class="ad_cancel"> <?php echo $this->Html->link('Clear Search', $indexUrl, ['escape'=>false, 'class'=>'btn btn-default canlcel_le']);?></div>
                       </div>
                    </div>
                <?php echo $this->Form->end(); ?>
                <div class="add_new_record"><?php echo $this->Html->link('<i class="fa fa-plus"></i> ' . ($isConferenceScope ? 'Add Conference Year' : 'Add Season'), $addUrl, ['escape'=>false, 'class'=>'btn btn-default']);?></div>
            </div>
            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Seasons/index"); ?>
            </div>
            
        </div>
    </section>
</div>
