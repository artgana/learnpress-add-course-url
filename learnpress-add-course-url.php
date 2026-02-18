<?php
/**
 * Plugin Name: LearnPress - Add Course via URL
 * Plugin URI: https://github.com/artgana/learnpress-add-course-url
 * Description: WooCommerce-style add-to-cart for LearnPress
 * Author: artgana
 * Version: 4.0.0
 * Text Domain: learnpress-add-course-url
 * Domain Path: /languages
 * Require_LP_Version: 4.0.0
 * Requires at least: 6.3
 * Requires PHP: 7.4
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @package learnpress-add-course-url
 */

defined('ABSPATH') || exit;

final class LP_Add_Course_URL_Plugin
{
	/**
	 * @var LP_Add_Course_URL_Plugin
	 */
	protected static $instance = null;

	private const QUERY_ARG = 'add-course';
	private const REDIRECT_ARG = 'redirect';

	/**
	 * Instance pattern.
	 *
	 * @return LP_Add_Course_URL_Plugin
	 */
	public static function instance(): self
	{
		if (is_null(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function init(): void
	{
		// Frontend handler - runs after LearnPress is fully bootstrapped.
		add_action('template_redirect', [$this, 'handle_add_course_request']);
		add_action('add_meta_boxes', [$this, 'register_metabox']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
		// Note: textdomain is loaded via a top-level hook, not here, because this class is bootstrapped on 'learn-press/ready'.
	}

	/**
	 * Load textdomain
	 */
	public function load_textdomain(): void
	{
		load_plugin_textdomain('learnpress-add-course-url', false, dirname(plugin_basename(__FILE__)) . '/languages');
	}

	/**
	 * Frontend: add course via URL
	 */
	public function handle_add_course_request(): void
	{

		if (empty($_GET[self::QUERY_ARG])) {
			return;
		}

		$course_id = absint($_GET[self::QUERY_ARG]);

		if (!$this->is_valid_course($course_id)) {
			return;
		}

		if ($this->is_user_already_enrolled($course_id)) {
			return;
		}

		// Add a course to LearnPress cart
		LP()->get_cart()->add_to_cart($course_id);

		if ($this->should_redirect_to_checkout()) {
			wp_safe_redirect($this->get_checkout_url());
			exit;
		}
	}

	private function is_valid_course(int $course_id): bool
	{
		return $course_id > 0 && get_post_type($course_id) === 'lp_course';
	}

	private function is_user_already_enrolled(int $course_id): bool
	{
		return is_user_logged_in()
			&& function_exists('learn_press_is_enrolled_course')
			&& learn_press_is_enrolled_course($course_id, get_current_user_id());
	}

	private function should_redirect_to_checkout(): bool
	{
		return isset($_GET[self::REDIRECT_ARG])
			&& $_GET[self::REDIRECT_ARG] === 'checkout';
	}

	private function get_checkout_url(): string
	{
		return learn_press_get_page_link('checkout');
	}

	/**
	 * Admin: create a metabox
	 */
	public function register_metabox(): void
	{
		add_meta_box(
			'lp_add_course_checkout_url',
			__('Checkout URL', 'learnpress-add-course-url'),
			[$this, 'render_metabox'],
			'lp_course',
			'side',
			'default'
		);
	}

	public function render_metabox(\WP_Post $post): void
	{

		$url = $this->build_add_course_url($post->ID);
		?>
		<div class="lp-add-course-url-box">
			<input type="text" class="widefat lp-add-course-url-input" readonly value="<?php echo esc_attr($url); ?>"
				onclick="this.select();" />

			<button type="button" class="button lp-copy-course-url" data-url="<?php echo esc_attr($url); ?>"
				style="margin-top:6px;width:100%;">
				<?php esc_html_e('Copy URL', 'learnpress-add-course-url'); ?>
			</button>

			<p style="margin-top:6px;font-size:12px;color:#666;">
				<?php esc_html_e('Use this link on landing pages or buttons.', 'learnpress-add-course-url'); ?>
			</p>
		</div>
		<?php
	}

	private function build_add_course_url(int $course_id): string
	{
		return add_query_arg(
			[
				self::QUERY_ARG => $course_id,
				self::REDIRECT_ARG => 'checkout',
			],
			$this->get_checkout_url()
		);
	}

	/**
	 * Admin assets
	 */
	public function enqueue_admin_assets(string $hook): void
	{

		if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
			return;
		}

		$screen = get_current_screen();

		if (!$screen || $screen->post_type !== 'lp_course') {
			return;
		}

		$script_rel_path = 'assets/admin-copy.js';
		wp_enqueue_script(
			'lp-add-course-admin-copy',
			plugin_dir_url(__FILE__) . $script_rel_path,
			['wp-i18n'],
			(string) filemtime(plugin_dir_path(__FILE__) . $script_rel_path),
			true
		);

		wp_set_script_translations('lp-add-course-admin-copy', 'learnpress-add-course-url', plugin_dir_path(__FILE__) . 'languages');
	}
}

/**
 * Load translations early enough regardless of LearnPress readiness.
 */
add_action('plugins_loaded', static function () {
	load_plugin_textdomain('learnpress-add-course-url', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

/**
 * Bootstrap
 */
add_action('learn-press/ready', static function () {
	LP_Add_Course_URL_Plugin::instance()->init();
});
