<?php
// Test change for git
function debug($texto){
	file_put_contents(TEMPLATEPATH.'/log.log',date('d/m/Y H:i:s').' - '.$texto."\n",FILE_APPEND);
	//echo date('d/m/Y H:i:s').' - '.$texto."<br>";
	flush();
	return;
}

// Change: Andreas Zeitler:
// subscriber sehen private posts und werden nach dem login sofort auf die startseite anstatt dem dashboard umgeleitet
$subRole = get_role( 'subscriber' );  
$subRole->add_cap( 'read_private_posts', true );

function loginRedirect( $redirect_to, $request_redirect_to, $user ) {  
    if ( is_a( $user, 'WP_User' ) && $user->has_cap( 'edit_posts' ) === false ) {  
        return get_bloginfo( 'siteurl' );  
    }  
    return $redirect_to;  
}  
add_filter( 'login_redirect', 'loginRedirect', 10, 3 );  

if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'before_widget' => '<li id="%1$s" class="widget %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h2 class="widgettitle">',
        'after_title' => '</h2>',
    ));

// Änderung Andeas Zeitler 03.09.2010 00:29
// add more link to excerpt
function new_excerpt_more($more) {
       global $post;
	//return '<p><a href="'. get_permalink($post->ID) . '">' . '<<< More >>>' . '</a></p>';
	return '&nbsp;<a class="morelink" href="'. get_permalink($post->ID) . '">' . '[---]' . '</a>';
}
add_filter('excerpt_more', 'new_excerpt_more');

// Änderung Andeas Zeitler 03.01.2011 22:43
// Neuer shortcode für caption


/**
 * The Caption shortcode.
 *
 * Allows a plugin to replace the content that would otherwise be returned. The
 * filter is 'img_caption_shortcode' and passes an empty string, the attr
 * parameter and the content parameter values.
 *
 * The supported attributes for the shortcode are 'id', 'align', 'width', and
 * 'caption'.
 *
 * @since 2.6.0
 *
 * @param array $attr Attributes attributed to the shortcode.
 * @param string $content Optional. Shortcode content.
 * @return string
 */
function az_img_caption_shortcode($attr, $content = null) {

	// Allow plugins/themes to override the default caption template.
	$output = apply_filters('img_caption_shortcode', '', $attr, $content);
	if ( $output != '' )
		return $output;

	extract(shortcode_atts(array(
		'id'	=> '',
		'align'	=> 'alignnone',
		'width'	=> '',
		'caption' => ''
	), $attr));

	if ( 1 > (int) $width || empty($caption) )
		return $content;

	if ( $id ) $id = 'id="' . esc_attr($id) . '" ';

	if (!(strpos($align,'left')===FALSE)) $al='left';
	elseif (!(strpos($align,'right')===FALSE)) $al='right';
	
	$co=do_shortcode( $content );
	$html = "<p class=\"lw-caption-$al\">
				$co
				<span class=\"lw-caption-text-$al\">
					$caption
				</span>
			</p>";
	
	return $html;
	/*return '<div ' . $id . 'class="wp-caption ' . esc_attr($align) . '">'
	. do_shortcode( $content ) . '<p class="wp-caption-text">' . $caption . '</p></div>'
	. '<p style="clear:both"></p>';*/
}


add_shortcode('wp_caption', 'az_img_caption_shortcode');
add_shortcode('caption', 'az_img_caption_shortcode');


// Änderung Andeas Zeitler 22.01.2011 15:00
// Filter neuen post um Windows Live Writer Dreck - inline styles für images wegzuräumen

// WICHTIG: debug Ausgaben verursachen eine Fehlermeldung im Live Writer, weil die response dadurch vermurxt wird!
// WICHTIG: wegen des Filters funktioniert die Designerkennung in LiveWriter nicht, ggf. also den hook deaktivieren
// Update: Filter wird bei Designerkennung nun automatisch weggelassen, s.u.

function filters_rpc_post( $data , $postarr )
{
		//return($data); // debug
	
	// finde das style="margin: 5px 0px 8px 10px; display: inline; float: right" Attribut beim img o.ä., das der Live Writer
	// aus dem CSS des themes generiert und lösche es. 	
	// das Suchmuster \\\" findet \" im post_content, Anführungsszeichen sind dort nämlich schon escaped
	// das U am Ende des Suchmusters, nach dem delimiter macht die Suche ungreedy, damit beim nächsten \" aufgehört wird, sonst löscht
	// die Anfrage alles bis zum letzten \" des post_contents
  $data['post_content'] = preg_replace('/img style=\\\"(.*)\"/U', 'img ', $data['post_content']);
  
	 // hspace von Blogdesk entfernen
	 $data['post_content'] = preg_replace('/hspace=\\\"0\\\"/', ' ', $data['post_content']);
	 $data['post_content'] = preg_replace('/border=\\\"0\\\"/', ' ', $data['post_content']);
 	// Falls azimg und lightbox schon da sind, vorher löschen (doppelte vermeiden)
	 $data['post_content'] = preg_replace('/class=\\\"azimg\\\"/', ' ', $data['post_content']);
	 $data['post_content'] = preg_replace('/rel=\\\"lightbox\[1\]\\\"/', ' ', $data['post_content']);

	// class="azimg" und rel="lightbox[1]" einhängen, damit Bilder nicht um 2px raufspringen, wie die anderen links
	// zwischen <a und .jpg darf kein close oder open tag < > vorkommen, sonst findet er .jpg weiter unten, das gar nicht zu dem <a tag gehört
	// d.h. es würden auch <a links, die kein img beinhten die Klasse azimg bekommen
 	$data['post_content'] = preg_replace('§(<a)([^>]+)(\.jpg)§U', '<a class=\"azimg\" rel=\"lightbox[1]\" ${2}${3}', $data['post_content']);
 	
 	/*$result=array();
 	preg_match_all('§(<a)([^>]+)(\.jpg)§U', $data['post_content'], $result);
 	$deb = print_r($result, true);
 	debug($deb);
 	preg_match_all('§(<a)([^>]+)(\.png)§U', $data['post_content'], $result);
 	$deb = print_r($result, true);*/
 
 
  $data['post_content'] = preg_replace('/align=\\\"right\\\"/', 'class=\"alignright\"', $data['post_content']);
  $data['post_content'] = preg_replace('/align=\\\"left\\\"/', 'class=\"alignleft\"', $data['post_content']);
  //.preg_match('/style=\\\"(.+)\"/', $data['post_content']);
	$data['post_content'] = preg_replace('/lightbox\[\]/', 'lightbox[1]', $data['post_content']);
 

  return ( $data );
}

function filter_normal_post ( $data , $postarr )
{
 	// Falls azimg und lightbox schon da sind, vorher löschen (doppelte vermeiden)
	 $data['post_content'] = preg_replace('/class=\\\"azimg\\\"/', '', $data['post_content']);
	 $data['post_content'] = preg_replace('/rel=\\\"lightbox\[1\]\\\"/', '', $data['post_content']);
	// class="azimg" und rel="lightbox[1]" einhängen, damit Bilder nicht um 2px raufspringen, wie die anderen links
	// zwischen <a und .jpg darf kein close oder open tag < > vorkommen, sonst findet er .jpg weiter unten, das gar nicht zu dem <a tag gehört
	// d.h. es würden auch <a links, die kein img beinhten die Klasse azimg bekommen
 	$data['post_content'] = preg_replace('§(<a)([^>]+)(\.jpg)§U', '<a class=\"azimg\" rel=\"lightbox[1]\"${2}${3}', $data['post_content']);
	$data['post_content'] = preg_replace('/lightbox\[\]/', 'lightbox[1]', $data['post_content']);
	return ($data);
}

function extract_caption ( $meta, $file, $sourceImageType )
{
		//debug(print_r($meta,true));
		
}

function copy_caption ( $post, $attachment ) 
{
	//debug(print_r($post,true));
	//debug(print_r($attachment,true));
	if ($post['post_content']!='' && trim($post['post_excerpt'])=='') {
		$post['post_excerpt'] = $post['post_content'];
	}
	return($post);
}


function my_xmlrpc_call ( $req_method ) {

	/*$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );*/
	
	if ($req_method == 'metaWeblog.editPost' ||  $req_method == 'metaWeblog.newPost') {
		// Filter post_data nur, wenn es ein xmlrpc_call war (von Live Writer)
		add_filter ( 'wp_insert_post_data' , 'filter_rpc_post');
	}
	
	//debug(print_r($req_method,true));
	return($req_method);
}

add_filter ( 'wp_insert_post_data' , 'filter_normal_post');
//add_filter ( 'wp_read_image_metadata' , 'extract_caption');
add_filter('attachment_fields_to_save', 'copy_caption');


// Filter wird bei live writer request zur Designerkennung automatisch weggelassen
// HINWEIS: lt. php net kann es sein, dass das Lesen vom stream nur 1x funktioniert. Dann wäre das ein Problem, weil
// php://input im xmlrpc nochmal gelesen wird. Funkt auf meinem webspace aber.


$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );
if (false === strpos($HTTP_RAW_POST_DATA, "Designerkennung"))
	add_filter ( 'xmlrpc_call' , 'my_xmlrpc_call');
	
add_editor_style();

?>