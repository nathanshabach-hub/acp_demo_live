<div class="content-wrapper conference-page">
    <section class="content-header conference-header">
        <h1>Conference Dashboard</h1>
        <p>Welcome back, here is your overview. <?php echo h($conferenceLabel); ?></p>
        <?php echo $this->Html->link('+ Add Conference', ['action' => 'addConference'], ['class' => 'btn btn-primary', 'style' => 'margin-top: 10px;']); ?>
    </section>

    <section class="content conference-shell">
        <div class="conference-panel conference-quick-links">
            <div class="conference-panel-title">Portal Quick Links</div>
            <div class="conference-quick-links-grid">
                <?php echo $this->Html->link('Dashboard', ['controller' => 'admins', 'action' => 'conference'], ['class' => 'conference-quick-link']); ?>
                <?php echo $this->Html->link('Manage Registrations', ['controller' => 'conventionregistrations', 'action' => 'index'], ['class' => 'conference-quick-link']); ?>
                <?php echo $this->Html->link('Create Certificates', ['controller' => 'judgeevaluations', 'action' => 'index'], ['class' => 'conference-quick-link']); ?>
                <?php echo $this->Html->link('Feedback Summary', ['controller' => 'judgeevaluations', 'action' => 'index'], ['class' => 'conference-quick-link']); ?>
                <?php echo $this->Html->link('Manage Schools', ['controller' => 'users', 'action' => 'index'], ['class' => 'conference-quick-link']); ?>
                <?php echo $this->Html->link('Manage Conferences', ['controller' => 'admins', 'action' => 'listConferences'], ['class' => 'conference-quick-link']); ?>
                <?php echo $this->Html->link('Create Invoices', ['controller' => 'transactions', 'action' => 'index'], ['class' => 'conference-quick-link']); ?>
                <?php echo $this->Html->link('Feedback Analytics', ['controller' => 'judgeevaluations', 'action' => 'index'], ['class' => 'conference-quick-link']); ?>
            </div>
        </div>

        <div class="conference-metrics row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="small-box bg-olive">
                    <div class="inner">
                        <h3><?php echo (int)$totalRegistrations; ?></h3>
                        <p>Total Registrations</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-newspaper-o"></i>
                    </div>
                    <?php echo $this->Html->link('<i class="fa fa-arrow-circle-right"></i> More info', ['controller' => 'admins', 'action' => 'conferenceRegistrations'], ['class' => 'small-box-footer', 'escape' => false]); ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="small-box bg-blue">
                    <div class="inner">
                        <h3><?php echo (int)$schoolsEnrolled; ?></h3>
                        <p>Schools Registered</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-bank"></i>
                    </div>
                    <?php echo $this->Html->link('<i class="fa fa-arrow-circle-right"></i> More info', ['controller' => 'admins', 'action' => 'conferenceSchools'], ['class' => 'small-box-footer', 'escape' => false]); ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo (int)$activeConferences; ?></h3>
                        <p>Active Conferences</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-university"></i>
                    </div>
                    <?php echo $this->Html->link('<i class="fa fa-arrow-circle-right"></i> More info', ['controller' => 'admins', 'action' => 'listConferences'], ['class' => 'small-box-footer', 'escape' => false]); ?>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-xs-6">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo (int)$attendanceRate; ?>%</h3>
                        <p>Full Attendance Rate</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-line-chart"></i>
                    </div>
                    <?php echo $this->Html->link('<i class="fa fa-arrow-circle-right"></i> More info', ['controller' => 'conventionregistrations', 'action' => 'index'], ['class' => 'small-box-footer', 'escape' => false]); ?>
                </div>
            </div>
        </div>

        <div class="row conference-panels">
            <div class="col-lg-6">
                <div class="conference-panel">
                    <div class="conference-panel-title">Registration Trend</div>
                    <?php
                    $maxTrend = max(array_merge([1], $trendValues));
                    ?>
                    <div class="conference-trend">
                        <?php foreach ($trendValues as $idx => $value) { ?>
                            <div class="conference-trend-col">
                                <div class="conference-trend-bar" style="height: <?php echo (int)round(($value / $maxTrend) * 180); ?>px;"></div>
                                <div class="conference-trend-value"><?php echo (int)$value; ?></div>
                                <div class="conference-trend-label"><?php echo h($trendLabels[$idx] ?? ''); ?></div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="conference-panel">
                    <div class="conference-panel-title">Schools by Location</div>
                    <div class="conference-locations">
                        <?php
                        $maxLocation = 1;
                        foreach ($locationData as $loc) {
                            if ((int)$loc['value'] > $maxLocation) {
                                $maxLocation = (int)$loc['value'];
                            }
                        }
                        ?>
                        <?php if (!empty($locationData)) { ?>
                            <?php foreach ($locationData as $loc) { ?>
                                <div class="conference-location-row">
                                    <div class="conference-location-text"><?php echo h($loc['label']); ?></div>
                                    <div class="conference-location-track">
                                        <div class="conference-location-bar" style="width: <?php echo (int)round(($loc['value'] / $maxLocation) * 100); ?>%;"></div>
                                    </div>
                                    <div class="conference-location-value"><?php echo (int)$loc['value']; ?></div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="conference-empty">No location data available.</div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="row conference-panels">
            <div class="col-lg-7">
                <div class="conference-panel">
                    <div class="conference-panel-title">School Invoice Status</div>
                    <div class="conference-table-wrap">
                        <table class="conference-table">
                            <thead>
                                <tr>
                                    <th>School</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($invoiceStatusRows)) { ?>
                                    <?php foreach ($invoiceStatusRows as $row) { ?>
                                        <tr>
                                            <td><?php echo h($row['name']); ?></td>
                                            <td>
                                                <span class="conference-chip <?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                                                    <?php echo h($row['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr>
                                        <td colspan="2" class="conference-empty">No invoice data available.</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="conference-panel">
                    <div class="conference-panel-title">Recent Registrations</div>
                    <div class="conference-recent-list">
                        <?php if (!$recentRegistrations->isEmpty()) { ?>
                            <?php foreach ($recentRegistrations as $reg) {
                                $name = 'Unknown User';
                                $role = 'Registration';
                                if (!empty($reg->Users)) {
                                    $name = trim((string)$reg->Users['first_name'] . ' ' . (string)$reg->Users['last_name']);
                                    $role = (string)$reg->Users['user_type'];
                                }
                            ?>
                                <div class="conference-recent-item">
                                    <div class="conference-recent-avatar"><?php echo h(strtoupper(substr($name, 0, 1))); ?></div>
                                    <div class="conference-recent-content">
                                        <div class="conference-recent-name"><?php echo h($name); ?></div>
                                        <div class="conference-recent-role"><?php echo h($role); ?></div>
                                    </div>
                                    <div class="conference-recent-date"><?php echo !empty($reg->created) ? safe_date('d M', strtotime($reg->created)) : '-'; ?></div>
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <div class="conference-empty">No recent registrations found.</div>
                        <?php } ?>
                    </div>
                </div>

                <div class="conference-panel conference-links">
                    <div class="conference-panel-title">Portal-Style Navigation</div>

                    <div class="conference-link-group">
                        <div class="conference-link-group-title">Dashboard</div>
                        <ul>
                            <li><?php echo $this->Html->link('Dashboard', ['controller' => 'admins', 'action' => 'conference']); ?></li>
                        </ul>
                    </div>

                    <div class="conference-link-group">
                        <div class="conference-link-group-title">Registration</div>
                        <ul>
                            <li><?php echo $this->Html->link('Manage Registrations', ['controller' => 'conventionregistrations', 'action' => 'index']); ?></li>
                            <li><?php echo $this->Html->link('Create Certificates', ['controller' => 'judgeevaluations', 'action' => 'index']); ?></li>
                            <li><?php echo $this->Html->link('Feedback Summary', ['controller' => 'judgeevaluations', 'action' => 'index']); ?></li>
                        </ul>
                    </div>

                    <div class="conference-link-group">
                        <div class="conference-link-group-title">School</div>
                        <ul>
                            <li><?php echo $this->Html->link('Manage Schools', ['controller' => 'users', 'action' => 'index']); ?></li>
                        </ul>
                    </div>

                    <div class="conference-link-group">
                        <div class="conference-link-group-title">Conference</div>
                        <ul>
                            <li><?php echo $this->Html->link('Manage Conferences', ['controller' => 'admins', 'action' => 'listConferences']); ?></li>
                        </ul>
                    </div>

                    <div class="conference-link-group">
                        <div class="conference-link-group-title">Invoicing</div>
                        <ul>
                            <li><?php echo $this->Html->link('Create Invoices', ['controller' => 'transactions', 'action' => 'index']); ?></li>
                        </ul>
                    </div>

                    <div class="conference-link-group">
                        <div class="conference-link-group-title">Analytics</div>
                        <ul>
                            <li><?php echo $this->Html->link('Feedback Analytics', ['controller' => 'judgeevaluations', 'action' => 'index']); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.conference-page {
    background: #ecf0f5;
    min-height: calc(100vh - 50px);
}

.conference-header h1 {
    color: #333;
    font-weight: 700;
}

.conference-header p {
    color: #666;
    margin: 6px 0 0;
}

.conference-shell {
    padding-top: 10px;
}

.conference-quick-links {
    margin-bottom: 18px;
    background: #fff;
    border: 1px solid #d2d6de;
    border-top: 3px solid #3c8dbc;
    border-radius: 0;
    box-shadow: none;
}

.conference-quick-links-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 10px;
}

.conference-quick-link {
    display: block;
    text-align: center;
    border: 1px solid #d2d6de;
    border-radius: 3px;
    padding: 10px 8px;
    color: #444;
    background: #f7f7f7;
    font-size: 12px;
    font-weight: 600;
}

.conference-quick-link:hover {
    color: #111;
    background: #f0f0f0;
    text-decoration: none;
}

.conference-panel {
    background: #fff;
    border: 1px solid #d2d6de;
    border-top: 3px solid #00a65a;
    border-radius: 0;
    box-shadow: none;
}

.conference-panel {
    padding: 16px;
    margin-bottom: 18px;
}

.conference-panel-title {
    color: #444;
    font-weight: 600;
    margin-bottom: 14px;
    letter-spacing: 0.3px;
}

.conference-trend {
    height: 230px;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 8px;
}

.conference-trend-col {
    flex: 1;
    text-align: center;
}

.conference-trend-bar {
    width: 100%;
    max-width: 56px;
    margin: 0 auto;
    border-radius: 10px 10px 4px 4px;
    background: linear-gradient(180deg, #5faee3 0%, #3c8dbc 100%);
    min-height: 4px;
}

.conference-trend-value,
.conference-trend-label {
    color: #555;
    font-size: 12px;
    margin-top: 6px;
}

.conference-locations {
    max-height: 250px;
    overflow-y: auto;
    padding-right: 4px;
}

.conference-location-row {
    display: grid;
    grid-template-columns: 1.4fr 2fr 40px;
    gap: 10px;
    align-items: center;
    margin-bottom: 8px;
}

.conference-location-text,
.conference-location-value {
    color: #555;
    font-size: 12px;
}

.conference-location-track {
    background: #e9ecef;
    border-radius: 999px;
    height: 10px;
    overflow: hidden;
}

.conference-location-bar {
    background: linear-gradient(90deg, #3c8dbc, #00a65a);
    height: 100%;
}

.conference-table-wrap {
    max-height: 320px;
    overflow-y: auto;
}

.conference-table {
    width: 100%;
    border-collapse: collapse;
}

.conference-table th,
.conference-table td {
    border-bottom: 1px solid #f4f4f4;
    padding: 10px 8px;
    color: #444;
    font-size: 13px;
}

.conference-table th {
    color: #111;
    position: sticky;
    top: 0;
    background: #fff;
}

.conference-chip {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
}

.conference-chip.paid {
    background: rgba(46, 199, 112, 0.2);
    color: #39d78e;
}

.conference-chip.sent {
    background: rgba(67, 175, 247, 0.2);
    color: #54c9ff;
}

.conference-chip.not-invoiced {
    background: rgba(244, 190, 66, 0.2);
    color: #ffd366;
}

.conference-recent-list {
    display: grid;
    gap: 10px;
}

.conference-recent-item {
    display: grid;
    grid-template-columns: 34px 1fr auto;
    align-items: center;
    gap: 10px;
    border-bottom: 1px solid #f4f4f4;
    padding-bottom: 8px;
}

.conference-recent-avatar {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #d9edf7;
    color: #31708f;
    text-align: center;
    line-height: 34px;
    font-weight: 700;
}

.conference-recent-name {
    color: #333;
    font-weight: 600;
    font-size: 13px;
}

.conference-recent-role,
.conference-recent-date {
    color: #777;
    font-size: 12px;
}

.conference-links ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.conference-link-group {
    border-bottom: 1px solid #f4f4f4;
    padding-bottom: 8px;
    margin-bottom: 10px;
}

.conference-link-group:last-child {
    border-bottom: 0;
    margin-bottom: 0;
    padding-bottom: 0;
}

.conference-link-group-title {
    color: #777;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.6px;
    text-transform: uppercase;
    margin-bottom: 6px;
}

.conference-links li {
    margin-bottom: 8px;
}

.conference-links a {
    color: #3c8dbc;
}

.conference-links a:hover {
    color: #367fa9;
}

.conference-empty {
    color: #777;
    font-size: 13px;
    padding: 6px 0;
}

@media (max-width: 991px) {
    .conference-quick-links-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .conference-location-row {
        grid-template-columns: 1fr;
        gap: 6px;
    }
}
</style>
