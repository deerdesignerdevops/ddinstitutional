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
	$version = rand(111,999);

	// CSS
	wp_enqueue_style( 'dd-custom', get_stylesheet_directory_uri() . '/style.css', array( 'hello-elementor-theme-style' ), $version, 'all' );

	//JS
	wp_enqueue_script('dd-custom', get_stylesheet_directory_uri() . '/scripts.js', array(), $version, 'all');

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );


// keep WP from resizing images 
add_filter( 'big_image_size_threshold', '__return_false' ); 


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


function addCustomCodeAfterImgInBlogPosts(){
	if(is_singular('post')){
		$spotifyUrl = get_field('spotify_button');
		$spotifyBtnImg = get_stylesheet_directory_uri() . '/assets/images/spotify.png';

		if($spotifyUrl){ 
			echo "
			<script>
				document.addEventListener('DOMContentLoaded', function(){
					const postContentImg = document.querySelector('#dd__post_content figure')
					console.log('postContentImg: ', postContentImg)
	
					let spotifyButtonWrapper = document.createElement('div');
					spotifyButtonWrapper.classList.add('spotify__button_wrapper');

					let spotifyButton = document.createElement('a');
					spotifyButton.classList.add('spotify__button');
					spotifyButton.setAttribute('target', '_blank');
					spotifyButton.href = '$spotifyUrl';

					let spotifyBtnImg = document.createElement('img');
					spotifyBtnImg.src = '$spotifyBtnImg';
					
					spotifyButtonWrapper.appendChild(spotifyButton);
					spotifyButton.appendChild(spotifyBtnImg);
					postContentImg.after(spotifyButtonWrapper);
				})
			</script>";
		 }
	}
}

add_action('template_redirect', 'addCustomCodeAfterImgInBlogPosts');



function getUrlReferralParamsAndSaveCookie(){
    if(isset($_GET['referral_id'])){
        $cookieName = "dd_referral_id";
        $cookieValue = $_GET['referral_id'];
        setcookie($cookieName, $cookieValue, time() + (86400 * 30), "/");
    }
	else if(isset($_GET['sld'])){
        $cookieName = "dd_affiliate_id";
        $cookieValue = $_GET['sld'];
        setcookie($cookieName, $cookieValue, time() + (86400 * 30), "/");
    }
}
add_action('template_redirect', 'getUrlReferralParamsAndSaveCookie');