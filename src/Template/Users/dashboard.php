<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			
			<!-- Dashboard Content -->
			<div class="dashboard-container">
				<h1 class="dashboard-title">Dashboard</h1>
				
				<!-- Welcome Section -->
				<div class="welcome-section">
					<h2 class="welcome-greeting">Welcome, <?php echo h($userDetails->first_name); ?></h2>
					<?php if (!empty($userDetails->email_address)) { ?>
						<p class="welcome-email"><?php echo h($userDetails->email_address); ?></p>
					<?php } ?>
					
					<?php if (!empty($settingsD->postinfo)) { ?>
						<div class="welcome-message">
							<?php echo $settingsD->postinfo; ?>
						</div>
					<?php } ?>
					
					<div class="welcome-instructions">
						<p>Please see the instructional videos below for navigation of the Convention Portal. For any other questions, please contact the events team.</p>
					</div>
				</div>
				
				<!-- Videos Section -->
				<?php
				$videoIds = isset($dashboardVideoIds) && is_array($dashboardVideoIds) ? $dashboardVideoIds : [];
				if (!empty($videoIds)) {
				?>
				<div class="videos-section">
					<h3 class="videos-title">Instructional Videos</h3>
					<div class="videos-grid">
						<?php foreach ($videoIds as $index => $videoId) { ?>
							<?php if (!empty($videoId)) { ?>
							<div class="video-card">
								<div class="video-wrapper">
									<iframe
										src="https://www.youtube.com/embed/<?php echo h($videoId); ?>"
										title="Instructional Video <?php echo (int)($index + 1); ?>"
										class="video-iframe"
										frameborder="0"
										allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
										allowfullscreen>
									</iframe>
								</div>
							</div>
							<?php } ?>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
			
			<style>
				:root {
					--dash-bg: #f8f9fa;
					--dash-surface: #ffffff;
					--dash-border: #e8eaed;
					--dash-text: #202124;
					--dash-text-light: #5f6368;
					--dash-accent: #1967d2;
					--dash-accent-light: #e8f0fe;
				}
				
				.dashboard-container {
					padding: 2rem 0;
				}
				
				.dashboard-title {
					font-size: 2rem;
					font-weight: 600;
					color: var(--dash-text);
					margin-bottom: 2rem;
					margin-top: 0.5rem;
				}
				
				/* Welcome Section */
				.welcome-section {
					background: var(--dash-surface);
					border: 1px solid var(--dash-border);
					border-radius: 8px;
					padding: 2rem;
					margin-bottom: 3rem;
					box-shadow: 0 1px 2px rgba(32, 33, 36, 0.08);
				}
				
				.welcome-greeting {
					font-size: 1.5rem;
					font-weight: 600;
					color: var(--dash-text);
					margin: 0 0 0.5rem 0;
				}
				
				.welcome-email {
					font-size: 0.875rem;
					color: var(--dash-text-light);
					margin: 0 0 1.5rem 0;
				}
				
				.welcome-message {
					background: var(--dash-accent-light);
					border-left: 3px solid var(--dash-accent);
					padding: 1rem;
					border-radius: 4px;
					margin-bottom: 1.5rem;
					font-size: 0.95rem;
					color: var(--dash-text);
					line-height: 1.6;
				}
				
				.welcome-message p {
					margin: 0;
				}
				
				.welcome-instructions {
					font-size: 0.95rem;
					color: var(--dash-text-light);
					line-height: 1.6;
				}
				
				.welcome-instructions p {
					margin: 0;
				}
				
				/* Videos Section */
				.videos-section {
					margin-bottom: 2rem;
				}
				
				.videos-title {
					font-size: 1.25rem;
					font-weight: 600;
					color: var(--dash-text);
					margin-bottom: 1.5rem;
					margin-top: 0;
				}
				
				.videos-grid {
					display: grid;
					grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
					gap: 1.5rem;
				}
				
				.video-card {
					background: var(--dash-surface);
					border: 1px solid var(--dash-border);
					border-radius: 8px;
					overflow: hidden;
					box-shadow: 0 1px 3px rgba(32, 33, 36, 0.12);
					transition: box-shadow 0.2s ease, transform 0.2s ease;
				}
				
				.video-card:hover {
					box-shadow: 0 4px 12px rgba(32, 33, 36, 0.15);
					transform: translateY(-2px);
				}
				
				.video-wrapper {
					position: relative;
					width: 100%;
					padding-bottom: 56.25%; /* 16:9 aspect ratio */
					height: 0;
					overflow: hidden;
					background: #000;
				}
				
				.video-iframe {
					position: absolute;
					top: 0;
					left: 0;
					width: 100%;
					height: 100%;
					border: none;
				}
				
				/* Responsive Design */
				@media (max-width: 1024px) {
					.videos-grid {
						grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
						gap: 1.25rem;
					}
				}
				
				@media (max-width: 768px) {
					.dashboard-container {
						padding: 1.5rem 0;
					}
					
					.dashboard-title {
						font-size: 1.75rem;
						margin-bottom: 1.5rem;
					}
					
					.welcome-section {
						padding: 1.5rem;
						margin-bottom: 2rem;
					}
					
					.welcome-greeting {
						font-size: 1.25rem;
					}
					
					.videos-grid {
						grid-template-columns: 1fr;
						gap: 1rem;
					}
				}
				
				@media (max-width: 576px) {
					.dashboard-title {
						font-size: 1.5rem;
						margin-bottom: 1rem;
					}
					
					.welcome-section {
						padding: 1.25rem;
						margin-bottom: 1.5rem;
						border-radius: 6px;
					}
					
					.welcome-greeting {
						font-size: 1.125rem;
					}
				}
			</style>
		</main>
	</div>
</div>