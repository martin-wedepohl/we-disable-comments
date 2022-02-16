<?php    // phpcs:ignore
/**
 * Disable Comments
 *
 * @package           WEDisableComments
 * @author            Martin Wedepohl
 * @copyright         2021 Wedepohl Engineering
 * @license           GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Wedepohl Engineering Disable Comments
 * Plugin URI:        https://github.com/martin-wedepohl/we-disable-coments
 * Description:       Disable Comments from administration menus and post/page editors.
 * Version:           1.0.2
 * Requires at least: 4.9
 * Requires PHP:      5.6
 * Author:            Martin Wedepohl
 * Author URI:        https://wedepohlengineering.com/
 * License:           GPL v3 or later
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       we-disable-comments
 *
 * The plugin we_disable_comments is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or any later version.
 *
 * we_disable_comments is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with we_disable_comments. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

if ( ! class_exists( 'WEDisableComments' ) ) {

	/**
	 * Class WEDisableComments
	 *
	 * Provides all the functionality to disable the WordPress block editor
	 * fullscreen mode which by default is eanbled in WordPress 5.4.
	 *
	 * @package WEDisableComments
	 */
	class WEDisableComments {

		/**
		 * Initialize the class.
		 *
		 * @global $wp_version The WordPress version
		 *
		 * @since 1.0.0
		 */
		public function init() {

			global $wp_version;

			add_action( 'admin_menu', array( $this, 'disable_comments_admin_menu' ) );
			add_action( 'admin_init', array( $this, 'comments_admin_menu_redirect' ) );
			add_action( 'admin_init', array( $this, 'disable_comments_dashboard' ) );
			add_action( 'admin_init', array( $this, 'disable_comments_admin_bar' ) );
			add_action( 'admin_init', array( $this, 'remove_columns' ) );

			if ( version_compare( $wp_version, '5.0', '>=' ) ) {
				add_action( 'enqueue_block_editor_assets', array( $this, 'remove_block_discussions' ) );
			}

		}

		/**
		 * Disable comments from the admin menu
		 */
		public function disable_comments_admin_menu() {

			remove_menu_page( 'edit-comments.php' );
			remove_submenu_page( 'options-general.php', 'options-discussion.php' );
			remove_meta_box( 'commentstatusdiv', 'post', 'normal' );
			remove_meta_box( 'commentstatusdiv', 'page', 'normal' );
			remove_meta_box( 'commentsdiv', 'post', 'normal' );
			remove_meta_box( 'commentsdiv', 'page', 'normal' );

		}

		/**
		 * Redirect any calls to the Comments page.
		 *
		 * @global type $pagenow
		 */
		public function comments_admin_menu_redirect() {

			global $pagenow;

			if ( 'edit-comments.php' === $pagenow ) {
				wp_safe_redirect( admin_url() );
				exit;
			}

		}

		/**
		 * Remove comments metabox from the dashboard
		 */
		public function disable_comments_dashboard() {

			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

		}

		/**
		 * Disable the comments in the admin bar
		 */
		public function disable_comments_admin_bar() {

			if ( is_admin_bar_showing() ) {
				remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
			}

		}

		/**
		 * Remove the discussion meta box in block editor
		 */
		public function remove_block_discussions() {

			$script = "wp.domReady( () => { const { removeEditorPanel } = wp.data.dispatch('core/edit-post'); removeEditorPanel( 'discussion-panel' ); } );";
			wp_add_inline_script( 'wp-blocks', $script );

		}

		/**
		 * Remove the comments column.
		 */
		public function remove_comments_column( $columns ) {

			unset( $columns['comments'] );
			return $columns;

		}

		/**
		 * Remove columns by setting filters on the specific columns.
		 */
		public function remove_columns() {

			add_filter( 'manage_posts_columns', array( $this, 'remove_comments_column' ) );
			add_filter( 'manage_pages_columns', array( $this, 'remove_comments_column' ) );

		}

	}

	$wedc = new WEDisableComments();
	$wedc->init();

}
