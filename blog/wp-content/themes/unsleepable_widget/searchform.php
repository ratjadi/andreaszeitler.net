<?php if (!is_search()) {
		$search_text = "type and wait to search";
	} else {
		$search_text = "$s";
	}
?>

	<?php /* Detect whether Livesearch is enabled. If it is, the following code is used. */ $k2ls = get_option('k2livesearch'); if ($k2ls == 0) { ?>
		
		<div class="livesearchform">
		<form onsubmit="return liveSearchSubmit()" id="searchform" name="searchform" method="get" action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>">
			<input type="text" id="livesearch" name="s" value="<?php echo wp_specialchars($search_text, 1); ?>" onkeypress="liveSearchStart()" onblur="setTimeout('closeResults()',2000); if (this.value == '') {this.value = 'type and wait to search';}"  onfocus="if (this.value == 'type and wait to search') {this.value = '';}" />
			<input type="submit" id="searchsubmit" style="display: none;" value="Search" />
			<!--[if IE]><div><![endif]--><div id="LSResult" style="display: none;"><div id="LSShadow"></div></div><!--[if IE]></div><br /><![endif]-->
		</form>
		</div>

	<?php } else { /* If livesearch is disabled, this is the normal vanilla search */ ?>

		<form method="get" id="searchform" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input type="text" value="<?php echo wp_specialchars($search_text, 1); ?>" name="s" id="s" onfocus="if (this.value == 'type and wait to search') {this.value = '';}" onblur="if (this.value == '') {this.value = 'type and wait to search';}" />
			<input type="submit" id="searchsubmit" value="go" />
		</form>

	<?php } ?>



