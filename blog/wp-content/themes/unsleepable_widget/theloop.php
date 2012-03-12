<?php /*
	This is the loop, which fetches entries from your database. It is used in some
	form on most of the K2 pages. Because of that, to make editing all of them easier,
	it has been placed in its own file, which is then included where needed.
*/ ?>

<?php /* Initialize The Loop */ if (have_posts()) { $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>

	<?php // Headlines for archives
	if (!is_single() && !is_home() or is_paged()) { ?>
	<div class="pagetitle">
		<h2>
		<?php /* If this is a category archive */ if (is_category()) { ?>				
		Archive for the '<?php echo single_cat_title(); ?>' Category

		<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		Archive for <?php the_time('F jS, Y'); ?>

		<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		Archive for <?php the_time('F, Y'); ?>

		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		Archive for <?php the_time('Y'); ?>

		<?php /* If this is a search */ } elseif (is_search()) { ?>
		Search Results for '<?php echo $s; ?>'

		<?php /* If this is an author archive */ } elseif (is_author()) { ?>
		Author Archive for <?php $post = $wp_query->post;
			$the_author = $wpdb->get_var("SELECT meta_value FROM $wpdb->usermeta WHERE user_id = '$post->post_author' AND meta_key = 'nickname'"); echo $the_author; ?>
		
		<?php /* If this is a paged archive */ } elseif (is_paged()) {
		// Änderung Andeas Zeitler 03.09.2010 00:04
		// Remove page title "archive page..."
		// echo("archive Page $paged");
		 ?>
		
		<?php } ?>
		</h2>
	</div>

	<?php } ?>

	<?php /*
		The 'next page' and 'previous page' navigation for permalinks have to be inside the loop.
		The exact opposite is true for the same navigation links on all other pages.
		Also, we don't want them at the top of the frontpage: */
		
		if (!is_single() && !is_home() or is_paged()) include (TEMPLATEPATH . '/navigation.php');

		/* Start The Loop */ while (have_posts()) { the_post();	

		if (is_single()) include (TEMPLATEPATH . '/navigation.php');
	?>
	
	
		<?php /* Asides -- Pick a category to be an 'aside' in the K2 options panel */
		/* On archive pages, show asides inline no matter what */ if (is_archive() or is_search() or is_single()) { $k2asidescheck = '0'; } else { $k2asidescheck = get_option('k2asidesposition'); }
		$k2asides = get_option('k2asidescategory'); if (($k2asides != '0') && (in_category($k2asides) && ($k2asidescheck != '1'))) { ?>
	
			<div id="post-<?php the_ID(); ?>" class="item aside">
				<div class="itemhead">
	<div id="entrytitle">
					<h3><a href="<?php the_permalink() ?>" rel="bookmark" title='Permanent Link to "<?php strip_tags(the_title()); ?>"'><?php the_title(); ?></a></h3>

	<span class="chronodata">
						<?php the_time('dMy') ?></span>
	</div>
					<!-- The following two sections are for a noteworthy plugin currently in alpha. They'll get cleaned up and integrated better -->
					<?php foreach((get_the_category()) as $cat) {  if ($cat->cat_name == 'Noteworthy') { ?>
						<span class="metalink favorite"><img src="<?php bloginfo('template_url'); ?>/images/favorite.gif" alt="Favorite Entry" /></span>
					<?php } } ?>
					
					<?php /* Support for noteworthy plugin */ if (function_exists(nw_noteworthyLink)) { ?><span class="metalink"><?php nw_noteworthyLink($post->ID); ?></span><?php } ?>
 
					<?php edit_post_link('<img src="'.get_bloginfo(template_directory).'/images/pencil.png" alt="Edit Link" />','<span class="editlink">','</span>'); ?>

					
				</div>

				<div class="itemtext">
					<?php 
							// Änderung Andeas Zeitler 03.09.2010 00:00
							 	the_content("more...");
							//the_content("Continue reading '" . the_title('', '', false) . "'"); 
					?>
				</div>
<small class="metadata">
						<span class="category">Filed under: <?php /* If 'Nice Categories' plugin is installed, use it; if not, use default */ if (function_exists('the_nice_category')) { the_nice_category(', '); } else { the_category(', '); }?>	</span>&nbsp;&nbsp;|&nbsp;&nbsp;<?php comments_popup_link('0&nbsp;<span>Comments</span>', '1&nbsp;<span>Comment</span>', '%&nbsp;<span>Comments</span>', 'commentslink', '<span class="commentslink">Closed</span>'); ?>

						
						<?php if (is_single() && function_exists(UTW_ShowTagsForCurrentPost)) { ?>
							<span class="tagdata">Tags: <?php UTW_ShowTagsForCurrentPost("commalist") ?></span>
						<?php } ?>
						
					</small>
			</div>


		<?php  /* Normal Entries */ } elseif (!(in_category($k2asides))) { ?>

	
			<div id="post-<?php the_ID(); ?>" class="item entry">
				<div class="itemhead">
	<div id="entrytitle">
					<h3><a href="<?php the_permalink() ?>" rel="bookmark" title='Permanent Link to "<?php strip_tags(the_title()); ?>"'><?php the_title(); ?></a></h3>

<div class="chronodata"><?php { the_time('dMy') ?><?php } ?>
						</div>
	</div>
	
					<!-- The following two sections are for a noteworthy plugin currently in alpha. They'll get cleaned up and integrated better -->
					<?php foreach((get_the_category()) as $cat) {  if ($cat->cat_name == 'Noteworthy') { ?>
						<span class="metalink favorite"><img src="<?php bloginfo('template_url'); ?>/images/favorite.gif" alt="Favorite Entry" /></span>
					<?php } } ?>
					
					<?php /* Support for noteworthy plugin */ if (function_exists(nw_noteworthyLink)) { ?><span class="metalink"><?php nw_noteworthyLink($post->ID); ?></span><?php } ?>

						<?php edit_post_link('<img src="'.get_bloginfo(template_directory).'/images/pencil.png" alt="Edit Link" />','<span class="editlink">','</span>'); ?>					

				</div>
	
				<div class="itemtext">
					<?php if (is_archive() or is_search()) { 
						the_excerpt();
					} else {
							// Änderung Andeas Zeitler 03.09.2010 00:00
							// In der Post Übersicht nur excerpts anzeigen, in der Post Detailansicht den normalen Content
							 	if (!is_single()) the_excerpt();
							 	else the_content(); 
					} ?>
	
					<?php link_pages('<p><strong>Pages:</strong> ', '</p>', 'number'); ?>
				</div>
	
<small class="metadata">			
<span class="category">Filed under: <?php /* If 'Nice Categories' plugin is installed, use it; if not, use default */ if (function_exists('the_nice_category')) { the_nice_category(', '); } else { the_category(', '); }?>	</span>

					&nbsp;&nbsp;|&nbsp;&nbsp;<?php comments_popup_link('0&nbsp;<span>Comments</span>', '1&nbsp;<span>Comment</span>', '%&nbsp;<span>Comments</span>', 'commentslink', '<span class="commentslink">Closed</span>'); ?>

<?php if (is_single() && function_exists(UTW_ShowTagsForCurrentPost)) { ?>
							<span class="tagdata">Tags: <?php UTW_ShowTagsForCurrentPost("commalist") ?>.</span>
						<?php } ?>

</small>


				<!--
				<?php trackback_rdf(); ?>
				-->
			</div>
				
		<?php /* End Asides Segregation Code */ }

	/* End The Loop */ }

	/* Insert Paged Navigation */ if (!is_single()) { include (TEMPLATEPATH . '/navigation.php'); } ?>

<?php /* If there is nothing to loop */  } else { $notfound = '1'; /* So we can tell the sidebar what to do */ ?>

	<div class="center">
		<h2>Not Found</h2>
	</div>

	<div class="item">
	<div class="itemtext2">
		<p>Oh no! You're looking for something which just isn't here! Fear not however,
		errors are to be expected, and luckily there are tools on the sidebar for you to
		use in your search for what you need.</p>
	</div>
	</div>

<?php /* End Loop Init */ } ?>