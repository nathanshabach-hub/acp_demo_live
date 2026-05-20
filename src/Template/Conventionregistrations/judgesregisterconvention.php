<style>
.jrc-page-title { margin: 16px 0 4px; font-size: 30px; font-weight: 800; letter-spacing: -0.02em; color: #1f2a37; }
.jrc-subtitle { color: #6b7280; margin: 0 0 16px; font-size: 15px; }

.jrc-card {
	background: #ffffff;
	border: 1px solid #dbe3ee;
	border-radius: 14px;
	box-shadow: 0 10px 24px rgba(16, 24, 40, 0.06);
	overflow: hidden;
}
.jrc-card-head {
	padding: 18px 20px;
	border-bottom: 1px solid #e7edf5;
	background: linear-gradient(180deg, #f9fbff 0%, #f4f8ff 100%);
}
.jrc-card-body { padding: 18px 20px 20px; }

.jrc-toolbar {
	display: flex;
	flex-wrap: wrap;
	align-items: center;
	justify-content: space-between;
	gap: 12px;
	margin-bottom: 12px;
}
.jrc-search-wrap { flex: 1 1 360px; }
.jrc-search {
	width: 100%;
	max-width: 520px;
	border: 1px solid #cdd7e4;
	border-radius: 10px;
	padding: 10px 14px;
	font-size: 14px;
	background: #ffffff;
	outline: none;
	transition: border-color .15s ease, box-shadow .15s ease;
}
.jrc-search:focus {
	border-color: #4f46e5;
	box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
}

.jrc-meta { font-size: 13px; color: #4b5563; }
.jrc-pill {
	display: inline-block;
	border: 1px solid #d7e0ec;
	background: #f8fbff;
	color: #334155;
	border-radius: 999px;
	padding: 5px 10px;
	font-size: 12px;
	font-weight: 600;
	margin-right: 6px;
}

.jrc-table-wrap {
	border: 1px solid #e2e8f0;
	border-radius: 10px;
	max-height: 62vh;
	overflow: auto;
	background: #fff;
}
.jrc-table {
	margin-bottom: 0;
	min-width: 980px;
}
.jrc-table th {
	position: sticky;
	top: 0;
	z-index: 2;
	background: #eef3fb;
	font-size: 13px;
	font-weight: 700;
	color: #1f2937;
	border-bottom: 1px solid #d8e2ef;
	padding: 10px 9px;
	white-space: nowrap;
}
.jrc-table td {
	font-size: 14px;
	color: #1f2937;
	vertical-align: middle;
	padding: 9px;
}
.jrc-table tbody tr:nth-child(even) td { background: #fbfdff; }
.jrc-assigned { font-size: 12px; color: #555; }
.jrc-slot-empty { color: #ccc; font-style: italic; font-size: 12px; }
.jrc-badge-you { display:inline-block; background:#d5f5e3; color:#1e8449; font-size:11px; padding:1px 7px; border-radius:10px; font-weight:700; margin-left:4px; }
.jrc-table tr:hover td { background: #f9fbff; }
.jrc-table input[type=checkbox] { width:18px; height:18px; cursor:pointer; }
.jrc-slot-partial { background: #e9f8ef; border-left: 3px solid #27ae60; }
.jrc-slot-full { background: #fdecea; border-left: 3px solid #c0392b; }
.jrc-slot-full .jrc-assigned { color: #8e1b12; font-weight: 600; }
.jrc-event-full td { background: #fff5f5; }
.jrc-table tr:hover td.jrc-slot-partial { background: #e9f8ef; }
.jrc-table tr:hover td.jrc-slot-full { background: #fdecea; }
.jrc-legend { font-size: 12px; margin: 8px 0 12px; }
.jrc-legend-chip { display:inline-block; padding:4px 9px; border-radius:10px; margin-right:8px; border:1px solid transparent; font-weight: 600; }
.jrc-legend-open { background:#e9f8ef; border-color:#27ae60; color:#1e8449; }
.jrc-legend-full { background:#fdecea; border-color:#c0392b; color:#8e1b12; }
.jrc-no-match { display:none; text-align:center; color:#6c757d; font-size:13px; padding: 12px; border:1px dashed #d0d7e2; border-radius:6px; margin-top:8px; }

@media (max-width: 768px) {
	.jrc-page-title { font-size: 24px; }
	.jrc-card-head { padding: 14px 14px; }
	.jrc-card-body { padding: 14px 14px 16px; }
	.jrc-search { max-width: 100%; }
	.jrc-meta { width: 100%; }
}
</style>
<script type="text/javascript">
	$(document).ready(function () {
		$("#judgeregister").validate();

		function jrcRefreshSelectedCount() {
			var selected = $('#jrc-event-tbody input[type=checkbox]:checked').length;
			$('#jrc-selected-count').text(selected);
		}

		function jrcApplyFilter() {
			var q = $.trim($('#jrc-event-search').val()).toLowerCase();
			var visible = 0;
			$('#jrc-event-tbody tr').each(function() {
				var hay = ($(this).attr('data-search') || '').toLowerCase();
				var match = (q === '' || hay.indexOf(q) !== -1);
				$(this).toggle(match);
				if(match) visible++;
			});
			$('#jrc-visible-count').text(visible);
			$('#jrc-no-match').toggle(visible === 0);
		}

		$('#jrc-event-search').on('input', jrcApplyFilter);

		$('#jrc-select-all').on('change', function() {
			$('#jrc-event-tbody input[type=checkbox]:visible').prop('checked', this.checked);
			jrcRefreshSelectedCount();
		});

		$('#jrc-event-tbody').on('click', 'tr', function(e) {
			if($(e.target).is('input')) return;
			var cb = $(this).find('input[type=checkbox]');
			cb.prop('checked', !cb.prop('checked'));
			jrcRefreshSelectedCount();
		});

		$('#jrc-event-tbody').on('change', 'input[type=checkbox]', function() {
			jrcRefreshSelectedCount();
		});

		jrcApplyFilter();
		jrcRefreshSelectedCount();
	});
</script>
<?php
$conventionD = isset($conventionD) ? $conventionD : null;
$convSeasonD = isset($convSeasonD) ? $convSeasonD : null;
$conventionregistrations = isset($conventionregistrations) ? $conventionregistrations : null;
$eventsList = (isset($eventsList) && is_iterable($eventsList)) ? $eventsList : [];
?>
<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

			<div class="ersu_message">
				<?php echo $this->Flash->render() ?>
			</div>

			<h2 class="jrc-page-title">Register For Convention :: <?php echo !empty($conventionD->name) ? h($conventionD->name) : 'Convention'; ?></h2>
			<p class="jrc-subtitle">Season <?php echo !empty($convSeasonD->season_year) ? h($convSeasonD->season_year) : ''; ?> judge preference selection</p>

			<div class="dashboard-form jrc-card">
				<div class="jrc-card-head">
					<h2 class="form-title" style="margin:0; font-size: 30px; font-weight: 600;">Select Preferred Events</h2>
				</div>
				<div class="jrc-card-body">
				<?php echo $this->Form->create($conventionregistrations, ['id' => 'judgeregister', 'type' => 'file', 'class' => ' ']); ?>

				<div class="form-group">
					<p class="text-muted" style="font-size:14px; margin-bottom:10px; line-height:1.45;">
						Tick the events you are available to judge. Not all events will be allocated to you &mdash; events are assigned on a needs basis, taking workload into consideration.
					</p>
					<div class="jrc-toolbar">
						<div class="jrc-search-wrap">
							<input type="text" id="jrc-event-search" class="jrc-search" placeholder="Search by event code or event name...">
						</div>
						<div class="jrc-meta">
							<span class="jrc-pill">Visible: <strong id="jrc-visible-count"><?php echo count($eventsList); ?></strong></span>
							<span class="jrc-pill">Selected: <strong id="jrc-selected-count">0</strong></span>
						</div>
					</div>
					<div class="jrc-legend">
						<span class="jrc-legend-chip jrc-legend-open">Green = at least one judge already assigned, slots still open</span>
						<span class="jrc-legend-chip jrc-legend-full">Red = all 3 judge slots are already filled</span>
					</div>
					<?php if (empty($eventsList)) { ?>
						<div class="alert alert-warning">No events found for this season.</div>
					<?php } else { ?>
					<div class="jrc-table-wrap">
					<table class="table table-bordered table-hover jrc-table">
						<thead>
							<tr>
								<th style="width:40px; text-align:center;">
									<input type="checkbox" id="jrc-select-all" title="Select / deselect all" style="width:16px;height:16px;cursor:pointer;">
								</th>
								<th style="width:80px;">Code</th>
								<th>Event Name</th>
								<th style="width:155px;">Judge 1</th>
								<th style="width:155px;">Judge 2</th>
								<th style="width:155px;">Judge 3</th>
							</tr>
						</thead>
						<tbody id="jrc-event-tbody">
						<?php
						$loggedUserId = (int)$this->request->getSession()->read('user_id');
						foreach($eventsList as $ev):
							$eid = (int)$ev->id;
							$searchText = trim((string)$ev->event_id_number . ' ' . (string)$ev->event_name);
							$checked = isset($alreadySelectedIds[$eid]) ? ' checked' : '';
							$panel = isset($seasonAssignments[$eid]) ? $seasonAssignments[$eid] : ['judge1'=>null,'judge2'=>null,'judge3'=>null];
							$filledSlots = 0;
							foreach(['judge1','judge2','judge3'] as $slot) {
								if((int)$panel[$slot] > 0) {
									$filledSlots++;
								}
							}
							$isFullyAssigned = ($filledSlots === 3);
							$slots = [];
							$slotClasses = [];
							foreach(['judge1','judge2','judge3'] as $slot) {
								$uid = (int)$panel[$slot];
								if($uid > 0) {
									$badge = ($uid === $loggedUserId) ? ' <span class="jrc-badge-you">you</span>' : '';
									$slots[] = '<span class="jrc-assigned">Judge Assigned'.$badge.'</span>';
									$slotClasses[] = $isFullyAssigned ? 'jrc-slot-full' : 'jrc-slot-partial';
								} else {
									$slots[] = '<span class="jrc-slot-empty">&mdash;</span>';
									$slotClasses[] = '';
								}
							}
						?>
							<tr style="cursor:pointer;" class="<?php echo $isFullyAssigned ? 'jrc-event-full' : ''; ?>" data-search="<?php echo h($searchText); ?>">
								<td style="text-align:center;">
									<input type="checkbox" name="Conventionregistrations[judges_event_ids][]" value="<?php echo $eid; ?>"<?php echo $checked; ?>>
								</td>
								<td><?php echo h($ev->event_id_number); ?></td>
								<td><?php echo h($ev->event_name); ?></td>
								<td class="<?php echo h($slotClasses[0]); ?>"><?php echo $slots[0]; ?></td>
								<td class="<?php echo h($slotClasses[1]); ?>"><?php echo $slots[1]; ?></td>
								<td class="<?php echo h($slotClasses[2]); ?>"><?php echo $slots[2]; ?></td>
							</tr>
						<?php endforeach; ?>
						</tbody>
					</table>
					<div id="jrc-no-match" class="jrc-no-match">No events match your search.</div>
					</div>
					<?php } ?>
				</div>

				<div class="form-group form-btns">
					<label></label>
					<button type="submit" class="btn btn-secondary">Save</button>
					<?php echo $this->Html->link('Cancel', ['controller' => 'conventionregistrations', 'action' => 'myregistrations'], ['class' => 'btn btn-secondary']); ?>
				</div>
				<?php echo $this->Form->end(); ?>
				</div>
			</div>
			<!-- dashboard-section-3 end-->

		</main>
	</div>
</div>
