<div class="container-fluid p-0">
	<div class="row">
		<?php echo $this->element('user_left_menu'); ?>
		<main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
			<div class="ersu_message"> <?php echo $this->Flash->render() ?> </div>
			<h2 class="mt-3">Dashboard</h2>
			
			<!-- dashboard-section-1 start-->
			<div class="dasboard-section">
				<div class="dashboard-text">
					<?php
					$videoIds = isset($dashboardVideoIds) && is_array($dashboardVideoIds) ? $dashboardVideoIds : [];
					?>
					<h2>Welcome <?php echo $userDetails->first_name; ?> (<?php echo $userDetails->email_address; ?>)</h2>
					
					
					<?php
					if(!empty($settingsD->postinfo))
					{
						echo '<p>';
						
						echo $postinfo = $settingsD->postinfo;
						
						// The Regular Expression filter
						//$reg_pattern = "/(((http|https|ftp|ftps)\:\/\/)|(www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\:[0-9]+)?(\/\S*)?/";
						 
						// make the urls to hyperlinks
						//echo preg_replace($reg_pattern, '<a style="color:#000;" href="$0" target="_blank" rel="noopener noreferrer">$0</a>', $postinfo);
						 
						echo '</p>';
						
					}
					?>
					
					<p>Please see instructional videos below for navigation of the Convention Portal. For any other questions, please contact the events team. 
					</p>
					
					<?php for ($i = 0; $i < count($videoIds); $i += 2) { ?>
					<p>
						<?php for ($j = $i; $j < $i + 2 && $j < count($videoIds); $j++) { ?>
							<?php if (!empty($videoIds[$j])) { ?>
							<iframe width="560" height="315" src="https://www.youtube.com/embed/<?php echo h($videoIds[$j]); ?>" title="YouTube video player <?php echo (int)($j + 1); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
							<?php } ?>
						<?php } ?>
					</p>
					<?php } ?>
					
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					<p>&nbsp;</p>
					
				</div>
			</div>
			<!-- dashboard-section-1 end-->
			
		</main>
	</div>
</div>