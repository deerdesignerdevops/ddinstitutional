<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	// Dynamically get version number of the parent stylesheet (lets browsers re-cache your stylesheet when you update your theme)
	$theme   = wp_get_theme( 'HelloElementorChild' );
	$version = $theme->get( 'Version' );

	// CSS
	wp_enqueue_style( 'custom', get_stylesheet_directory_uri() . '/style.css', array( 'hello-elementor-theme-style' ), $version );
	wp_enqueue_style( 'slick', get_stylesheet_directory_uri() . '/libs/slick/css/slick.css', $version );
	wp_enqueue_style( 'slick-theme', get_stylesheet_directory_uri() . '/libs/slick/css/slick-theme.css', $version );

	//JS
	wp_enqueue_script('jquery', get_stylesheet_directory_uri() . '/libs/jquery/jquery.js', $version);
	wp_enqueue_script('slick', get_stylesheet_directory_uri() . '/libs/slick/js/slick.min.js', $version);
	wp_enqueue_script('custom', get_stylesheet_directory_uri() . '/scripts.js', $version);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );


//STRIPE LIB
require_once('libs/stripe/init.php');


function sendStripeNotificationPaymentUpdatedToSlack($req){
	//SLACK PUSH NOTIFICATION
	$slackUrl = SLACK_WEBHOOK_URL;
	$slackMessageBody = [
		'text'  => '<!channel> - Payment Succeeded :white_check_mark:
Client: ' . $req['name'] . '
Email: ' . $req['email'],
		'username' => 'Marcus',
	];


	wp_remote_post( $slackUrl, array(
		'body'        => wp_json_encode( $slackMessageBody ),
		'headers' => array(
			'Content-type: application/json'
		),
	) );
}



//NEW ENDPOINT
add_action( 'rest_api_init', function () {
  register_rest_route( '/stripe/v1','paymentcheck', array(
    'methods' => 'POST',
    'callback' => 'sendStripeNotificationPaymentUpdatedToSlack',
  ) );
} );


function stripeInvoiceGenerationWebhook($req){
	$invoiceId = $req['data']['object']['id'];
	$response_data_arr = file_get_contents('php://input');	
	file_put_contents("wp-content/uploads/stripe_webhooks_logs/stripe_response_".date('Y_m_d')."_".$invoiceId.".log", $response_data_arr);
}

add_action( 'rest_api_init', function () {
  register_rest_route( '/stripe/v1','invoicegenerated', array(
    'methods' => 'POST',
    'callback' => 'stripeInvoiceGenerationWebhook',
  ) );
} );

function hideHeaderAndFooterByPage(){
	global $post;
	if ( $post->post_parent === 13609 ){
		echo "<style>
			header, footer{display: none !important;}
		</style>";
	};
}
add_action("wp_head", "hideHeaderAndFooterByPage");








