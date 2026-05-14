<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div class="content-wrapper">
    <section class="content-header">
        <h1><i class="fa fa-university"></i> Add Conference</h1>
        <ol class="breadcrumb">
            <li><a href="/admin/admins/conference"><i class="fa fa-university"></i> Conference Portal</a></li>
            <li><a href="/admin/admins/list-conferences">All Conferences</a></li>
            <li class="active">Add Conference</li>
        </ol>
    </section>

    <section class="content">
        <?php echo $this->Flash->render(); ?>

        <?php echo $this->Form->create($convention, ['url' => ['controller' => 'admins', 'action' => 'addConference']]); ?>

        <div class="row">

            <!-- Left column -->
            <div class="col-md-8">

                <!-- Conference Details -->
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-info-circle"></i> Conference Details</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <label for="name">Conference Name <span class="text-danger">*</span></label>
                            <?php echo $this->Form->text('name', ['class' => 'form-control', 'placeholder' => 'e.g., Pacific Educators Conference 2026', 'required' => true, 'id' => 'name']); ?>
                        </div>
                        <div class="form-group">
                            <label for="address">Venue / Address</label>
                            <?php echo $this->Form->textarea('address', ['class' => 'form-control', 'placeholder' => 'e.g., Vodafone Arena, Suva, Fiji', 'rows' => 2, 'id' => 'address']); ?>
                        </div>
                        <div class="form-group">
                            <label for="season_id">Link to Season</label>
                            <?php echo $this->Form->select('season_id', ['0' => '-- Select Season (optional) --'] + $seasons, ['class' => 'form-control', 'id' => 'season_id']); ?>
                        </div>
                    </div>
                </div>

                <!-- Registration Window -->
                <div class="box box-info">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-calendar"></i> Registration Window</h3>
                        <small class="pull-right text-muted" style="line-height:34px;">Leave blank for no date restriction</small>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Opens</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <?php echo $this->Form->text('registration_start_date', ['class' => 'form-control datepicker', 'placeholder' => 'Select date…', 'autocomplete' => 'off', 'id' => 'reg_start']); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Closes</label>
                                    <div class="input-group">
                                        <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
                                        <?php echo $this->Form->text('registration_end_date', ['class' => 'form-control datepicker', 'placeholder' => 'Select date…', 'autocomplete' => 'off', 'id' => 'reg_end']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Fee -->
                <div class="box box-success">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-dollar"></i> Registration Fee</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Currency</label>
                                    <?php echo $this->Form->select('currency', [
                                        'FJD' => 'FJD — Fijian Dollar',
                                        'AUD' => 'AUD — Australian Dollar',
                                        'NZD' => 'NZD — New Zealand Dollar',
                                        'PGK' => 'PGK — Papua New Guinean Kina',
                                        'VUV' => 'VUV — Vanuatu Vatu',
                                        'TOP' => 'TOP — Tongan Paʻanga',
                                        'SBD' => 'SBD — Solomon Islands Dollar',
                                        'IDR' => 'IDR — Indonesian Rupiah',
                                    ], ['class' => 'form-control', 'default' => 'FJD', 'id' => 'currency']); ?>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label>Fee per Delegate</label>
                                    <div class="input-group">
                                        <span class="input-group-addon currency-symbol">FJD</span>
                                        <?php echo $this->Form->text('student_registration_fees', ['class' => 'form-control', 'placeholder' => '0.00', 'id' => 'reg_fee']); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="box-footer" style="background:transparent; border:none; padding:0 0 20px;">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fa fa-save"></i> Add Conference
                    </button>
                    <?php echo $this->Html->link('<i class="fa fa-times"></i> Cancel', ['action' => 'listConferences'], ['escape' => false, 'class' => 'btn btn-default btn-lg']); ?>
                </div>

            </div>

            <!-- Right column: tips -->
            <div class="col-md-4">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title"><i class="fa fa-lightbulb-o"></i> Quick Tips</h3>
                    </div>
                    <div class="box-body">
                        <dl class="dl-horizontal" style="font-size:13px;">
                            <dt>Status</dt>
                            <dd>New conferences are set to <span class="label label-success">Active</span> automatically.</dd>
                        </dl>
                        <dl class="dl-horizontal" style="font-size:13px;">
                            <dt>Season</dt>
                            <dd>Linking a season lets you track the conference by year and configure registration dates.</dd>
                        </dl>
                        <dl class="dl-horizontal" style="font-size:13px;">
                            <dt>Dates</dt>
                            <dd>If no registration window is set, registrations remain open with no date restriction.</dd>
                        </dl>
                        <dl class="dl-horizontal" style="font-size:13px;">
                            <dt>Fee</dt>
                            <dd>Enter 0.00 for a free conference.</dd>
                        </dl>
                    </div>
                </div>
            </div>

        </div>

        <?php echo $this->Form->end(); ?>
    </section>
</div>

<script>
$(function () {
    $('#currency').on('change', function () {
        $('.currency-symbol').text($(this).val());
    });
    flatpickr('.datepicker', {
        dateFormat: 'Y-m-d',
        allowInput: true
    });
});
</script>
