<?php
/*
Template Name: Links
*/
?>

<?php get_header(); ?>

<div class="content">
	
	<div class="primary">

    <?php if (have_posts()) { while (have_posts()) { the_post(); ?>

		<div class="item2">

			<div class="pagetitle">
				<h2 id="post-<?php the_ID(); ?>"><?php the_title(); ?></h2>
				
			</div>
		
			<div class="itemtext">

			<div id="linkpage">
			<ul>
                        <?php get_links_list(); ?>
			</ul>
			</div>


			</div>

		</div>

		<?php } ?>
		<?php /* If there is nothing to loop */  } else { $notfound = '1'; /* So we can tell the sidebar what to do */ ?>
		
			<div class="center">
				<h2>Not Found</h2>
			</div>
		
			<div class="item">
			<div class="itemtext">
				<p>Oh no! You're looking for something which just isn't here! Fear not however,
				errors are to be expected, and luckily there are tools on the sidebar for you to
				use in your search for what you need.</p>
			</div>
			</div>

		<?php /* End Loop Init */ } ?>

	</div>

	<?php get_sidebar(); ?>

</div>
	
<?php get_footer(); ?>