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
Stable tag: 1.0.0
License: GPL3
*/

/**
*
*	WP Notices
*
*	[wp notice to='admin' user='false' class='alert-info' start='Monday 12pm' end='Monday 6pm' image='basic']Message[/notice]
*
*		@to = The user role, capability or username the message should display to. Options are
*					anon, anonymous, admin, administrator, editor, author, contributor, subscriber
*					or any WordPress capability, WordPress user role or site username. Defaults to admin user.
*					Use @ to direct a notice to a specific username e.g. @john or @paul or @username.
*		@class = (optional) Message type or any CSS class(es). This determines the display style of the message. Default options are
*					alert-success, alert-info, alert-warning and alert-danger.
*		@start = (optional) Full date, or day, or month, or year etc... for publication to begin. Accepts PHP natural time language.
*		@end =  (optional) Full date, or day, or month, or year etc... for publication to expire. Accepts PHP natural time language.
*				Relative Time reference: http://php.net/manual/en/datetime.formats.relative.php
*		@image = (optional) Convert the message to an image to prevent search engine indexing of the text within the notice when the notice is set
*					to show publicly. Options are 'landscape' and 'portrait'.
*		@format = (optional) Specify the size format for the image e.g. A4, B4, C4, letter etc... More details are in the help file.
*
*		@help = true or false. Default is false. Display link to shortcode help page.
*
*		Help does not display to anonymous readers
*		Messages can only be displayed to a single user or capability type or user role, not a mix of each.
*		Display to all logged in readers by setting to='loggedin'.
*		Display to all readers, logged in or not,  by setting to='everyone'.
*
*		WP Notices has been tested. It is known to work. If you find a bug, let us know.
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
* Load DOMPDF for PDF Generation. PDFs are converted to images when image='' is set.
*
**/

// Use dompdf to generate PDF of the content
require_once plugin_dir_path( __FILE__ ).'includes/dompdf/autoload.inc.php';

// reference the Dompdf namespace
use Dompdf\Dompdf;


/**
*
* Create the shortcode
*
**/

function vr_wp_notices( $atts, $content='' ) {

	/**
	*
	*	Set allowed shortcode attributes and default values
	*
	**/
 
	$atts = shortcode_atts(
		array(
 
			'to' => 'administrator', // User role https://codex.wordpress.org/Roles_and_Capabilities
			'class' => '', // Plugin style class. No commas or full-stops needed
			'start' => '', // Message launch date
			'end' => 'Tomorrow', // Message expiry date
			'image' => '', // Convert the notice to an image
			'format' => 'c4',
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
	$start = sanitize_text_field( $atts['start'] );
	$end = sanitize_text_field( $atts['end'] );
	$image = sanitize_text_field( $atts['image'] );
	$format = sanitize_text_field( $atts['format'] );

	$help = sanitize_text_field( $atts['help'] );

	$content = wp_kses_post( $content );

	/**
	*
	*	Use the input
	*
	**/

	// Does the user need help?
	if ( $help == 'true' ) {
		$help_file = plugin_dir_url( __FILE__ ).'includes/help.html';
		$help = "<div class='alert alert-info'><a href='$help_file' target='_blank'>SHORTCODE USAGE HELP</a></div>";
	}
 
	// Set $output to null just in case...
	$output = '';

	// Decide who to display $output to

	if ( $to == 'everyone' ) {
		$output = "<div class='$class' role='alert'>$content</div>";
	}

	if ( $to == 'anonymous' && ! is_user_logged_in() ) {
		$output = "<div class='$class' role='alert'>$content</div>";
	}

	// Display to username?
	if ( substr($to, 0, 1) == '@' ) {
		$user = substr($to, 1);
		$current_user = wp_get_current_user();
		if ( "$user" == $current_user->user_login ) {
			$output = "<div class='$class' role='alert'>$content</div>".$help;
		}
	} else {
	// Display to userole?
		if ( current_user_can( "$to" ) ) {
			$output = "<div class='$class' role='alert'>$content</div>".$help;
		}
	}

	if ( ! $start == '' ) {

		$now = time();
		$start = strtotime($start);
		$end = strtotime($end);

		if ( $now < $start ) { $output = ''; }
			elseif ( $now > $end ) { $output = ''; }
			else { $output = $output; }

	}

	// If $output (as $output is decided above) is needed in image format we will convert it to an image whether $output is empty or not.
	if ( $image ) {
	
		// We create HTML, PDF and PNG file for the notice
		
		// Create PDF cache to store PDF files
		$upload_dir = wp_upload_dir(); 
		$pdf_cache = $upload_dir['basedir'].'/wp-notices';
		$pdf_cache_url = content_url().'/uploads/wp-notices';
		$file_name = basename(get_permalink()).'-'.mt_rand().'-'.date('Ymd'); // We add the extension at point of use.

		if( !file_exists( $pdf_cache ) )
			wp_mkdir_p( $pdf_cache );
	
		/* Embed output in HTML framework */
		$output='<html '.get_language_attributes().'><head><meta charset="UTF-8"><link rel="stylesheet" id="vr-wp-notices-css-css" href="'.plugins_url( 'css/style.css', __FILE__ ).'" type="text/css" media="all"></head><body>'.$output.'</body></html>';
		file_put_contents( $pdf_cache.'/'.$file_name.'.html', $output);

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
		
		$dompdf->set_option('isRemoteEnabled', 'TRUE');
		$dompdf->setPaper("$format", "$image");
		// $dompdf->set_base_path(plugin_dir_path( __FILE__ ));
		// $dompdf->set_option('defaultFont', 'Courier');

		
		// Process HTML to PDF
		$dompdf->loadHtml($output);
		// $dompdf->loadHtmlFile($pdf_cache_url.'/pdf.html');

		// Render the HTML as PDF
		$dompdf->render();

		// Output the generated PDF to Browser
		// $output = $dompdf->stream( "output.pdf", array("Attachment" => 0) );
		
		$output = $dompdf->output();
		
		/* Push $output to PDF file */
		$file_pdf = $pdf_cache.'/'.$file_name.'.pdf';
		file_put_contents( $file_pdf, $output);

		/* Convert PDF to image --- oh my gosh this is convoluted more than it needs to be.... lalalalala */
		// ImageMagick reference http://php.net/manual/en/imagick.displayimage.php
		
		$file_png = $pdf_cache.'/'.$file_name.'.png';
		$file_png_url = $pdf_cache_url.'/'.$file_name.'.png';
		
		$imagick = new Imagick();
		$imagick->readImage($file_pdf);
		$imagick->setImageFormat( "png" );
		$imagick->trimImage(0);
		$imagick->setImagePage(0, 0, 0, 0); 
		$imagick->writeImage($file_png);
		$imagick->destroy();
		// $imagick->writeImages($file_png, false);
		// $image = $imagick->getImagesBlob();
		// $output = "<img src='image/png;base64,".base64_encode($image)."' />";
		$output = "<img class='wp-notices' src='$file_png_url' alt='' />";
		
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

	/* Shortcode */
	add_shortcode( 'wp-notice', 'vr_wp_notices');

	/* Scripts */
	function register_vr_wp_notices_files() {
		wp_register_style( 'vr-wp-notices-css', plugins_url( '/css/style.css' , __FILE__ ) );
		wp_enqueue_style( 'vr-wp-notices-css' );
	}

	add_action( 'wp_enqueue_scripts', 'register_vr_wp_notices_files' );
	
	$upload_dir = wp_upload_dir(); 
	$pdf_cache = $upload_dir['basedir'].'/wp-notices';
/*
	$cacheDate = new DateTime(date('Ymd'));
	$cacheDate->sub(new DateInterval('P1D'));
	$cacheWipeDate = $cacheDate->format('Ymd');
	
	array_map('unlink', glob($pdf_cache."/*.".$cacheWipeDate));
*/
	array_map('unlink', glob($pdf_cache."/*"));
	
}
