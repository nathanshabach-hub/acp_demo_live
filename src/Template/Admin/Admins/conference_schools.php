<div class="content-wrapper">
    <section class="content-header">
        <h1>Schools Registered for Active Conference</h1>
        <ol class="breadcrumb">
            <li><a href="/admin/admins/conference"><i class="fa fa-university"></i> Conference Portal</a></li>
            <li class="active">Schools Registered</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            Schools Registered
                            <?php if (!$activeConferences->isEmpty()): ?>
                                &mdash;
                                <?php foreach ($activeConferences as $conf): ?>
                                    <span class="label label-success"><?php echo h($conf->name); ?></span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </h3>
                    </div>
                    <div class="box-body">
                        <?php echo $this->Flash->render(); ?>
                        <?php if ($registrations->isEmpty()): ?>
                            <p class="text-muted">No schools have registered for the active conference yet.</p>
                        <?php else: ?>
                        <table class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>City / Country</th>
                                    <th>Season Year</th>
                                    <th>Registered On</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($registrations as $reg): ?>
                                <tr>
                                    <td><?php echo $i++; ?></td>
                                    <td>
                                        <?php
                                        if (!empty($reg->Users)) {
                                            echo h(trim(($reg->Users['first_name'] ?? '') . ' ' . ($reg->Users['last_name'] ?? '')));
                                        } else {
                                            echo 'User #' . $reg->user_id;
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo h($reg->Users['email_address'] ?? '—'); ?></td>
                                    <td>
                                        <?php
                                        $city = $reg->Users['bill_to_city'] ?? '';
                                        $country = $reg->Users['bill_to_country'] ?? '';
                                        echo h(implode(', ', array_filter([$city, $country])) ?: '—');
                                        ?>
                                    </td>
                                    <td><?php echo h($reg->season_year); ?></td>
                                    <td><?php echo !empty($reg->created) ? date('d M Y', strtotime($reg->created)) : '—'; ?></td>
                                    <td>
                                        <?php if ($reg->status == 1): ?>
                                            <span class="label label-success">Active</span>
                                        <?php else: ?>
                                            <span class="label label-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                    <div class="box-footer">
                        <?php echo $this->Html->link('<i class="fa fa-arrow-left"></i> Back to Dashboard', ['action' => 'conference'], ['escape' => false, 'class' => 'btn btn-default btn-sm']); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
