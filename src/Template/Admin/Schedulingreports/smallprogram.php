<div class="content-wrapper">
    <section class="content-header">
      <h1>
        Small Program - [Convention - <?php echo $conventionSD->Conventions['name']; ?>]&nbsp;&nbsp;&nbsp;&nbsp;
          [Season Year - <?php echo $conventionSD->season_year; ?>]
      </h1>
      <ol class="breadcrumb">
          <li><?php echo $this->Html->link('<i class="fa fa-dashboard"></i> <span>Dashboard</span> ', ['controller'=>'admins', 'action'=>'dashboard'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Conventions ', ['controller'=>'conventions', 'action'=>'index'], ['escape'=>false]);?></li>
          <li><?php echo $this->Html->link('<i class="fa fa-bars"></i> Seasons ', ['controller'=>'conventions', 'action'=>'seasons',$convention_slug], ['escape'=>false]);?></li>
          <li class="active">Small Program</li>
      </ol>
    </section>

    <section class="content">
        <div class="box box-info">
            <div class="ersu_message"> <?php echo $this->Flash->render() ?></div>

            <div class="admin_search">
               <div class="admin_asearch">
                <div class="add_new_record">
                <?php echo $this->Html->link('<i class="fa fa-print"></i> Print / Save PDF', ['controller'=>'schedulingreports', 'action'=>'smallprogramprint',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-default', 'target'=>'_blank']);?>
                <?php echo $this->Html->link('Back', ['controller'=>'schedulings', 'action'=>'reports',$convention_season_slug], ['escape'=>false, 'class'=>'btn btn-warning']);?>
                </div>
            </div>
            </div>

            <div class="m_content" id="listID">
                <?php echo $this->element("Admin/Schedulingreports/smallprogram_booklet"); ?>
            </div>

            <div class="box box-default" style="margin:20px;">
                <div class="box-header with-border">
                    <h3 class="box-title">Edit Small Program Notes</h3>
                </div>
                <?php echo $this->Form->create(null, ['url' => ['controller'=>'schedulingreports', 'action'=>'smallprogram', $convention_season_slug], 'class' => 'form-horizontal']); ?>
                <div class="box-body">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Intro Day Heading</label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->text('Smallprogramnotes.intro_day_label', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['intro_day_label'] ?? '']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Intro Entries</label>
                        <div class="col-sm-10">
                            <table class="table table-condensed" id="intro-entries-table" style="margin-bottom:6px;">
                                <thead>
                                    <tr>
                                        <th style="width:200px;">Time</th>
                                        <th>Description</th>
                                        <th style="width:40px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="intro-entries-body">
                                <?php
                                $introEntriesRaw = trim((string)($smallProgramNotes['intro_entries'] ?? ''));
                                $introRows = array();
                                if ($introEntriesRaw !== '') {
                                    foreach (preg_split('/\r\n|\r|\n/', $introEntriesRaw) as $ln) {
                                        $ln = trim((string)$ln);
                                        if ($ln === '') continue;
                                        $parts = explode('|', $ln, 2);
                                        $introRows[] = array('time' => trim((string)($parts[0] ?? '')), 'text' => trim((string)($parts[1] ?? '')));
                                    }
                                }
                                if (empty($introRows)) {
                                    $introRows[] = array('time' => '', 'text' => '');
                                }
                                foreach ($introRows as $row) { ?>
                                    <tr class="intro-entry-row">
                                        <td><input type="text" name="Smallprogramnotes[intro_time][]" class="form-control input-sm" value="<?php echo h($row['time']); ?>" placeholder="e.g. 4:00 pm - 5:00 pm" /></td>
                                        <td><input type="text" name="Smallprogramnotes[intro_text][]" class="form-control input-sm" value="<?php echo h($row['text']); ?>" placeholder="Description" /></td>
                                        <td><button type="button" class="btn btn-xs btn-danger intro-remove-row" title="Remove row">&times;</button></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-xs btn-success" id="intro-add-row"><i class="fa fa-plus"></i> Add Row</button>
                        </div>
                    </div>
                    <script>
                    (function(){
                        document.getElementById('intro-add-row').addEventListener('click', function(){
                            var tbody = document.getElementById('intro-entries-body');
                            var tr = document.createElement('tr');
                            tr.className = 'intro-entry-row';
                            tr.innerHTML = '<td><input type="text" name="Smallprogramnotes[intro_time][]" class="form-control input-sm" placeholder="e.g. 4:00 pm - 5:00 pm" /></td>'
                                + '<td><input type="text" name="Smallprogramnotes[intro_text][]" class="form-control input-sm" placeholder="Description" /></td>'
                                + '<td><button type="button" class="btn btn-xs btn-danger intro-remove-row" title="Remove row">&times;</button></td>';
                            tbody.appendChild(tr);
                        });
                        document.getElementById('intro-entries-body').addEventListener('click', function(e){
                            if (e.target && e.target.classList.contains('intro-remove-row')) {
                                var row = e.target.closest('tr');
                                if (row) row.parentNode.removeChild(row);
                            }
                        });
                    })();
                    </script>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Dinner Banner</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.dinner_banner', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['dinner_banner'] ?? '']); ?>
                        </div>
                        <label class="col-sm-2 control-label">Evening Rally Time</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.evening_rally_time', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['evening_rally_time'] ?? '']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Evening Rally Label</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.evening_rally_label', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['evening_rally_label'] ?? '']); ?>
                        </div>
                        <label class="col-sm-2 control-label">Offsite Note</label>
                        <div class="col-sm-4">
                            <?php echo $this->Form->text('Smallprogramnotes.offsite_note', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['offsite_note'] ?? '']); ?>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Footer Note</label>
                        <div class="col-sm-10">
                            <?php echo $this->Form->text('Smallprogramnotes.footer_note', ['label'=>false, 'div'=>false, 'class'=>'form-control', 'value'=>$smallProgramNotes['footer_note'] ?? '']); ?>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <label class="col-sm-2 control-label">&nbsp;</label>
                    <?php echo $this->Form->button('Save Notes', ['type'=>'submit', 'class' => 'btn btn-primary']); ?>
                </div>
                <?php echo $this->Form->end(); ?>
            </div>
        </div>
    </section>
</div>
