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



function stripeInvoiceGenerationWebhook($req){
	$invoiceId = $req['data']['object']['id'];
	$response_data_arr = file_get_contents('php://input');	
	file_put_contents("wp-content/uploads/stripe_webhooks_logs/stripe_response_".date('Y_m_d')."_".$invoiceId.".log", $response_data_arr);
}

//NEW ENDPOINT FOR STRIPE INVOICE WEBHOOK
add_action( 'rest_api_init', function () {
  register_rest_route( '/stripe/v1','invoicegenerated', array(
    'methods' => 'POST',
    'callback' => 'stripeInvoiceGenerationWebhook',
  ) );
} );



function googleTagManagerOnHead(){
	echo "
	<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-W9B92LV');</script>
<!-- End Google Tag Manager -->
";
}
add_action("wp_head", "googleTagManagerOnHead");



function googleTagManagerOnBody(){
	echo '
	<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-W9B92LV"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
';
}
add_action('wp_body_open', 'googleTagManagerOnBody');


function removePageTitleFromAllPages($return){
	return false;
}
add_filter('hello_elementor_page_title', 'removePageTitleFromAllPages');


function subscribeUserToMoosendEmailListFromQuizForm($entryId, $formData, $form){
	if($form->id === 7){

		$user_name = $formData['quiz_user_name'];
		$user_email = $formData['quiz_user_email'];
		$user_company_url = $formData['quiz_user_url'];
		$user_client_type = $formData['quiz_user_type'];
		$user_team_size = $formData['quiz_user_team_size'];
		$time_spent = $formData["deer_designer_quiz_total_hours"];
		$total_week = $formData["deer_designer_quiz_total_money_week"];
		$total_month = $formData["deer_designer_quiz_total_money_month"];
		
		$user_client_type_final = isset($formData['quiz_user_type_other']) ? $formData['quiz_user_type_other'] : $user_client_type;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, MOOSEND_API_URL);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json',
			'Accept: application/json',
		]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"Name\" : \"$user_name\",\n    \"Email\" : \"$user_email\",\n    \"HasExternalDoubleOptIn\": false,\n    \"CustomFields\": [\n        \"Company Website=$user_company_url\",\n        \"Client Type=$user_client_type_final\",\n        \"Team Size=$user_team_size\",\n        \"Time Spent=$time_spent\",\n        \"Total Week=$$total_week\",\n        \"Total Month=$$total_month\"   ]}");

		curl_exec($ch);

		curl_close($ch);
	}
}
add_action( 'fluentform/submission_inserted', 'subscribeUserToMoosendEmailListFromQuizForm', 20, 3);
