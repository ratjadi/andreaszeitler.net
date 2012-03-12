<?php /*
	Template Name: Archives (Do Not Use Manually)
*/ ?>

<?php /* Counts the posts, comments and categories on your blog */
	$numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish'");
	if (0 < $numposts) $numposts = number_format($numposts); 
	
	$numcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
	if (0 < $numcomms) $numcomms = number_format($numcomms);
	
	$numcats = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->categories");
	if (0 < $numcats) $numcats = number_format($numcats);
?>


<?php get_header(); ?>

<div class="content">

	<div class="primary">

		<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	
			<div class="item">
	
				<div class="pagetitle">
					<h2 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title='Permanent Link to "<?php the_title(); ?>"'><?php the_title(); ?></a></h2>
					<?php edit_post_link('<img src="'.get_bloginfo(template_directory).'/images/pencil.png" alt="Edit Link" />', '<span class="editlink">', '</span>'); ?>
				</div>
				
				<div class="itemtext2">
	
					<p>This is the frontpage of the <?php bloginfo('name'); ?> archives. Currently the archives are spanning
					<?php echo $numposts; ?> posts and <?php echo $numcomms; ?> comments, contained within the meager confines of <?php echo $numcats; ?> categories.
					Through here, you will be able to move down into the archives by way of time or category. If you are looking for something
					specific, perhaps you should try the search on the sidebar.</p>
	
					<?php if (function_exists('af_ela_super_archive')) { ?>
		

					<p>This is a 'live archive', which allows you to 'dig' into the <?php bloginfo('name'); ?> repository in a fast an efficient way, 
					without having to reload this page as you explore.</p>
	
					<div id="livearchives">
						<?php af_ela_super_archive('num_posts_by_cat=50&truncate_title_length=40&hide_pingbacks_and_trackbacks=1&num_entries=1&num_comments=1&number_text=<span>%</span>&comment_text=<span>%</span>&selected_text='.urlencode('')); ?>
						<div style="clear: both;"></div>
					</div>
	
					<?php } ?>
	
				
				
					<?php if (function_exists('UTW_ShowWeightedTagSetAlphabetical')) { ?>
					

					<p>The following is a list of the tags used at <?php bloginfo('name'); ?>, colored and 'weighed' in relation to their relative usage.</p>
				
					<?php UTW_ShowWeightedTagSetAlphabetical("coloredsizedtagcloud"); } ?>
	
				</div>
	
			</div>
	
		<?php endwhile; endif; ?>
	
	</div>

	<hr />

	<div class="secondary">

		<div class="sb-search"><h2><?php _e('Search'); ?></h2>
			<?php include (TEMPLATEPATH . '/searchform.php'); ?>
		</div>


		<div class="sb-about"><h2><?php _e('About'); ?></h2>
			<p>You are currently at the root of the <?php bloginfo('name'); ?> archives. From here there are several options for burrowing into the archives.</p>
		</div>

		<div class="sb-latest2"><h2><?php _e('Latest'); ?></h2>
			<ul>
				<?php wp_get_archives('type=postbypost&limit=10'); ?>
			</ul>
		</div>

		
	
	</div>	

	<?php // get_sidebar(); ?>
	<div class="clear">&nbsp;</div>
</div>

<?php get_footer(); ?>