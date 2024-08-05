<?php
namespace Depicter\Dashboard;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class DashboardSettings {

	/**
	 * Settings Page title.
	 *
	 * @var string
	 */
	private $page_title;

	/**
	 * Menu title.
	 *
	 * @var string
	 */
	private $menu_title;

	/**
	 * Capability to access Settings page.
	 *
	 * @var string
	 */
	private $capability;

	/**
	 * Menu slug.
	 *
	 * @var string
	 */
	private $menu_slug;

	/**
	 * Settings form action.
	 *
	 * @var string
	 */
	private $form_action;

	/**
	 * Option group.
	 *
	 * @var string
	 */
	private $option_group;

	/**
	 * Constructor
	 */
	public function __construct() {
		// parameters.
		$this->page_title = __( 'Depicter Settings', 'depicter' );
		$this->menu_title = __( 'Settings', 'depicter' );
		$this->capability = 'manage_depicter';
		$this->menu_slug  = 'depicter-settings';

		// $this->form_action = 'admin.php?page=depicter-settings';
		$this->form_action = 'options.php';

		$this->option_group = 'depicter_';

		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'add_settings' ) );
	}

	/**
	 * Adds a submenu page to the Settings main menu.
	 */
	public function add_settings_page() {
		/**
		* Params for add_options_page
		*
		* @param  string       $page_title The text to be displayed in the title tags of the page when the menu is selected.
		* @param  string       $menu_title The text to be used for the menu.
		* @param  string       $capability The capability required for this menu to be displayed to the user.
		* @param  string       $menu_slug  The slug name to refer to this menu by (should be unique for this menu).
		* @param  callable     $callback   Optional. The function to be called to output the content for this page.
		* @param  int          $position   Optional. The position in the menu order this item should appear.
		* @return string|false The resulting page's hook_suffix, or false if the user does not have the capability required.
		*/
		// add_options_page(
		// 	$this->page_title,
		// 	$this->menu_title,
		// 	$this->capability,
		// 	$this->menu_slug,
		// 	array( $this, 'settings_page_html' )
		// );

		add_submenu_page(
			'depicter-dashboard',
			$this->page_title,
			$this->menu_title,
			$this->capability,
			$this->menu_slug,
			[ $this, 'settings_page_html' ] );
	}

	/**
	 * Compose settings
	 */
	public function add_settings() {

		// Define Sections ----------------------------------------------------.

		/**
		 * Adds a new section to a settings page.
		 *
		 * Part of the Settings API. Use this to define new settings sections for an admin page.
		 * Show settings sections in your admin page callback function with do_settings_sections().
		 * Add settings fields to your section with add_settings_field().
		 *
		 * The $callback argument should be the name of a function that echoes out any
		 * content you want to show at the top of the settings section before the actual
		 * fields. It can output nothing if you want.
		 *
		 * @param string   $id       Slug-name to identify the section. Used in the 'id' attribute of tags.
		 * @param string   $title    Formatted title of the section. Shown as the heading for the section.
		 * @param callable $callback Function that echos out any content at the top of the section (between heading and fields).
		 * @param string   $page     The slug-name of the settings page on which to show the section. Built-in pages include
		 *                           'general', 'reading', 'writing', 'discussion', 'media', etc. Create your own using
		 *                           add_options_page();
		 */
		add_settings_section(
			'depicter_general_section',
			__( 'General', 'depicter' ),
			null,
			$this->menu_slug
		);

		add_settings_section(
			'depicter_performance_section',
			__( 'Performance', 'depicter' ),
			null,
			$this->menu_slug
		);

		// Input text field ---------------------------------------------------.

		/**
		 * Adds a new field to a section of a settings page.
		 *
		 * Part of the Settings API. Use this to define a settings field that will show
		 * as part of a settings section inside a settings page. The fields are shown using
		 * do_settings_fields() in do_settings_sections().
		 *
		 * The $callback argument should be the name of a function that echoes out the
		 * HTML input tags for this setting field. Use get_option() to retrieve existing
		 * values to show.
		 *
		 * @param string   $id       Slug-name to identify the field. Used in the 'id' attribute of tags.
		 * @param string   $title    Formatted title of the field. Shown as the label for the field
		 *                           during output.
		 * @param callable $callback Function that fills the field with the desired form inputs. The
		 *                           function should echo its output.
		 * @param string   $page     The slug-name of the settings page on which to show the section
		 *                           (general, reading, writing, ...).
		 * @param string   $section  Optional. The slug-name of the section of the settings page
		 *                           in which to show the box. Default 'default'.
		 * @param array    $args     {
		 *                           Optional. Extra arguments used when outputting the field.
		 *
		 *     @type string $label_for When supplied, the setting title will be wrapped
		 *                             in a `<label>` element, its `for` attribute populated
		 *                             with this value.
		 *     @type string $class     CSS Class to be added to the `<tr>` element when the
		 *                             field is output.
		 *
		 */
		add_settings_field(
			'cliowp_sp_input1',
			__( 'Input1 Label', 'depicter' ),
			array( $this, 'input1_html' ),
			$this->menu_slug,
			'depicter_general_section'
		);

		/**
		 * Registers a setting and its data.
		 *
		 * @param string $option_group A settings group name. Should correspond to an allowed option key name.
		 *                             Default allowed option key names include 'general', 'discussion', 'media',
		 *                             'reading', 'writing', and 'options'.
		 * @param string $option_name The name of an option to sanitize and save.
		 * @param array  $args {
		 *     Data used to describe the setting when registered.
		 *
		 *     @type string     $type              The type of data associated with this setting.
		 *                                         Valid values are 'string', 'boolean', 'integer', 'number', 'array', and 'object'.
		 *     @type string     $description       A description of the data attached to this setting.
		 *     @type callable   $sanitize_callback A callback function that sanitizes the option's value.
		 *     @type bool|array $show_in_rest      Whether data associated with this setting should be included in the REST API.
		 *                                         When registering complex settings, this argument may optionally be an
		 *                                         array with a 'schema' key.
		 *     @type mixed      $default           Default value when calling `get_option()`.
		 * }
		 */
		register_setting(
			$this->option_group,
			'cliowp_sp_input1',
			array(
				'sanitize_callback' => array( $this, 'sanitize_input1' ),
				'default'           => 'input1 test',
			)
		);

		// Date field ---------------------------------------------------------.
		add_settings_field(
			'cliowp_sp_date1',
			__( 'Date1 Label', 'depicter' ),
			array( $this, 'date1_html' ),
			$this->menu_slug,
			'depicter_general_section'
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_date1',
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// DateTime field -----------------------------------------------------.
		add_settings_field(
			'cliowp_sp_datetime1',
			__( 'Datetime1 Label', 'depicter' ),
			array( $this, 'datetime1_html' ),
			$this->menu_slug,
			'depicter_general_section'
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_datetime1',
			array(
				'sanitize_callback' => 'sanitize_text_field',
			)
		);

		// Password field -----------------------------------------------------.
		add_settings_field(
			'cliowp_sp_password1',
			__( 'Password1 Label', 'depicter' ),
			array( $this, 'password1_html' ),
			$this->menu_slug,
			'depicter_general_section'
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_password1',
			array(
				'sanitize_callback' => array( $this, 'encrypt_password1' ),
			)
		);

		// Number field -------------------------------------------------------.
		add_settings_field(
			'cliowp_sp_number1',
			__( 'Number1 Label', 'depicter' ),
			array( $this, 'number1_html' ),
			$this->menu_slug,
			'depicter_general_section',
			array(
				'min'  => 1,
				'max'  => 10,
				'step' => 1,
			)
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_number1',
		);

		// Select field -------------------------------------------------------.
		add_settings_field(
			'cliowp_sp_select1',
			__( 'Select1 Label', 'depicter' ),
			array( $this, 'select1_html' ),
			$this->menu_slug,
			'depicter_general_section'
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_select1',
			array(
				'sanitize_callback' => array( $this, 'sanitize_select1' ),
				'default'           => '1',
			)
		);

		// Checkbox field -----------------------------------------------------.
		add_settings_field(
			'cliowp_sp_checkbox1',
			__( 'Checkbox1 Label', 'depicter' ),
			array( $this, 'checkbox1_html' ),
			$this->menu_slug,
			'depicter_general_section'
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_checkbox1',
			array(
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => '1',
			)
		);

		// MultiSelect field --------------------------------------------------.
		add_settings_field(
			'cliowp_sp_multiselect1',
			__( 'MultiSelect1 Label', 'depicter' ),
			array( $this, 'multiselect1_html' ),
			$this->menu_slug,
			'depicter_performance_section'
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_multiselect1',
		);

		// Textarea field -----------------------------------------------------.
		add_settings_field(
			'cliowp_sp_textarea1',
			__( 'Textarea1 Label', 'depicter' ),
			array( $this, 'textarea1_html' ),
			$this->menu_slug,
			'depicter_performance_section',
			array(
				'rows' => 4,
				'cols' => 30,
			)
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_textarea1',
			array(
				'sanitize_callback' => 'sanitize_textarea_field',
			)
		);

		// Color field --------------------------------------------------------.
		add_settings_field(
			'cliowp_sp_color1',
			__( 'Color1 Label', 'depicter' ),
			array( $this, 'color1_html' ),
			$this->menu_slug,
			'depicter_performance_section'
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_color1',
		);

		// WYSIWYG editor field -----------------------------------------------.
		add_settings_field(
			'cliowp_sp_editor1',
			__( 'Editor1 Label', 'depicter' ),
			array( $this, 'editor1_html' ),
			$this->menu_slug,
			'depicter_performance_section',
		);

		register_setting(
			$this->option_group,
			'cliowp_sp_editor1',
			array(
				'sanitize_callback' => 'wp_kses_post',
			)
		);

	}

	/**
	 * Create HTML for input1 field
	 */
	public function input1_html() {         ?>
		<input type="text" name="cliowp_sp_input1" value="<?php echo esc_attr( get_option( 'cliowp_sp_input1' ) ); ?>">
		<?php
	}

	/**
	 * Sanitize input1
	 *
	 * @param string $input The input value.
	 */
	public function sanitize_input1( $input ) {
		if ( true === empty( trim( $input ) ) ) {
			add_settings_error(
				'cliowp_sp_input1',
				'cliowp_sp_input1_error',
				__( 'Input1 cannot be empty', 'depicter' ),
			);
			return get_option( 'cliowp_sp_input1' );
		}

		return sanitize_text_field( $input );
	}

	/**
	 * Create HTML for date1 field
	 */
	public function date1_html() {
		?>
		<input type="date" name="cliowp_sp_date1" value="<?php echo esc_attr( get_option( 'cliowp_sp_date1' ) ); ?>">
		<?php
	}

	/**
	 * Create HTML for datetime1 field
	 */
	public function datetime1_html() {
		?>
		<input type="datetime-local" name="cliowp_sp_datetime1" value="<?php echo esc_attr( get_option( 'cliowp_sp_datetime1' ) ); ?>">
		<?php
	}

	/**
	 * Create HTML for password1 field
	 *
	 * This is the only field that does not retrieve the value from the database
	 * (because a hash is stored and not that original value).
	 * Check the wp_options table to view what is saved as a hash.
	 */
	public function password1_html() {
		?>
		<input type="password" name="cliowp_sp_password1" value="">
		<?php
	}

	/**
	 * Encrypt password1
	 *
	 * @param string $input The plain password.
	 */
	public function encrypt_password1( $input ) {

		return wp_hash_password( $input );
	}

	/**
	 * Create HTML for number1 field
	 *
	 * @param array $args Arguments passed.
	 */
	public function number1_html( array $args ) {
		?>
		<input type="number" name="cliowp_sp_number1"
		min="<?php echo esc_html( $args['min'] ); ?>"
		max="<?php echo esc_html( $args['max'] ); ?>"
		step="<?php echo esc_html( $args['step'] ); ?>"
		value="<?php echo esc_attr( get_option( 'cliowp_sp_number1' ) ); ?>">
		<?php
	}

	/**
	 * Create HTML for select1 field
	 */
	public function select1_html() {
		?>
		<select name="cliowp_sp_select1">
			<option value="1" <?php selected( get_option( 'cliowp_sp_select1' ), '1' ); ?>><?php esc_attr_e( 'Option1', 'depicter' ); ?></option>
			<option value="2" <?php selected( get_option( 'cliowp_sp_select1' ), '2' ); ?>><?php esc_attr_e( 'Option2', 'depicter' ); ?></option>
			<option value="3" <?php selected( get_option( 'cliowp_sp_select1' ), '3' ); ?>><?php esc_attr_e( 'Option3', 'depicter' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Sanitize select1
	 *
	 * @param string $input The selected value.
	 */
	public function sanitize_select1( $input ) {
		$valid_input = array( '1', '2', '3' );
		if ( false === in_array( $input, $valid_input, true ) ) {
			add_settings_error(
				'cliowp_sp_select1',
				'cliowp_sp_select1_error',
				__( 'Invalid option for Select1', 'depicter' ),
			);
			return get_option( 'cliowp_sp_select1' );
		}
		return $input;
	}

	/**
	 * Create HTML for checkbox1 field
	 */
	public function checkbox1_html() {
		?>
		<input type="checkbox" name="cliowp_sp_checkbox1" value="1" <?php checked( get_option( 'cliowp_sp_checkbox1' ), '1' ); ?>>
		<?php
	}

	/**
	 * Create HTML for multiselect1 field
	 */
	public function multiselect1_html() {
		$selected_values = get_option( 'cliowp_sp_multiselect1' );
		?>
		<select name="cliowp_sp_multiselect1[]" multiple>
			<option value="1" <?php echo esc_html( $this->cliowp_multiselected( $selected_values, '1' ) ); ?>><?php esc_attr_e( 'MultiSelect Option1', 'depicter' ); ?></option>
			<option value="2" <?php echo esc_html( $this->cliowp_multiselected( $selected_values, '2' ) ); ?>><?php esc_attr_e( 'MultiSelect Option2', 'depicter' ); ?></option>
			<option value="3" <?php echo esc_html( $this->cliowp_multiselected( $selected_values, '3' ) ); ?>><?php esc_attr_e( 'MultiSelect Option3', 'depicter' ); ?></option>
			<option value="4" <?php echo esc_html( $this->cliowp_multiselected( $selected_values, '4' ) ); ?>><?php esc_attr_e( 'MultiSelect Option4', 'depicter' ); ?></option>
			<option value="5" <?php echo esc_html( $this->cliowp_multiselected( $selected_values, '5' ) ); ?>><?php esc_attr_e( 'MultiSelect Option5', 'depicter' ); ?></option>
		</select>
		<?php
	}

	/**
	 * Utility function to check if value is selected
	 *
	 * @param array|string $selected_values Array (or empty string) returned by get_option().
	 * @param string       $current_value Value to check if it is selected.
	 *
	 * @return string
	 */
	private function cliowp_multiselected(
		$selected_values,
		string $current_value
	): string {
		if ( is_array( $selected_values ) && in_array( $current_value, $selected_values, true ) ) {
			return 'selected';
		}

		return '';
	}

	/**
	 * Create HTML for textarea1 field
	 *
	 * @param array $args Arguments passed.
	 */
	public function textarea1_html( array $args ) {
		?>
		<textarea
			name="cliowp_sp_textarea1"
			rows="<?php echo esc_html( $args['rows'] ); ?>"
			cols="<?php echo esc_html( $args['cols'] ); ?>"><?php echo esc_attr( get_option( 'cliowp_sp_textarea1' ) ); ?></textarea>
		<?php
	}

	/**
	 * Create HTML for color1 field
	 */
	public function color1_html() {
		?>
		<input type="color" name="cliowp_sp_color1" value="<?php echo esc_attr( get_option( 'cliowp_sp_color1' ) ); ?>">
		<?php
	}

	/**
	 * Create HTML for editor1 field
	 */
	public function editor1_html() {
		wp_editor(
			wp_kses_post( get_option( 'cliowp_sp_editor1' ) ),
			'cliowp_sp_editor1',
		);
	}

	/**
	 * Create Settings Page HTML
	 */
	public function settings_page_html() {
		?>

		<div class="wrap">
			<h1><?php echo esc_attr( $this->page_title ); ?>
			</h1>
			<form action="<?php echo esc_attr( $this->form_action ); ?>" method="POST">
				<?php
				settings_fields( $this->option_group );
				do_settings_sections( $this->menu_slug );
				submit_button();
				?>
			</form>
		</div>

		<?php
	}
}
