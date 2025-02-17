<?php /* Template Name: Shows Archive */ ?>
<?php 
global $current_user, $wpdb;
get_currentuserinfo();
$userId = (is_user_logged_in()) ? $current_user->ID : 0;
?>
<?php get_header(); ?>

<?php 
$title = 'Live Shows'; 
$color_key = '
				<div class="well">				
						<h4 style="margin-top:0">Colour key to shows:</h4>
						<div class="row">
						<div class="col-sm-4"><i class="fa fa-square text-success" aria-hidden="true"></i>&nbsp;Open</div>
						<div class="col-sm-4"><i class="fa fa-square text-warning" aria-hidden="true"></i>&nbsp;Closes within 7 days</div>
						<div class="col-sm-4"><i class="fa fa-square text-danger" aria-hidden="true"></i>&nbsp;CLOSED</div>
					</div>
				</div>';
if (get_query_var('show_year') > 0){ $title = get_query_var('show_year').' Shows'; $color_key = '';}
?>

<div id="content" class="standard">
    <div class="container">
        <div class="row">
            <div class="col-md-9" id="main-content">
				<h1 class="title"><?php echo $title; ?></h1>
				<?php echo $color_key; ?>
				<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

<?php
		if(have_posts()):
                    while(have_posts()):
                        the_post();
                		$post_id = get_the_ID();
                		
                		$start_date = get_field('start_date', false, false);
                		$end_date = get_field('end_date', false, false);
                		$close_date = get_field('close_date', false, false);
                		$schedule_file = get_field('pdf_upload_schedule');
                		$entryform_file = get_field('pdf_upload_entryform');
                		$ringcards_file = get_field('ring_cards_pdf');
                		$ringplan_file = get_field('ring_plan');
                		$helpers_file = get_field('helpers_list');
                		$info_file = get_field('show_info');
                		$results_file = get_field('pdf_upload_results');
                		$champ_results_file = get_field('champ_results');
                		$venue = get_field('venue', false, false);
                		$online_link = get_field('online_show_entry_link', false, false);
                		
                		$start_date = new DateTime($start_date);
                		$end_date = new DateTime($end_date);
                		$closes = new DateTime($close_date);
                		$show_dates = $start_date->format('jS M');
                		if($start_date != $end_date){
                			$show_dates .= ' to '.$end_date->format('jS M Y');
                		}
                		else{
                			$show_dates .= $start_date->format(' Y');
                		}
                		
                		$enter_show_link = '<!-- || <i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<a href="">Enter Show</a> -->';
                		if (isset($online_link) && $online_link != ''){
                			if (preg_match("/ishowservices/i", $online_link)) {                				
                				$enter_show_link = '|| <img src="'.get_stylesheet_directory_uri() . '/img/iss-logo.png" style="height:16px" />&nbsp;<a href="'.$online_link.'" target="_blank">Enter Show</a>';
                			}
                			elseif (preg_match("/agilityshows\.online/i", $online_link)) {
                				$enter_show_link = '|| <img src="'.get_stylesheet_directory_uri() . '/img/aso-logo.png" style="height:16px" />&nbsp;<a href="'.$online_link.'" target="_blank">Enter Show</a>';
                			}
                		}
                		elseif ($online_link == ''){
                			$enter_show_link = '|| <img src="'.get_stylesheet_directory_uri() . '/img/logo.png" style="height:16px" />&nbsp;<a href="/enter-show/?show='.$post_id.'">Enter Online</a>';                				
                		}
                		
                		//green (success) for open shows; orange (warning) for closing in 7 days; red (danger) for closed
                		$panel_class = 'success';
                		$closes_text = '&nbsp;';
                		$enter_show = ' ||  <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$entryform_file['url'].'" target="_blank">Entry Form</a>
                				'.$enter_show_link;
                		if ($close_date < date('Ymd')){
                			$panel_class = 'danger';
                			$closes_text = 'CLOSED';
                			$enter_show = '';
                		}
                		elseif ($close_date <= date('Ymd', strtotime("+7 day"))){
                			$panel_class = 'warning';
                			$closes_text = 'Closes: '.$closes->format('D jS M');
                		}

                		if ($userId > 0){
	                		$ring_cards = get_ring_card_info($post_id, $userId);
	                		if (count($ring_cards) > 0){
								$filename = $post->post_name."__".$current_user->user_firstname."-".$current_user->user_lastname.".pdf";
								$closes_text = 'RING CARDS available';
								$ring_card_link = '|| <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href=\'javascript:pdf_ring_cards('.json_encode($ring_cards, JSON_HEX_APOS).', "'.get_the_title().'", "'.$show_dates.'", "'.$filename.'");\'>Ring Cards</a> ';
	                		}
	                		else{
	                			$ring_card_link = '';
	                		}
                		}

                		$results_file = get_field('day_1');
                		$results_per_day = '';
                		if(isset($results_file) && is_array($results_file)){
                			$day1 = $start_date->format('D');
                			$results_array = array();
                			array_push($results_array, '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$results_file['url'].'" target="_blank">'.$day1.'</a>');
                			$day_count = 1;
                			for ($day = $start_date->format('U')+86400; $day <= $end_date->format('U'); $day += 86400){
                				$day_count++;
                				$results_file = get_field('day_'.$day_count);
		                		if(isset($results_file) && is_array($results_file)){
                					$this_day = date('D', $day);
                					array_push($results_array, '<i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$results_file['url'].'" target="_blank">'.$this_day.'</a>');		                			
		                		}
                			}
                			$results_per_day = implode(" || ", $results_array);
                		}
                		
                        echo '
                		<div class="panel panel-'.$panel_class.'">
					    	<div class="panel-heading" role="tab" id="heading'.$post_id.'">
					      		<h4 class="panel-title">
                					<span class="pull-right">'.$closes_text.'</span>
					        		<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse'.$post_id.'" aria-expanded="false" aria-controls="collapseOne">' . get_the_title() . ' ('.$show_dates.')</a>
					      		</h4>
					    	</div>
					    	<div id="collapse'.$post_id.'" class="panel-collapse collapse" role="tabpanel" aria-labelledby="heading'.$post_id.'">
					      		<div class="panel-body">
					        		<div class="row">
						        		<div class="col-md-6">
		                        			<strong>Dates:</strong> '.$show_dates.'<br />
							        		<strong>Closes:</strong> '.$closes->format('jS M Y').'<br />
		                        		</div>
		                        		<div class="col-md-6">
		                        			<strong>Venue:</strong><br />
		                        			'.$venue.'
		                        		</div>
	                        		</div>
					        		<div class="row">
						        		<div class="col-md-12">
		                        			<!-- <i class="fa fa-map-o" aria-hidden="true"></i>&nbsp;<a href="'.get_the_permalink().'">View Show</a> || -->
							        		<i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$schedule_file['url'].'" target="_blank">Schedule</a>
							        		'.$enter_show;
							        		if(isset($ring_card_link) && $ring_card_link !=''){
							        			echo $ring_card_link;
							        		}
							        		elseif (isset($ringcards_file['url']) && $ringcards_file['url'] != ''){
							        			echo '|| <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$ringcards_file['url'].'" target="_blank">Ring Cards</a> ';
							        		}
							        		if (isset($ringplan_file['url']) && $ringplan_file['url'] != ''){
							        			echo '|| <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$ringplan_file['url'].'" target="_blank">Ring Plan</a>';
							        		}
							        		if (isset($helpers_file['url']) && $helpers_file['url'] != ''){
							        			echo '|| <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$helpers_file['url'].'" target="_blank">Helpers List</a>';
							        		}
							        		if (isset($info_file['url']) && $info_file['url'] != ''){
							        			echo '|| <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$info_file['url'].'" target="_blank">Show Information</a>';
							        		}
							        		if (isset($results_file['url']) && $results_file['url'] != ''){
							        			echo '|| <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$results_file['url'].'" target="_blank">Show Results</a>';
							        		}
							        		if (isset($champ_results_file['url']) && $champ_results_file['url'] != ''){
							        			echo '|| <i class="fa fa-file-pdf-o" aria-hidden="true"></i>&nbsp;<a href="'.$champ_results_file['url'].'" target="_blank">Championships Results</a>';
							        		}
							        		echo '
							        	</div>';
							        		if ($results_per_day !== ""){
							        			echo '<div class="col-md-12"><strong>Daily Results:</strong> '.$results_per_day.'</div>';
							        		}
							        	echo '
	                        		</div>
					      		</div>
					    	</div>
					  	</div>';

                        
                    endwhile;
                endif;
                ?>
				</div>
            </div>
            <div class="col-md-3" id="sidebar">
            <?php get_sidebar(); ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
