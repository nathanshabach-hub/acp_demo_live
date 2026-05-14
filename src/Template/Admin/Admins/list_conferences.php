<div class="content-wrapper">
    <section class="content-header">
        <h1>Conferences</h1>
        <ol class="breadcrumb">
            <li><a href="/admin/admins/conference"><i class="fa fa-university"></i> Conference Portal</a></li>
            <li class="active">All Conferences</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">All Conferences</h3>
                        <div class="box-tools pull-right">
                            <?php echo $this->Html->link('<i class="fa fa-plus"></i> Add Conference', ['action' => 'addConference'], ['escape' => false, 'class' => 'btn btn-primary btn-sm']); ?>
                        </div>
                    </div>
                    <div class="box-body">
                        <?php echo $this->Flash->render(); ?>
                        <?php if (!$conferences->isEmpty()): ?>
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Conference Name</th>
                                    <th>Venue / Address</th>
                                    <th>Registrations</th>
                                    <th>Status</th>
                                    <th style="width:130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($conferences as $conf): ?>
                                <tr>
                                    <td><strong><?php echo h($conf->name); ?></strong></td>
                                    <td><?php echo h($conf->address ?? '—'); ?></td>
                                    <td><?php echo (int)($regCounts[$conf->id] ?? 0); ?></td>
                                    <td>
                                        <?php if ($conf->status == 1): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-default">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo $this->Html->link('<i class="fa fa-eye"></i>', ['action' => 'viewConference', $conf->slug], ['escape' => false, 'class' => 'btn btn-info btn-xs', 'title' => 'View']); ?>
                                        <?php echo $this->Form->postLink('<i class="fa fa-trash"></i>', ['action' => 'deleteConference', $conf->slug], ['escape' => false, 'class' => 'btn btn-danger btn-xs', 'title' => 'Delete', 'confirm' => 'Are you sure you want to delete "' . h($conf->name) . '"? This cannot be undone.']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php else: ?>
                        <p class="text-muted">No conferences have been added yet. <a href="/admin/admins/add-conference">Add one now</a>.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
