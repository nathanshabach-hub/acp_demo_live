<div class="content-wrapper conference-page">
    <section class="content-header conference-header">
        <h1>Add Conference</h1>
        <p>Create a new conference for schools to register</p>
    </section>

    <section class="content conference-shell">
        <div class="row">
            <div class="col-md-8">
                <div class="conference-panel">
                    <div class="conference-panel-title">Conference Details</div>
                    
                    <div class="ersu_message">
                        <?php echo $this->Flash->render(); ?>
                    </div>

                    <?php echo $this->Form->create($convention, ['url' => ['controller' => 'admins', 'action' => 'addConference']]); ?>
                    
                    <div class="form-group">
                        <?php echo $this->Form->label('name', 'Conference Name'); ?>
                        <?php echo $this->Form->text('name', ['class' => 'form-control', 'placeholder' => 'e.g., Papua New Guinea Conference 2026', 'required' => true]); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $this->Form->label('address', 'Address/Venue'); ?>
                        <?php echo $this->Form->textarea('address', ['class' => 'form-control', 'placeholder' => 'e.g., Convention Center, Port Moresby', 'rows' => 3]); ?>
                    </div>

                    <div class="form-group">
                        <?php echo $this->Form->label('season_id', 'Link to Season'); ?>
                        <?php echo $this->Form->select('season_id', ['0' => '-- Select Season --'] + $seasons, ['class' => 'form-control']); ?>
                        <small class="form-text text-muted">Select the season this conference belongs to (optional)</small>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Add Conference
                        </button>
                        <?php echo $this->Html->link('Cancel', ['action' => 'conference'], ['class' => 'btn btn-secondary']); ?>
                    </div>

                    <?php echo $this->Form->end(); ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="conference-panel">
                    <div class="conference-panel-title">Information</div>
                    <div class="conference-info">
                        <p><strong>Conference Type:</strong> Online Student Conference</p>
                        <p><strong>Default Status:</strong> Active</p>
                        <p>When you create a conference, it will be:</p>
                        <ul>
                            <li>Set as a conference (type 1)</li>
                            <li>Automatically activated</li>
                            <li>Available for schools to register</li>
                            <li>Linked to the selected season (if chosen)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    display: block;
}

.form-group small {
    display: block;
    margin-top: 4px;
}

.conference-info {
    padding: 15px;
    background-color: #f5f5f5;
    border-radius: 4px;
}

.conference-info ul {
    margin: 10px 0 0 20px;
}

.conference-info li {
    margin-bottom: 8px;
}
</style>
