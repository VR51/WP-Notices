<?php
/*
Plugin Name: WP Notices
Contributors: leehodson
Author: Lee Hodson
Author URI: https://vr51.com
Tags: notices,messages,members,membership,timed,regular,periodic
Plugin URI: https://github.com/VR51/WP-Notices
Donate link: https://paypal.me/vr51
Description: Display notice messages to visitors, admin users, editors, contributors and anonymous readers. Notices can last forever, display between specific dates or at specified times of specified days regularly. Automatically convert notices to images if desired.
Requires at least: 4.0.0
Tested up to: 4.5.3
Stable tag: 1.2.0
License: GPL3
*/

/**
*
*	WP Notices
*
*	[wp-notice to='admin' user='false' class='alert-info' start='Monday 12pm' end='Monday 6pm' image='basic' html5='true']Message[/wp-notice]
*
*		@to		= The user role, capability or username the message should display to. Options are
*					anon, anonymous, admin, administrator, editor, author, contributor, subscriber
*					or any WordPress capability, WordPress user role or site username. Defaults to admin user.
*					Use @ to direct a notice to a specific username e.g. @john or @paul or @username.
*		@class	= (optional) Message type or any CSS class(es). This determines the display style of the message. Default options are
*					alert-success, alert-info, alert-warning and alert-danger.
*		@css	= Add custom CSS to load inline or load the custom stylesheet stored in wp-content/uploads/wp-notices/css/custom.css. Use @ to load a file e.g. css='@'.
*		@start	= (optional) Full date, or day, or month, or year etc... for publication to begin. Accepts PHP natural time language.
*		@end =  (optional) Full date, or day, or month, or year etc... for publication to expire. Accepts PHP natural time language.
*				Relative Time reference: http://php.net/manual/en/datetime.formats.relative.php
*		@image	= (optional) Convert the message to an image to prevent search engine indexing of the text within the notice when the notice is set.
*					Requires ImageMagick to be enabled on the server. No ImageMagick equals no image support.
*					Options are 'landscape' or 'portrait' or @number e.g. @300. Use an @number value to specify a custom width. Measurement is in points (not pixels).
*		@format	= (optional) Specify the size format for the image e.g. A4, B4, C4, letter etc... More details are in the help file.
*					When image='@' e.g. image='@300', use a number in the format='' attribute to specify a custom image height e.g. image='@200' format='300'. Notice format does not require an @ sign.
*		@files	= (optional) Show links to notice message HTML, PDF and PNG files. These files are available only when the notice is converted to an image. Set to files='true' to show download links.
*		@html5	= (optional) Enable or disable HTML5 support. Default is true (enabled). Disable if it causes bugs.
*
*		@help	= (optional) Display link to shortcode help page and help messages (if any). Accepts a user role, user capability, username (@username) or admin.
*
*		Help does not display to anonymous readers
*		Messages can only be displayed to a single user or capability type or user role, not a mix of each.
*		Display to all logged in readers by setting to='loggedin'.
*		Display to all readers, logged in or not,  by setting to='everyone'.
*
*		WP Notices has been tested. It is known to work. If you find a bug, let us know.
*
*		Database option key prefix = vr_wp_notices_
*
*		CSS Classes: wp-notices, wp-notices-image, wp-notices-links
*
**/

/**
*
* Security First!
*
**/
if ( !function_exists( 'add_action' ) ) {
	echo "Hi there! I'm just a plugin, not much I can do when called directly.";
	exit;
}


/**
*
*	Activation Routine
*
**/

function vr_wp_notices_install() {

	// Check for dompdf directory, rename it if it exists
	if ( file_exists( plugin_dir_path( __FILE__ ).'includes/dompdf' ) ) {
		// Register database option to store random directory name for dompdf and rename dompdf directory (security precaution)
		$locationDOMPDF = plugin_dir_path( __FILE__ ).'includes/'.hash( 'sha1', mt_rand() );
		update_option( 'vr_wp_notices_dompdf', "$locationDOMPDF" );
		$locationDOMPDF = get_option('vr_wp_notices_dompdf');
		rename( plugin_dir_path( __FILE__ ).'includes/dompdf', "$locationDOMPDF" );
	}
	
	// Create WP Notices directory path and URL in wp-content/wp-notices and create db options to store them
	$upload_dir = wp_upload_dir(); 
	$wp_notices_directory = $upload_dir['basedir'].'/wp-notices';
	update_option( 'vr_wp_notices_directory', "$wp_notices_directory" );
	$wp_notices_directory_url = content_url().'/uploads/wp-notices';
	update_option( 'vr_wp_notices_directory_url', "$wp_notices_directory_url" );
	
	// Create needed directories and files
	if( !file_exists( "$wp_notices_directory" ) ) { wp_mkdir_p( "$wp_notices_directory" ); }
	if( !file_exists( "$wp_notices_directory/css" ) ) { wp_mkdir_p( "$wp_notices_directory/css" ); file_put_contents( "$wp_notices_directory/css/custom.css", ""); }
	if( !file_exists( "$wp_notices_directory/tmp" ) ) { wp_mkdir_p( "$wp_notices_directory/tmp" ); }
	
	// Add cron job
	if (! wp_next_scheduled ( 'vr_wp_notices_cron' )) {
		wp_schedule_event(time(), 'hourly', 'vr_wp_notices_cron');
	}
	
}
register_activation_hook( __FILE__, 'vr_wp_notices_install' );


/**
*
*	Deactivation Routine
*
**/

function my_deactivation() {
	wp_clear_scheduled_hook('vr_wp_notices_cron');
}
register_deactivation_hook(__FILE__, 'vr_wp_notices_deactivate');


/**
*
*	Uninstallation Routine
*
**/

function vr_wp_notices_uninstall() {
 
    // Delete WP Notices database options
	delete_option( 'vr_wp_notices_dompdf' );
	delete_option('vr_wp_notices_directory');
	delete_option( 'vr_wp_notices_directory_url' );
}
register_uninstall_hook( __FILE__, 'vr_wp_notices_uninstall' );


/**
*
* Load DOMPDF for PDF Generation. PDFs are converted to images when image='' is set.
*
**/

// Use dompdf to generate PDF of the content (which will later be converted to an image)
if ( file_exists( plugin_dir_path( __FILE__ ).'includes/dompdf/autoload.inc.php' ) ) {
	require_once plugin_dir_path( __FILE__ ).'includes/dompdf/autoload.inc.php';
} else {
	require_once get_option('vr_wp_notices_dompdf').'/autoload.inc.php';
}
// reference the Dompdf namespace
use Dompdf\Dompdf;


/**
*
* Create the shortcode
*
**/

function vr_wp_notices_shortcode( $atts, $content='' ) {

	/**
	*
	*	Set allowed shortcode attributes and default values
	*
	**/
 
	$atts = shortcode_atts(
		array(
 
			'to' => 'administrator', // User role https://codex.wordpress.org/Roles_and_Capabilities (options: any WordPress user role, any WordPress capability, @ any username and the aliases: admin, loggedin and everyone)
			'class' => '', // Plugin style class. No commas or full-stops needed (in built options: alert, alert-info, alert-success, alert-danger and alert-warning)
			'css' => '', // CSS file to load or styles to use inline.
			'start' => '', // Message launch date
			'end' => 'Tomorrow', // Message expiry date
			'image' => '', // Convert the notice to an image (options: portrait or landscape)
			'format' => 'c4', // Set the PDF page size
			'files' => '', // Show file download links
			'html5' => 'true', // Enable HTML5 support (pptions: are true or false).
			'help' => '' // Display help reference information for this shortcode
 
		), $atts, 'wp-notice'
	);

	/**
	*
	*	Sanitize user input.
	*	Create aliases for roles and capabilities.
	*
	**/
 
	$to = sanitize_user( $atts['to'], 1 );
		if ( $to == 'admin' ) { $to='administrator'; } // Set admin alias
		if ( $to == 'anon' ) { $to='anonymous'; } // Set anon alias
		if ( $to == 'loggedin' ) { $to='read'; } // Set loggedin alias
	$class = sanitize_text_field( $atts['class'] );
	$css = wp_strip_all_tags( $atts['css'] );
	$start = sanitize_text_field( $atts['start'] );
	$end = sanitize_text_field( $atts['end'] );
	$image = sanitize_text_field( $atts['image'] );
	$format = sanitize_text_field( $atts['format'] );
	$files = sanitize_text_field( $atts['files'] );
	$html5 = sanitize_text_field( $atts['html5'] );

	$help = sanitize_text_field( $atts['help'] );
		if ( $help == 'admin' ) { $help='administrator'; } // Set admin alias
	$content = wp_kses_post( $content );

	// Load Script Files in Footer
	add_action( 'get_footer', 'register_vr_wp_notices_files', 1 );


	/**
	*
	*	Get DB options, create file name and build download links
	*
	**/

	$wp_notices_directory = get_option( 'vr_wp_notices_directory' );
	$wp_notices_directory_url = get_option( 'vr_wp_notices_directory_url' );
	$file_name = basename(get_permalink()).'-'.mt_rand(); // Add the extension at point of use.
	
	$downloadLinks = "<p>Download: <a href='$wp_notices_directory_url/tmp/".$file_name.".html'>HTML</a> | <a href='$wp_notices_directory_url/tmp/".$file_name.".pdf'>PDF</a> | <a href='$wp_notices_directory_url/tmp/".$file_name.".png'>PNG</a><br><small>These links expire hourly.</small></p>";


	/**
	*
	*	Use the input
	*
	**/


	// Does the user need help?
	if ( $help ) {
	
		$help_file = plugin_dir_url( __FILE__ ).'includes/help.html';
		if ( ! extension_loaded('imagick') ) { $nomagick = '<p>Image creation requires ImageMagick support. Please enable ImageMagick in your server\s PHP configurations.</p>'; } else { $nomagick = ''; }

		// Display to username?
		if ( substr($help, 0, 1) == '@' ) {
			$user = substr($help, 1);
			$current_user = wp_get_current_user();
			if ( "$user" == $current_user->user_login ) {
				$help = "<div class='alert alert-info'><a href='$help_file' target='_blank'>SHORTCODE USAGE HELP</a></div>".$nomagick;
			}
		} // Display to userole?
		elseif ( current_user_can( "$help" ) ) {
				$help = "<div class='alert alert-info'><a href='$help_file' target='_blank'>SHORTCODE USAGE HELP</a></div>".$nomagick;
		}
		else {
			$help = '';
		}

	}


	/**
	*
	* Format $output
	*
	**/

	// Set CSS
	if ( substr($css, 0, 1) == '@' ) {
		$output = "<div class='wp-notices $class' role='alert'>".do_shortcode($content)."</div>".$help;
		if ( $image == '' ) {
			$css = substr($css, 1);
			function vr_wp_notices_footer_css_link() {
				$wp_notices_directory_url = get_option( 'vr_wp_notices_directory_url' );
				$css = "<link rel='stylesheet' id='vr-wp-notices-css-css-footer' href='$wp_notices_directory_url/css/custom.css' type='text/css' media='all'>";
				echo $css;
			}
			add_action('wp_footer', 'vr_wp_notices_footer_css_link', 99 );
			
		}
	} else {
		$output = "<div class='wp-notices $class' style='$css' role='alert'>".do_shortcode($content)."</div>".$help;
	}


	// Decide who to display $output to

	if ( $to == 'everyone' ) {
		$output = $output;
		$downloadLinks = $downloadLinks;
	}
	elseif ( $to == 'anonymous' && ! is_user_logged_in() ) {
		$output = $output;
		$downloadLinks = $downloadLinks;
	} // Display to username?
	elseif ( substr($to, 0, 1) == '@' ) {
		$user = substr($to, 1);
		$current_user = wp_get_current_user();
		if ( "$user" == $current_user->user_login ) {
			$output = $output;
			$downloadLinks = $downloadLinks;
		}
	} // Display to userole?
	elseif ( current_user_can( "$to" ) ) {
			$output = $output;
	} else {
		$output = '';
		$downloadLinks = '';
	}

	// Decide whether we are in date
	
	if ( ! $start == '' ) {

		$now = time();
		$start = strtotime($start);
		$end = strtotime($end);

		if ( $now < $start ) { $output = ''; }
			elseif ( $now > $end ) { $output = ''; }
			else { $output = $output; }

	}

	// If $output (as $output is decided above) is needed in image format we will convert it to an image whether $output is empty or not.
	if ( $image == 'portrait' || $image == 'landscape' || substr($image, 0, 1) == '@' && extension_loaded('imagick') ) {
	
		/**
		*
		*	Create HTML file
		*	Convert HTML to PDF
		*	Convert PDF to image
		*
		**/
		
		// Create HTML, PDF and PNG file for the notice

		// Determine CSS file to link
		if ( substr($css, 0, 1) == '@' ) {
			$css = substr($css, 1);
			$css = "<link rel='stylesheet' id='vr-wp-notices-css-css' href='$wp_notices_directory_url/css/custom.css' type='text/css' media='all'>";
		}
		else
		{
			$css = '';
		}
		
		// Link to style.css that ships with WP Notices
		if ( ! $class = '' ) {
			$defaultCSS = "<link rel='stylesheet' id='vr-wp-notices-css-css' href='".plugins_url( 'css/style.css', __FILE__ )."' type='text/css' media='all'>";
		}
		else
		{
			$defaultCSS = '';
		}
			
		// Insert $output into HTML doc
		$output='<html '.get_language_attributes().'><head><meta charset="UTF-8">'.$defaultCSS.$css.'</head><body>'.$output.'</body></html>';
		file_put_contents( "$wp_notices_directory/tmp/$file_name.html", "$output");

		/* Convert $output to PDF */
		
		// instantiate and use the dompdf class
		$dompdf = new Dompdf();
		
		$context = stream_context_create([ 
			'ssl' => [ 
				'verify_peer' => FALSE, 
				'verify_peer_name' => FALSE,
				'allow_self_signed'=> TRUE 
			] 
		]);
		$dompdf->setHttpContext($context);
		
		if ( $html5 == 'true' ) {
			$dompdf->set_option('isPhpEnabled', 'TRUE');
		}
		$dompdf->set_option('isRemoteEnabled', 'TRUE');
		
		if ( substr($image, 0, 1) == '@' ) {
			$image = substr($image, 1);
			// Confirm we have numbers
				if ( ! intval($image) ) { $image = '400'; } // Check we have anumber otherwise use 400pt as default
				if ( substr($format, 0, 1) == '@' ) { $format = substr($format, 1); } // Remove @ from $format. Someone's likely to put one in...
				if ( ! intval($format) ) { $format = '400'; }
			$custom = array(0,0,$image,$format);
			$dompdf->setPaper($custom);
		} else {
			$dompdf->setPaper("$format", "$image");
		}
		
		// Process HTML to PDF
		$dompdf->loadHtml($output);
		// $dompdf->loadHtmlFile($wp_notices_directory_url.'/tmp/pdf.html');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		// $output = $dompdf->stream( "output.pdf", array("Attachment" => 0) );
		
		$output = $dompdf->output();
		
		/* Push $output to PDF file */
		$file_pdf = $wp_notices_directory.'/tmp/'.$file_name.'.pdf';
		file_put_contents( "$file_pdf", "$output" );

		// Convert PDF to image
		// ImageMagick reference http://php.net/manual/en/imagick.displayimage.php
		
		$file_png = $wp_notices_directory.'/tmp/'.$file_name.'.png';
		$file_png_url = $wp_notices_directory_url.'/tmp/'.$file_name.'.png';
		
		$imagick = new Imagick();
		$imagick->setResolution(90,90);
		$imagick->setCompressionQuality(100);
		$imagick->readImage($file_pdf);
		$imagick->setImageFormat( "png" );
		$imagick->trimImage(0);
		$imagick->setImagePage(0, 0, 0, 0); 
		$imagick->writeImage($file_png);
		$imagick->clear();
		$imagick->destroy();

		if ( $files ) {
			$output = "<img class='wp-notices-image' src='$file_png_url' alt='' /><div class='wp-notices-links'>$downloadLinks</div>";
		} else {
			$output = "<img class='wp-notices-image' src='$file_png_url' alt='' />";
		}

	}

	return $output;
    // return $output.'Now: '.$now.'<br>Start: '.$start.'<br>End: '.$end.'<br>To: '.$to: // FOR DEBUG
}


/**
*
*	Add the shortcode and scripts but only if we are viewing the front of the site
*
**/

if ( ! is_admin() ) {

	add_filter( 'widget_text', array( $wp_embed, 'run_shortcode' ), 8 );
	add_filter( 'widget_text', array( $wp_embed, 'autoembed'), 8 );

	// Shortcode
	add_shortcode( 'wp-notice', 'vr_wp_notices_shortcode');

	// Register Script Files (loaded in the footer when the shortcode is used).
	function register_vr_wp_notices_files() {
		wp_register_style( 'vr-wp-notices-css', plugins_url( '/css/style.css' , __FILE__ ) );
		wp_enqueue_style( 'vr-wp-notices-css' );
	}
	// add_action( 'wp_enqueue_scripts', 'register_vr_wp_notices_files' );

	// Run cron to clear /wp-content/wp-notices/tmp
	function vr_wp_notices_cron_action() {
		$wp_notices_directory = get_option( 'vr_wp_notices_directory' );
		array_map('unlink', glob("$wp_notices_directory/tmp/*"));
	}
	add_action('vr_wp_notices_cron', 'vr_wp_notices_cron_action');
	
}
