<?php
/**
 * @package Prospress
 * @author Brent Shepherd
 * @version 0.1
 */

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
 * GLOBAL CONSTANTS
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
/* Define the path and url of the Prospress plugins directory */
//define( 'PP_PLUGIN_DIR', WP_PLUGIN_DIR . '/prospress' );
//define( 'PP_PLUGIN_URL', WP_PLUGIN_URL . '/prospress' );

/* Define the current version number for checking if DB tables are up to date. */
//define( 'PP_CORE_DB_VERSION', '0001' );

//define('PP_DB_PREFIX', 'pp_');

/* Place your custom code (actions/filters) in a file called /plugins/pp-custom.php and it will be loaded before anything else. */
//if ( file_exists( WP_PLUGIN_DIR . '/pp-custom.php' ) )
//	require( WP_PLUGIN_DIR . '/pp-custom.php' );


if( !defined( 'PP_CORE_DIR' ) )
	define( 'PP_CORE_DIR', WP_PLUGIN_DIR . '/prospress/pp-core' );

// Include currency functions, class and global vars
// ** THIS CLASS CREATED A BAZOOKA FOR KILLING ANTS... A FEW SIMPLER FUNCTIONS HAVE BEEN ADDED TO THIS FILE
//if ( file_exists( PP_CORE_DIR . '/money.class.php' ) )
//	require_once( PP_CORE_DIR . '/money.class.php' );


/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * REMOVING WP DASHBOARD WIDGETS TO MAKE SPACE FOR PROSPRESS WIDGETS - WHICH ARE ADDED IN EACH COMPONENT
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
function pp_remove_wp_dashboard_widgets() {
	global $wp_meta_boxes;

	// Remove the main column widgets
	//unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
	//unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
	//unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
	unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);

	// Remove the side column widgets
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
	//unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_recent_drafts']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
	unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);

}
add_action('wp_dashboard_setup', 'pp_remove_wp_dashboard_widgets' );

/* * * * * * * * * * * * * EXAMPLE CODE TO ADD WIDGETS * * * * * * * * * * * * * * * * * *
// The function to output the contents of PP Dashboard Widget
function example_dashboard_widget_function() {
	// Display whatever it is you want to show
	echo "Hello World, I'm a great Dashboard Widget";
}

// Create the function use in the action hook
function example_add_dashboard_widgets() {
	wp_add_dashboard_widget('example_dashboard_widget', 'Example Dashboard Widget', 'example_dashboard_widget_function');	
} 

// Hoook into the 'wp_dashboard_setup' action to register our other functions
add_action('wp_dashboard_setup', 'example_add_dashboard_widgets' );


* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


/************************************************************************************************************************/
/**** MONEY FORMAT FUNCTIONS ****/
/************************************************************************************************************************/

/** 
 * Global currencies variable for storing all currencies available in the marketplace.
 * 
 * To make a new currency available for your marketplace, add an array to this variable. 
 * The key for this array must be the currency's ISO 4217 code. The array must contain the currency 
 * name and symbol. 
 * e.g. $currencies['CAD'] = array( 'currency' => __('Canadian Dollar'), 'symbol' => '&#36;' ).
 * 
 * Once added, the currency will be available for selection from the admin page.
 * 
 * @package Prospress Currency
 * @since 0.1
 */
global $currencies, $currency, $currency_symbol;

$currencies = array(
	'AUD' => array( 'currency' => __('Australian Dollar'), 'symbol' => '&#36;' ),
	'GBP' => array( 'currency' => __('British Pound'), 'symbol' => '&#163;' ),
	'CNY' => array( 'currency' => __('Chinese Yuan'), 'symbol' => '&#165;' ),
	'EUR' => array( 'currency' => __('Euro'), 'symbol' => '&#8364;' ),
	'INR' => array( 'currency' => __('Indian Rupee'), 'symbol' => 'Rs' ),
	'JPY' => array( 'currency' => __('Japanese Yen'), 'symbol' => '&#165;' ),
	'USD' => array( 'currency' => __('United States Dollar'), 'symbol' => '&#36;' )
	);

$currency = get_option( 'currency_type' );

$currency_symbol = $currencies[ $currency ][ 'symbol' ];

// Administration functions for choosing default currency (may be set by locale in future, like number format is already)
function pp_add_currency_admin(){
	if ( function_exists( 'add_settings_section' ) ){
		add_settings_section( 'currency', 'Currency', 'pp_currency_settings_section', 'general' );
	} else {
		$bid_settings_page = add_submenu_page( 'options-general.php', 'Currency', 'Currency', 58, 'currency', 'pp_currency_settings_section' );
	}
}
add_action( 'admin_menu', 'pp_add_currency_admin' );

// Displays the fields for handling currency default options
function pp_currency_settings_section() {
	global $currencies;
	?>
	<p><?php _e('Please choose a default currency and where the symbol for this currency should be positioned.'); ?></p>
	<table class='form-table'>
		<tr>
			<th scope="row"><?php _e('Currency Type'); ?>:</th>
			<td>
				<select id='currency_type' name='currency_type'>
				<?php
				$currency_type = get_option( 'currency_type' );
				foreach( $currencies as $code => $currency ) {
				?>
					<option value='<?php echo $code; ?>' <?php echo ($currency_type == $code) ? 'selected="selected"': ''; ?> >
						<?php echo $currency[ 'currency' ]; ?> (<?php echo $code . ', ' . $currency['symbol']; ?>)
					</option>
		<?php	} ?>
				</select>
			</td>
		</tr>
		<tr>
			<th scope="row"><?php echo _e('Symbol Location');?>:</th>
			<td>
				<?php
				$currency_sign_location = get_option('currency_sign_location');
				switch( $currency_sign_location ) {
					case 1:
						$csl1 = "checked ='checked'";
						break;
					case 2:
						$csl2 = "checked ='checked'";
						break;
					case 3:
						$csl3 = "checked ='checked'";
						break;
					case 4:
						$csl4 = "checked ='checked'";
						break;
					default:
						$csl1 = 'checked ="checked"';
						break;
				}
				$currency_sign = $currencies[$currency_type]['symbol'];
				?>
				<input type='radio' value='1' name='currency_sign_location' id='csl1' <?php echo $csl1; ?> /> 
				<label for='csl1'><?php echo $currency_sign; ?>100</label>
				<input type='radio' value='2' name='currency_sign_location' id='csl2' <?php echo $csl2; ?> /> 
				<label for='csl2'><?php echo $currency_sign; ?>&nbsp;100</label>
				<input type='radio' value='3' name='currency_sign_location' id='csl3' <?php echo $csl3; ?> /> 
				<label for='csl3'>100<?php echo $currency_sign; ?></label>
				<input type='radio' value='4' name='currency_sign_location' id='csl4' <?php echo $csl4; ?> /> 
				<label for='csl4'>100&nbsp;<?php echo $currency_sign; ?></label>
			</td>
		</tr>
	</table>
<?php
}

function currency_admin_option( $whitelist_options ) {
	$whitelist_options['general'][] = 'currency_type';
	$whitelist_options['general'][] = 'currency_sign_location';
	return $whitelist_options;
}
add_filter( 'whitelist_options', 'currency_admin_option' );

/** 
 * Function for transforming a number into a monetary formatted number, complete with currency symbol.
 * 
 * @param number int | float
 * @param decimals int | float optional number of decimal places
 * @param currency string optional ISO 4217 code representing the currency. eg. for Japanese Yen, $currency == 'JPY'. If left empty, the currency stored in the options table will be used.
 **/
// Takes an int or float representing number and returns it as a string with currency symbol and formatted in locale suitable number format
function pp_money_format( $number, $decimals = 2, $currency = '' ){
	global $currencies, $currency_symbol;

	$currency = strtoupper( $currency );
	
	if( empty( $currency ) || !array_key_exists( $currency, $currencies ) ) //$currency_sym = $currencies[ get_option( 'currency_type' ) ][ 'symbol' ];
		$currency_sym = $currency_symbol;
	else
		$currency_sym = $currencies[ $currency ][ 'symbol' ];

	switch ( get_option( 'currency_sign_location' ) ) {
		case 1:
			$money = $currency_sym . number_format_i18n( $number, $decimals );
			break;
		case 2:
			$money = $currency_sym . ' ' . number_format_i18n( $number, $decimals );
			break;
		case 3:
			$money = number_format_i18n( $number, $decimals ) . $currency_sym;
			break;
		case 4:
			$money = number_format_i18n( $number, $decimals ) . ' ' . $currency_sym;
			break;
		default:
			$money = $currency_sym . number_format_i18n( $number, $decimals );
			break;
	}
	
	return $money;
}



/************************************************************************************************************************/
/**** ALLOW FOR A THEME TO CUSTOMISE THE APPEARANCE OF ADMIN AND LOGIN PAGES ****/
/************************************************************************************************************************/
//Overload admin CSS to allow themes to customize admin area
function pp_admin_css() {
	echo "<link rel='stylesheet' type='text/css' href='" . get_bloginfo('stylesheet_directory') . "/admin.css' />\n";
}
add_filter('admin_head', 'pp_admin_css');

//Overload login CSS to allow themes to customize login screen
function pp_login_css() {
	echo "<link rel='stylesheet' type='text/css' href='" . get_bloginfo('stylesheet_directory') . "/login.css' />\n";
}
add_action('login_head', 'pp_login_css');

//Overload login URL
function pp_login_url() {
	echo "http://www.prospress.com";
}
add_filter('login_headerurl', 'pp_login_url' ); 

//Overload login image
function pp_login_alt() {
	//echo get_option('mm_login_alt');
}
add_filter('login_headertitle', 'pp_login_alt' );


//Add Marketplace Logo upload box to General Settings page, like on wordpress.com, 
//this can be used on the prospress.com home page and top left of admin pages

?>