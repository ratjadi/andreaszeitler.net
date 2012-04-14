<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head profile="http://gmpg.org/xfn/11">
<?php  
	// Change: Andreas Zeitler: airy_album.php einziehen
	include  (TEMPLATEPATH . '/airy_album.php'); 	
?>

	<title><?php wp_title(''); ?> <?php if ( !(is_404()) && (is_single()) or (is_page()) or (is_archive()) ) { ?> at <?php } ?> <?php bloginfo('name'); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
	<meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
	<meta name="template" content="K2 <?php if (function_exists('k2info')) { k2info('version'); } ?>" />
 	<meta name="description" content="<?php bloginfo('description'); ?>" />
 

<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>" type="text/css" media="screen" />


	<link rel="alternate" type="application/rss+xml" title="RSS 2.0" href="<?php bloginfo('rss2_url'); ?>" />
	<link rel="alternate" type="text/xml" title="RSS .92" href="<?php bloginfo('rss_url'); ?>" />
	<link rel="alternate" type="application/atom+xml" title="Atom 0.3" href="<?php bloginfo('atom_url'); ?>" />

<?php if (is_single() or is_page()) { ?>
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
<?php } ?>
	
<?php if(!is_user_logged_in() && is_single() && ($comment_author != $_COOKIE['comment_author_'.COOKIEHASH]) and ('open' == $post-> comment_status) or ('comment' == $post-> comment_type) ) { ?>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/k2functions.js"></script>
<?php } ?>

<?php /* Load Live Commenting if enabled in the K2 Options Panel and the page is a permalink */ if ((get_option('k2livecommenting') == 0) and (is_single() and ('open' == $post-> comment_status) or ('comment' == $post-> comment_type) )) { ?>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/ajax_comments.js"></script>
<?php } ?>

<?php /* Load LiveSearch if enabled in the K2 Options Panel */ if (get_option('k2livesearch') == 0) { ?>
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/livesearch.js.php"></script>
	<script type="text/javascript">//<![CDATA[
		window.onload=function() {
		///liveSearchInit();
		<?php if( !is_user_logged_in() && is_single() && ($comment_author = $_COOKIE['comment_author_'.COOKIEHASH]) and ('open' == $post-> comment_status) or ('comment' == $post-> comment_type) ) { ?>HideUtils();<?php } ?>

		}
	//]]></script>
<?php } elseif  ( !is_user_logged_in() && is_single() && ($comment_author = $_COOKIE['comment_author_'.COOKIEHASH]) and ('open' == $post-> comment_status) or ('comment' == $post-> comment_type) ) { ?>

	<script type="text/javascript">//<![CDATA[
		window.onload=function() {
		HideUtils();
		}
	//]]></script>
	
	

<?php } ?>

<?php wp_get_archives('type=monthly&format=link'); ?>

	<?php wp_head(); ?>

	<?php /* Sets Matt's asides in motion */ function stupid_hack($str) { return preg_replace('|</ul>s*<ul class="asides">|', '', $str); } ob_start('stupid_hack'); ?>

	<script type="text/javascript">
		lightboxJBase = "../../../../";
	</script>	
	
	<script type="text/javascript" src="<?php bloginfo('template_directory'); ?>/js/jquery-1.7.1.min.js"></script>
	<script type="text/javascript" src="../../../../include/lightbox/js/jquery.lightbox-0.5.js"></script>
	<link rel="stylesheet" type="text/css" href="../../../../include/lightbox/css/jquery.lightbox-0.5.css" media="screen" />
	<script type="text/javascript">
		$(document).ready(function() {
			$('a:has(img)').addClass('azimg');
			//$('a[rel*=lightbox]').lightBox();
			$('.itemtext a[href$="jpg"], .itemtext a[href$="png"], .itemtext a[href$="jpeg"]').lightBox();
		});
	</script>	
	
</head>


<body>

<div id="page">

	<div id="header"> 
		
		<!-- Change: Andreas Zeitler: homelink einbauen -->

		<a href="<?php echo "http://www.andreaszeitler.net"; /* echo get_settings('home'); */?>"> 
			<div id="homelink"></div> 
		</a>		
		
		<?php
		include  (TEMPLATEPATH . '/mainmenu.php'); 
		?>
		
	<!--	<div id="mainmenu"> 
			<ul> 
				<li><a href="<?php echo get_settings('home');?>"><img src="phptext.php?text=portfolio" /></a></li> 							
			</ul> 
		</div>-->
    </div> 
    
    
<hr /> 