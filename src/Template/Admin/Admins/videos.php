<script type="text/javascript">
    $(document).ready(function() {
        $("#adminForm").validate();
    });
</script>

<div class="content-wrapper">
    <section class="content-header">
      <h1>
         Dashboard Videos
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><a href="javascript:void(0);"><i class="fa fa-cogs"></i> Configuration</a></li>
          <li class="active">Videos</li>
      </ol>
    </section>

    <section class="content">
     <div class="box box-info">
            <div class="box-header with-border">
                <h3 class="box-title">Manage User Dashboard Video Links</h3>
            </div>
            <div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
            <?php echo $this->Form->create(NULL, ['id'=>'adminForm', 'autocomplete'=>'off']); ?>
                <div class="form-horizontal">
                    <div class="box-body">
                        <p><em>Paste a full YouTube URL (watch/share/embed) or a YouTube video ID. Leave blank to hide a slot.</em></p>

                        <?php for ($i = 1; $i <= 9; $i++) : ?>
                        <div class="form-group">
                            <label class="col-sm-2 control-label">Video <?php echo $i; ?> Link</label>
                            <div class="col-sm-10">
                                <?php echo $this->Form->input('Settings.video_' . $i, ['label' => false, 'type' => 'text', 'div' => false, 'class' => 'form-control', 'placeholder' => 'https://www.youtube.com/watch?v=...', 'autocomplete' => 'off', 'value' => isset($videoLinks[$i - 1]) ? $videoLinks[$i - 1] : '']); ?>
                            </div>
                        </div>
                        <?php endfor; ?>

                    <div class="box-footer">
                        <label class="col-sm-2 control-label" for="inputPassword3">&nbsp;</label>
                        <?php echo $this->Form->button('Save Video Links', ['type'=>'submit', 'class' => 'btn btn-info', 'div'=>false]); ?>
                        <?php echo $this->Html->link('Cancel', ['action' => 'dashboard'], ['class'=>'btn btn-default canlcel_le']); ?>
                    </div>
                  </div>
                </div>
            <?php echo $this->Form->end(); ?>
          </div>
    </section>
</div>
