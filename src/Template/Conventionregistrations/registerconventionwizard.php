<?php
/**
 * Conference Registration Wizard — 4 steps
 */
$adminName  = trim(($userDetails->first_name ?? '') . ' ' . ($userDetails->last_name ?? ''));
$adminEmail = $userDetails->email_address ?? '';
$currency   = (!empty($convSeasonD->currency)) ? $convSeasonD->currency : 'FJD';
$regFee     = (!empty($convSeasonD->student_registration_fees)) ? $convSeasonD->student_registration_fees : null;
?>

<style>
/* ── Hero banner ───────────────────────────────────────────── */
.conf-hero {
    background: linear-gradient(135deg, #1c2452 0%, #2d3a8c 100%);
    color: #fff;
    border-radius: 10px;
    padding: 28px 32px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    gap: 20px;
}
.conf-hero-icon {
    font-size: 40px;
    color: #fff;
    flex-shrink: 0;
}
.conf-hero-body h2 {
    margin: 0 0 8px;
    font-size: 22px;
    font-weight: 700;
    color: #fff;
    text-shadow: 0 1px 3px rgba(0,0,0,.3);
}
.conf-hero-body .hero-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    font-size: 13px;
    color: #d6ddf5;
}
.conf-hero-body .hero-meta span { display: flex; align-items: center; gap: 5px; color: #d6ddf5; }

/* ── Step bar ──────────────────────────────────────────────── */
.wiz-steps {
    display: flex;
    align-items: center;
    margin-bottom: 28px;
}
.wiz-step {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
}
.wiz-step .circle {
    width: 38px; height: 38px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 15px;
    background: #e8ecf4; color: #999;
    border: 2px solid #d0d6e8;
    transition: all .25s;
}
.wiz-step .step-lbl { font-size: 12px; color: #999; line-height: 1.3; }
.wiz-step.active  .circle { background: #1c2452; color: #fff; border-color: #1c2452; }
.wiz-step.active  .step-lbl { color: #1c2452; font-weight: 600; }
.wiz-step.done    .circle { background: #28a745; color: #fff; border-color: #28a745; }
.wiz-step.done    .step-lbl { color: #28a745; }
.wiz-connector { flex: 1; height: 3px; background: #e0e4f0; margin: 0 6px; border-radius: 2px; }
.wiz-connector.done { background: #28a745; }

/* ── Card ──────────────────────────────────────────────────── */
.wiz-card {
    background: #fff;
    border: 1px solid #e0e4f0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(28,36,82,.07);
}
.wiz-card-head {
    background: #f5f7fb;
    border-bottom: 1px solid #e0e4f0;
    padding: 16px 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.wiz-card-head h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #1c2452;
}
.wiz-card-head .step-badge {
    background: #1c2452;
    color: #fff;
    border-radius: 50%;
    width: 26px; height: 26px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; flex-shrink: 0;
}
.wiz-card-body { padding: 24px; }

/* ── Info rows ─────────────────────────────────────────────── */
.info-row {
    display: flex;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f3f8;
    align-items: flex-start;
}
.info-row:last-child { border-bottom: none; }
.info-row .info-lbl {
    min-width: 180px;
    font-size: 12px;
    color: #888;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding-top: 2px;
}
.info-row .info-val { font-size: 14px; color: #222; }
.fee-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eaf7ea;
    color: #1e6e1e;
    border: 1px solid #b8e0b8;
    border-radius: 20px;
    padding: 4px 14px;
    font-weight: 700;
    font-size: 15px;
}
.open-badge {
    display: inline-block;
    background: #d4edda; color: #155724;
    border-radius: 4px; padding: 1px 8px;
    font-size: 12px; font-weight: 600;
    margin-left: 8px;
}

/* ── Confirm summary ───────────────────────────────────────── */
.confirm-box {
    background: #f5f7fb;
    border: 1px solid #dde3f0;
    border-radius: 8px;
    padding: 20px 24px;
    margin-bottom: 20px;
}
.confirm-box .cb-row { display: flex; gap: 12px; padding: 6px 0; font-size: 14px; }
.confirm-box .cb-lbl { min-width: 130px; color: #888; font-weight: 600; font-size: 12px; text-transform: uppercase; padding-top: 2px; }
.confirm-box .cb-val { color: #222; }
.confirm-check-wrap {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #fffbe6;
    border: 1px solid #ffe58f;
    border-radius: 8px;
    padding: 14px 18px;
    margin-top: 18px;
}
.confirm-check-wrap input { width: 18px; height: 18px; margin-top: 2px; cursor: pointer; flex-shrink: 0; }
.confirm-check-wrap label { font-size: 14px; color: #333; cursor: pointer; line-height: 1.5; }

/* ── Navigation ────────────────────────────────────────────── */
.wiz-nav { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
.btn-back { display: inline-block; background: #fff; border: 1px solid #aab0c8; color: #333 !important; border-radius: 6px; padding: 9px 22px; font-size: 14px; cursor: pointer; transition: background .15s; text-decoration: none !important; }
.btn-back:hover { background: #f0f3f8; color: #111 !important; }
.btn-next { display: inline-block; background: #1c2452; color: #fff !important; border: none; border-radius: 6px; padding: 9px 28px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-next:hover { background: #2d3a8c; }
.btn-submit { display: inline-block; background: #28a745; color: #fff !important; border: none; border-radius: 6px; padding: 9px 28px; font-size: 14px; font-weight: 600; cursor: pointer; transition: background .15s; }
.btn-submit:hover { background: #1e7e34; }

/* ── Panel toggle ──────────────────────────────────────────── */
.wiz-panel { display: none; }
.wiz-panel.active { display: block; }

/* ── Attendees step ────────────────────────────────────────── */
.sup-table { width: 100%; border-collapse: collapse; font-size: 14px; }
.sup-table th { background: #f5f7fb; color: #555; font-size: 11px; text-transform: uppercase; letter-spacing: .4px; padding: 10px 12px; border-bottom: 2px solid #e0e4f0; text-align: left; }
.sup-table td { padding: 10px 12px; border-bottom: 1px solid #f0f3f8; vertical-align: middle; }
.sup-table tr:hover td { background: #fafbfe; }
.sup-table td.cb-cell { width: 36px; text-align: center; }
.sup-table input[type=checkbox] { width: 17px; height: 17px; cursor: pointer; accent-color: #1c2452; }
.sup-table select, .sup-table input[type=text], .sup-table input[type=email] { width: 100%; padding: 5px 8px; border: 1px solid #d0d6e8; border-radius: 5px; font-size: 13px; }
.add-row-btn { background: #1c2452; color: #fff; border: none; border-radius: 6px; padding: 8px 18px; font-size: 13px; cursor: pointer; margin-top: 14px; }
.add-row-btn:hover { background: #2d3a8c; }
.new-att-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 12px; }
.new-att-table th { background: #e8ecf4; color: #555; font-size: 11px; text-transform: uppercase; padding: 8px 10px; border-bottom: 1px solid #d0d6e8; }
.new-att-table td { padding: 7px 8px; border-bottom: 1px solid #f0f3f8; vertical-align: middle; }
.new-att-table input, .new-att-table select { width: 100%; padding: 5px 7px; border: 1px solid #d0d6e8; border-radius: 5px; font-size: 12px; }
.rm-row-btn { background: none; border: none; color: #c0392b; font-size: 16px; cursor: pointer; padding: 0 4px; }
.section-divider { border: none; border-top: 2px dashed #e0e4f0; margin: 24px 0; }
</style>

<div class="container-fluid p-0">
    <div class="row">
        <?php echo $this->element('user_left_menu'); ?>

        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="ersu_message"><?php echo $this->Flash->render(); ?></div>

            <div class="teachers-top-heading mb-3">
                <span>Register for Conference</span>
            </div>

            <div style="max-width: 100%; margin: 0 0 40px;">

                <!-- Hero banner -->
                <div class="conf-hero">
                    <div class="conf-hero-icon"><i class="fa fa-university"></i></div>
                    <div class="conf-hero-body">
                        <h2><?php echo h($conventionD->name); ?></h2>
                        <div class="hero-meta">
                            <?php if (!empty($conventionD->address)): ?>
                            <span><i class="fa fa-map-marker"></i> <?php echo h($conventionD->address); ?></span>
                            <?php endif; ?>
                            <span><i class="fa fa-calendar"></i> <?php echo h($seasonD->season_year); ?></span>
                            <?php if ($convSeasonD && !empty($convSeasonD->registration_start_date) && $convSeasonD->registration_start_date !== '0000-00-00'): ?>
                            <span><i class="fa fa-clock-o"></i> <?php echo safe_date('d M Y', $convSeasonD->registration_start_date); ?> – <?php echo safe_date('d M Y', $convSeasonD->registration_end_date); ?></span>
                            <?php endif; ?>
                            <?php if ($regFee): ?>
                            <span><i class="fa fa-tag"></i> <?php echo $currency . ' ' . number_format($regFee, 2); ?> / delegate</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Step bar -->
                <div class="wiz-steps">
                    <div class="wiz-step active" id="ind-1">
                        <div class="circle">1</div>
                        <div class="step-lbl">Conference<br>Details</div>
                    </div>
                    <div class="wiz-connector" id="con-1"></div>
                    <div class="wiz-step" id="ind-2">
                        <div class="circle">2</div>
                        <div class="step-lbl">Your<br>Details</div>
                    </div>
                    <div class="wiz-connector" id="con-2"></div>
                    <div class="wiz-step" id="ind-3">
                        <div class="circle">3</div>
                        <div class="step-lbl">Attendees<br>Details</div>
                    </div>
                    <div class="wiz-connector" id="con-3"></div>
                    <div class="wiz-step" id="ind-4">
                        <div class="circle">4</div>
                        <div class="step-lbl">Confirm &amp;<br>Register</div>
                    </div>
                </div>

                <!-- ── STEP 1 ── -->
                <div class="wiz-panel active" id="step-1">
                    <div class="wiz-card">
                        <div class="wiz-card-head">
                            <div class="step-badge">1</div>
                            <h4>Conference Details</h4>
                        </div>
                        <div class="wiz-card-body">
                            <div class="info-row">
                                <span class="info-lbl">Conference</span>
                                <span class="info-val"><strong><?php echo h($conventionD->name); ?></strong></span>
                            </div>
                            <?php if (!empty($conventionD->address)): ?>
                            <div class="info-row">
                                <span class="info-lbl">Venue</span>
                                <span class="info-val"><i class="fa fa-map-marker" style="color:#888;margin-right:5px;"></i><?php echo h($conventionD->address); ?></span>
                            </div>
                            <?php endif; ?>
                            <div class="info-row">
                                <span class="info-lbl">Season Year</span>
                                <span class="info-val"><?php echo h($seasonD->season_year); ?></span>
                            </div>
                            <?php if ($convSeasonD && !empty($convSeasonD->registration_start_date) && $convSeasonD->registration_start_date !== '0000-00-00'): ?>
                            <div class="info-row">
                                <span class="info-lbl">Registration Window</span>
                                <span class="info-val">
                                    <?php echo safe_date('D, d M Y', strtotime($convSeasonD->registration_start_date)); ?>
                                    &nbsp;&rarr;&nbsp;
                                    <?php echo safe_date('D, d M Y', strtotime($convSeasonD->registration_end_date)); ?>
                                    <?php
                                    $now = time();
                                    $start = strtotime($convSeasonD->registration_start_date);
                                    $end   = strtotime($convSeasonD->registration_end_date);
                                    if ($now >= $start && $now <= $end):
                                    ?><span class="open-badge">Open</span><?php endif; ?>
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if ($regFee): ?>
                            <div class="info-row">
                                <span class="info-lbl">Registration Fee</span>
                                <span class="info-val">
                                    <span class="fee-pill">
                                        <i class="fa fa-tag"></i>
                                        <?php echo $currency . ' ' . number_format($regFee, 2); ?> per delegate
                                    </span>
                                </span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($conventionD->google_map_link)): ?>
                            <div class="info-row">
                                <span class="info-lbl">Map</span>
                                <span class="info-val"><a href="<?php echo h($conventionD->google_map_link); ?>" target="_blank" rel="noopener noreferrer"><i class="fa fa-map"></i> View on Google Maps</a></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="wiz-nav">
                        <a href="<?php echo $this->Url->build(['action' => 'myconferenceregistrations']); ?>" class="btn-back">&larr; Back to Conferences</a>
                        <button type="button" class="btn-next" onclick="wizardGo(2)">Next &rarr;</button>
                    </div>
                </div>

                <!-- ── STEP 2 ── -->
                <div class="wiz-panel" id="step-2">
                    <div class="wiz-card">
                        <div class="wiz-card-head">
                            <div class="step-badge">2</div>
                            <h4>Your Details</h4>
                        </div>
                        <div class="wiz-card-body">
                            <p style="font-size:13px;color:#666;margin-bottom:18px;">These are the contact details on file for your account. They will be used for invoices and confirmation emails.</p>
                            <div class="info-row">
                                <span class="info-lbl">Name</span>
                                <span class="info-val"><strong><?php echo h($adminName ?: '—'); ?></strong></span>
                            </div>
                            <div class="info-row">
                                <span class="info-lbl">Email</span>
                                <span class="info-val"><?php echo h($adminEmail ?: '—'); ?></span>
                            </div>
                            <?php if (!empty($userDetails->bill_to_city) || !empty($userDetails->bill_to_country)): ?>
                            <div class="info-row">
                                <span class="info-lbl">Location</span>
                                <span class="info-val"><?php echo h(implode(', ', array_filter([$userDetails->bill_to_city ?? '', $userDetails->bill_to_country ?? '']))); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($userDetails->school_id)): ?>
                            <div class="info-row">
                                <span class="info-lbl">School ID</span>
                                <span class="info-val"><?php echo h($userDetails->school_id); ?></span>
                            </div>
                            <?php endif; ?>
                            <p style="font-size:12px;color:#aaa;margin-top:16px;margin-bottom:0;">
                                Incorrect details? <a href="<?php echo $this->Url->build(['controller' => 'users', 'action' => 'editprofile']); ?>">Update your profile</a> before proceeding.
                            </p>
                        </div>
                    </div>
                    <div class="wiz-nav">
                        <button type="button" class="btn-back" onclick="wizardGo(1)">&larr; Back</button>
                        <button type="button" class="btn-next" onclick="wizardGo(3)">Next &rarr;</button>
                    </div>
                </div>

                <?php echo $this->Form->create(null, [
                    'url'    => ['action' => 'registerconventionwizard', $convention_slug, $season_id],
                    'method' => 'post',
                    'id'     => 'wiz-form',
                ]); ?>

                <!-- ── STEP 3 — Attendees Details ── -->
                <div class="wiz-panel" id="step-3">
                    <div class="wiz-card">
                        <div class="wiz-card-head">
                            <div class="step-badge">3</div>
                            <h4>Attendees Details</h4>
                        </div>
                        <div class="wiz-card-body">
                            <p style="font-size:13px;color:#666;margin-bottom:4px;">
                                Please fill the names exactly as you wish them to appear on <strong>certificates and name tags</strong>.
                            </p>

                            <?php if (!empty($schoolSupervisors)): ?>
                            <!-- Existing supervisors -->
                            <h5 style="font-size:14px;font-weight:700;color:#1c2452;margin:16px 0 10px;">
                                <i class="fa fa-users" style="margin-right:6px;"></i>Select from your existing supervisors
                            </h5>
                            <div style="overflow-x:auto;">
                            <table class="sup-table">
                                <thead>
                                    <tr>
                                        <th class="cb-cell"><i class="fa fa-check-square-o"></i></th>
                                        <th>First Name</th>
                                        <th>Surname</th>
                                        <th>Email</th>
                                        <th style="min-width:130px;">Role</th>
                                        <th style="min-width:140px;">Dietary Needs</th>
                                        <th style="min-width:130px;">Attending Both Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($schoolSupervisors as $sup): ?>
                                <tr id="sup-row-<?php echo $sup->id; ?>">
                                    <td class="cb-cell">
                                        <input type="checkbox" name="attendee_ids[]" value="<?php echo $sup->id; ?>"
                                               id="sup-<?php echo $sup->id; ?>"
                                               onchange="toggleSupRow(<?php echo $sup->id; ?>, this.checked)">
                                    </td>
                                    <td><?php echo h($sup->first_name); ?></td>
                                    <td><?php echo h($sup->last_name); ?></td>
                                    <td style="font-size:12px;color:#666;"><?php echo h($sup->email_address); ?></td>
                                    <td>
                                        <select name="attendee_role_<?php echo $sup->id; ?>" class="sup-extra" disabled>
                                            <option value="">— Role —</option>
                                            <option value="Principal">Principal</option>
                                            <option value="Administrator">Administrator</option>
                                            <option value="Supervisor">Supervisor</option>
                                            <option value="Monitor">Monitor</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="attendee_diet_<?php echo $sup->id; ?>" class="sup-extra" disabled>
                                            <option value="None">None</option>
                                            <option value="Vegetarian">Vegetarian</option>
                                            <option value="Vegan">Vegan</option>
                                            <option value="Gluten-Free">Gluten-Free</option>
                                            <option value="Halal">Halal</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="attendee_days_<?php echo $sup->id; ?>" class="sup-extra" disabled>
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                            </div>
                            <?php else: ?>
                            <div style="background:#f5f7fb;border-radius:8px;padding:14px 18px;font-size:13px;color:#888;margin-bottom:16px;">
                                <i class="fa fa-info-circle"></i> No existing supervisors found for your school. Add new attendees below.
                            </div>
                            <?php endif; ?>

                            <hr class="section-divider">

                            <!-- Add new attendees -->
                            <h5 style="font-size:14px;font-weight:700;color:#1c2452;margin:0 0 10px;">
                                <i class="fa fa-user-plus" style="margin-right:6px;"></i>Add New Attendees
                            </h5>
                            <p style="font-size:12px;color:#aaa;margin-bottom:10px;">These will be added to your school's supervisor list automatically.</p>
                            <div style="overflow-x:auto;">
                            <table class="new-att-table" id="new-att-tbl">
                                <thead>
                                    <tr>
                                        <th>First Name</th>
                                        <th>Surname</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Dietary Needs</th>
                                        <th>Both Days?</th>
                                        <th style="width:36px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="new-att-body">
                                    <!-- rows added by JS -->
                                </tbody>
                            </table>
                            </div>
                            <button type="button" class="add-row-btn" onclick="addAttendeeRow()">
                                <i class="fa fa-plus"></i> Add Attendee
                            </button>

                        </div>
                    </div>
                    <div class="wiz-nav">
                        <button type="button" class="btn-back" onclick="wizardGo(2)">&larr; Back</button>
                        <button type="button" class="btn-next" onclick="wizardGo(4)">Next &rarr;</button>
                    </div>
                </div>

                <!-- ── STEP 4 ── -->
                <div class="wiz-panel" id="step-4">
                    <div class="wiz-card">
                        <div class="wiz-card-head">
                            <div class="step-badge" style="background:#28a745;">4</div>
                            <h4>Confirm &amp; Register</h4>
                        </div>
                        <div class="wiz-card-body">
                            <p style="font-size:13px;color:#666;margin-bottom:16px;">Please review your registration summary before confirming.</p>

                            <div class="confirm-box">
                                <div class="cb-row">
                                    <span class="cb-lbl">Conference</span>
                                    <span class="cb-val"><strong><?php echo h($conventionD->name); ?></strong></span>
                                </div>
                                <div class="cb-row">
                                    <span class="cb-lbl">Venue</span>
                                    <span class="cb-val"><?php echo h($conventionD->address ?? '—'); ?></span>
                                </div>
                                <div class="cb-row">
                                    <span class="cb-lbl">Season</span>
                                    <span class="cb-val"><?php echo h($seasonD->season_year); ?></span>
                                </div>
                                <div class="cb-row">
                                    <span class="cb-lbl">Delegate</span>
                                    <span class="cb-val"><?php echo h($adminName ?: 'Unknown'); ?> &mdash; <?php echo h($adminEmail); ?></span>
                                </div>
                                <?php if ($regFee): ?>
                                <div class="cb-row">
                                    <span class="cb-lbl">Fee</span>
                                    <span class="cb-val"><strong><?php echo $currency . ' ' . number_format($regFee, 2); ?></strong></span>
                                </div>
                                <?php endif; ?>
                                <div class="cb-row" id="confirm-attendees-row" style="display:none;">
                                    <span class="cb-lbl">Attendees</span>
                                    <span class="cb-val" id="confirm-attendees-val" style="font-size:13px;"></span>
                                </div>
                            </div>

                            <div class="confirm-check-wrap">
                                <input type="checkbox" id="confirm_registration" name="confirm_registration" value="1">
                                <label for="confirm_registration">
                                    I confirm I wish to register <strong><?php echo h($adminName ?: 'my school'); ?></strong> for
                                    <strong><?php echo h($conventionD->name); ?></strong> — <?php echo h($seasonD->season_year); ?> season.
                                </label>
                            </div>
                            <div id="confirm-error" style="color:#c0392b;font-size:13px;margin-top:8px;display:none;">
                                Please tick the checkbox above to confirm.
                            </div>
                        </div>
                    </div>
                    <div class="wiz-nav">
                        <button type="button" class="btn-back" onclick="wizardGo(3)">&larr; Back</button>
                        <button type="button" class="btn-submit" onclick="wizardSubmit()">
                            <i class="fa fa-check"></i> Confirm &amp; Register
                        </button>
                    </div>
                </div>

                <?php echo $this->Form->end(); ?>

            </div><!-- max-width wrap -->
        </main>
    </div>
</div>

<script>
var currentStep = 1;

function wizardGo(step) {
    document.querySelectorAll('.wiz-panel').forEach(function(p) { p.classList.remove('active'); });
    for (var i = 1; i <= 4; i++) {
        var ind = document.getElementById('ind-' + i);
        ind.classList.remove('active', 'done');
        if (i < step)  ind.classList.add('done');
        if (i === step) ind.classList.add('active');
    }
    for (var j = 1; j <= 3; j++) {
        var con = document.getElementById('con-' + j);
        con.classList.toggle('done', j < step);
    }
    document.getElementById('step-' + step).classList.add('active');
    currentStep = step;
    window.scrollTo(0, 0);
    if (step === 4) updateAttendeeSummary();
}

function wizardSubmit() {
    var cb = document.getElementById('confirm_registration');
    if (!cb.checked) {
        document.getElementById('confirm-error').style.display = 'block';
        return;
    }
    document.getElementById('confirm-error').style.display = 'none';
    document.getElementById('wiz-form').submit();
}

/* ── Supervisor checkbox toggle ─── */
function toggleSupRow(id, checked) {
    var row = document.getElementById('sup-row-' + id);
    if (!row) return;
    row.querySelectorAll('.sup-extra').forEach(function(el) {
        el.disabled = !checked;
    });
    row.style.background = checked ? '#eef3ff' : '';
}

/* ── Dynamic new-attendee rows ─── */
var newRowCount = 0;
var roleOptions   = '<option value="">— Role —</option><option>Principal</option><option>Administrator</option><option>Supervisor</option><option>Monitor</option><option>Other</option>';
var dietOptions   = '<option value="None">None</option><option>Vegetarian</option><option>Vegan</option><option>Gluten-Free</option><option>Halal</option><option>Other</option>';

function addAttendeeRow() {
    var i = newRowCount++;
    var tr = document.createElement('tr');
    tr.id = 'nr-' + i;
    tr.innerHTML =
        '<td><input type="text" name="new_first_name[]" placeholder="First name"></td>' +
        '<td><input type="text" name="new_last_name[]" placeholder="Surname"></td>' +
        '<td><input type="email" name="new_email[]" placeholder="Email (optional)"></td>' +
        '<td><select name="new_role[]">' + roleOptions + '</select></td>' +
        '<td><select name="new_diet[]">' + dietOptions + '</select></td>' +
        '<td><select name="new_days[]"><option value="1">Yes</option><option value="0">No</option></select></td>' +
        '<td><button type="button" class="rm-row-btn" onclick="removeRow(' + i + ')" title="Remove">&times;</button></td>';
    document.getElementById('new-att-body').appendChild(tr);
}

function removeRow(i) {
    var el = document.getElementById('nr-' + i);
    if (el) el.remove();
}

function updateAttendeeSummary() {
    var names = [];
    // Existing supervisors checked
    document.querySelectorAll('#step-3 .sup-table input[type=checkbox]:checked').forEach(function(cb) {
        var row = cb.closest('tr');
        if (row) {
            var cells = row.querySelectorAll('td');
            // cells: 0=checkbox, 1=first, 2=last, 3=email
            var name = (cells[1] ? cells[1].textContent.trim() : '') + ' ' + (cells[2] ? cells[2].textContent.trim() : '');
            if (name.trim()) names.push(name.trim());
        }
    });
    // New attendees with first names
    document.querySelectorAll('#new-att-body input[name="new_first_name[]"]').forEach(function(inp) {
        var row = inp.closest('tr');
        if (!row) return;
        var fn = inp.value.trim();
        var ln = row.querySelector('input[name="new_last_name[]"]');
        ln = ln ? ln.value.trim() : '';
        if (fn || ln) names.push((fn + ' ' + ln).trim() + ' <em style="color:#aaa;">(new)</em>');
    });
    var row = document.getElementById('confirm-attendees-row');
    var val = document.getElementById('confirm-attendees-val');
    if (names.length > 0) {
        val.innerHTML = names.join('<br>');
        row.style.display = 'flex';
    } else {
        row.style.display = 'none';
    }
}
</script>
