<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://robertdevore.com
 * @since             1.0.0
 * @package           Metadata_Import_Export_For_Yoast
 *
 * @wordpress-plugin
 *
 * Plugin Name: Metadata Import/Export for Yoast
 * Plugin URI:  https://github.com/robertdevore/metadata-import-export-for-yoast/
 * Description: Import and export Yoast SEO metadata via CSV files.
 * Version:     1.0.0
 * Author:      Robert DeVore
 * Author URI:  https://robertdevore.com/
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: mie-yoast
 * Domain Path: /languages
 * Update URI:  https://github.com/robertdevore/metadata-import-export-for-yoast/
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define the plugin version.
define( 'MIEY_VERSION', '1.0.0' );

// Include the Plugin Update Checker.
require 'includes/plugin-update-checker/plugin-update-checker.php';
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/robertdevore/metadata-import-export-for-yoast/',
    __FILE__,
    'metadata-import-export-for-yoast'
);

// Set the branch that contains the stable release.
$myUpdateChecker->setBranch( 'main' );

/**
 * Class Metadata_Import_Export_Yoast
 *
 * Handles the import/export functionality for Yoast SEO metadata.
 *
 * @package Metadata_Import_Export_For_Yoast
 * @since 1.0.0
 */
class Metadata_Import_Export_Yoast {
    /**
     * The hook suffix for the plugin's admin page.
     *
     * @var string
     */
    private $hook_suffix;

    /**
     * Constructor - Hook into WordPress.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'wp_ajax_import_yoast_csv', [ $this, 'handle_csv_import' ] );
        add_action( 'wp_ajax_export_yoast_csv', [ $this, 'handle_csv_export' ] );
    }

    /**
     * Register the plugin submenu under Yoast SEO.
     *
     * @since  1.0.0
     * @return void
     */
    public function register_menu() {
        $this->hook_suffix = add_submenu_page(
            'wpseo_dashboard',
            esc_html__( 'Metadata Import/Export for Yoast', 'mie-yoast' ),
            esc_html__( 'Import/Export', 'mie-yoast' ),
            'read',
            'yoast-metadata-import',
            [ $this, 'render_tabs_page' ]
        );
    }

    /**
     * Enqueue scripts, styles, and localize AJAX parameters.
     *
     * @param string $hook The current admin page hook.
     *
     * @since  1.0.0
     * @return void
     */
    public function enqueue_scripts( $hook ) {
        if ( $hook === $this->hook_suffix ) {
            wp_enqueue_script(
                'metadata-yoast-js',
                plugin_dir_url( __FILE__ ) . 'assets/js/metadata-yoast.js',
                [],
                MIEY_VERSION,
                true
            );
            wp_localize_script(
                'metadata-yoast-js',
                'YoastMetadataAjax',
                [
                    'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
                    'importNonce' => wp_create_nonce( 'yoast_csv_import_nonce' ),
                    'exportNonce' => wp_create_nonce( 'yoast_csv_export_nonce' ),
                ]
            );

            // Enqueue the custom CSS.
            wp_enqueue_style(
                'metadata-yoast-css',
                plugin_dir_url( __FILE__ ) . 'assets/css/metadata-yoast.css',
                [],
                MIEY_VERSION
            );
        }
    }

    /**
     * Render the admin page with tabs.
     *
     * @since  1.0.0
     * @return void
     */
    public function render_tabs_page() {
        // Check if the user has permission.
        if ( ! $this->user_has_permission() ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mie-yoast' ) );
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'import';
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Metadata Import/Export for Yoast', 'mie-yoast' ); ?>
                <a id="miey-support-btn" href="https://robertdevore.com/contact/" target="_blank" class="button button-alt" style="margin-left: 10px;">
                    <span class="dashicons dashicons-format-chat" style="vertical-align: middle;"></span> <?php esc_html_e( 'Support', 'mie-yoast' ); ?>
                </a>
                <a id="miey-docs-btn" href="https://robertdevore.com/articles/metadata-import-export-for-yoast/" target="_blank" class="button button-alt" style="margin-left: 5px;">
                    <span class="dashicons dashicons-media-document" style="vertical-align: middle;"></span> <?php esc_html_e( 'Documentation', 'mie-yoast' ); ?>
                </a>
            </h1>
            <h2 class="nav-tab-wrapper">
                <a href="?page=yoast-metadata-import&tab=import" class="nav-tab <?php echo $active_tab === 'import' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Import', 'mie-yoast' ); ?>
                </a>
                <a href="?page=yoast-metadata-import&tab=export" class="nav-tab <?php echo $active_tab === 'export' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Export', 'mie-yoast' ); ?>
                </a>
                <a href="?page=yoast-metadata-import&tab=settings" class="nav-tab <?php echo $active_tab === 'settings' ? 'nav-tab-active' : ''; ?>">
                    <?php esc_html_e( 'Settings', 'mie-yoast' ); ?>
                </a>
            </h2>
            <div class="tab-content">
                <?php
                switch ( $active_tab ) {
                    case 'export':
                        $this->render_export_page();
                        break;
                    case 'settings':
                        $this->render_settings_page();
                        break;
                    case 'import':
                    default:
                        $this->render_import_page();
                        break;
                }
                ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render the import page content.
     *
     * @since  1.0.0
     * @return void
     */
    public function render_import_page() {
        ?>
        <div class="miey-container">
            <div class="miey-import-section">
                <h2><?php esc_html_e( 'Import Yoast SEO Metadata', 'mie-yoast' ); ?></h2>
                <p><?php esc_html_e( 'Upload a CSV file to import metadata into your site. Please ensure the file is properly formatted according to the plugin\'s specifications.', 'mie-yoast' ); ?></p>
                <form id="yoast-import-form" enctype="multipart/form-data" method="post">
                    <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
                    <button type="submit" class="button button-primary">
                        <?php esc_html_e( 'Upload and Import', 'mie-yoast' ); ?>
                    </button>
                </form>
                <div id="import-progress-bar" class="miey-progress-bar" style="display: none;">
                    <div id="import-progress-fill" class="miey-progress-fill"></div>
                </div>
                <div id="import-data" class="miey-import-data"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Render the export page content with enhanced styling.
     *
     * @since  1.0.0
     * @return void
     */
    public function render_export_page() {
        ?>
        <div class="miey-container">
            <div class="miey-export-section">
                <h2><?php esc_html_e( 'Export Yoast SEO Metadata', 'mie-yoast' ); ?></h2>
                <p><?php esc_html_e( 'Click the button below to export your site\'s Yoast SEO metadata to a CSV file. You can edit this file and import it back to update your metadata.', 'mie-yoast' ); ?></p>
                <button type="button" class="button button-primary" id="export-button">
                    <?php esc_html_e( 'Export Metadata', 'mie-yoast' ); ?>
                </button>
                <div id="export-progress-bar" class="miey-progress-bar" style="display: none;">
                    <div id="export-progress-fill" class="miey-progress-fill"></div>
                </div>
                <div id="export-data" class="miey-export-data"></div>
            </div>
        </div>
        <?php
    }

    /**
     * Handle the CSV export via AJAX.
     *
     * @since  1.0.0
     * @return void
     */
    public function handle_csv_export() {
        // Check nonce for security.
        check_ajax_referer( 'yoast_csv_export_nonce', 'nonce' );

        // Check user permissions.
        if ( ! $this->user_has_permission() ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'mie-yoast' ) ] );
        }

        // Retrieve settings.
        $settings            = get_option( 'miey_settings' );
        $selected_post_types = $settings['post_types'] ?? array_keys( get_post_types( [ 'public' => true ] ) );

        // Exclude attachment post type if not selected.
        if ( ( $key = array_search( 'attachment', $selected_post_types, true ) ) !== false ) {
            unset( $selected_post_types[ $key ] );
        }

        $args = [
            'post_type'      => $selected_post_types,
            'posts_per_page' => -1,
            'post_status'    => 'any',
        ];

        $posts = get_posts( $args );

        $total_posts            = 0;
        $post_type_counts       = [];
        $posts_with_metadata    = 0;
        $posts_without_metadata = 0;

        // @TODO localize these.
        $csv_data = [ [ 'ID', 'Post Type', 'Slug', 'Keyphrase', 'SEO Title', 'SEO Description' ] ];

        foreach ( $posts as $post ) {
            $total_posts++;
            $post_type = $post->post_type;
            if ( ! isset( $post_type_counts[ $post_type ] ) ) {
                $post_type_counts[ $post_type ] = 0;
            }
            $post_type_counts[ $post_type ]++;

            $keyphrase       = get_post_meta( $post->ID, '_yoast_wpseo_focuskw', true );
            $seo_title       = get_post_meta( $post->ID, '_yoast_wpseo_title', true );
            $seo_description = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true );

            if ( $keyphrase || $seo_title || $seo_description ) {
                $posts_with_metadata++;
            } else {
                $posts_without_metadata++;
            }

            $csv_data[] = [
                $post->ID,
                $post_type,
                $post->post_name,
                $keyphrase,
                $seo_title,
                $seo_description,
            ];
        }

        // Generate the file name with domain and datetime.
        $domain   = parse_url( home_url(), PHP_URL_HOST );
        $datetime = current_time( 'Y-m-d_H-i-s' );

        $filename = $domain . '-yoast-metadata-export-' . $datetime . '.csv';

        $upload_dir = wp_upload_dir();
        $file_path  = $upload_dir['basedir'] . '/' . $filename;
        $file_url   = $upload_dir['baseurl'] . '/' . $filename;

        // Create the CSV file.
        $handle = fopen( $file_path, 'w' );
        foreach ( $csv_data as $row ) {
            fputcsv( $handle, $row );
        }
        fclose( $handle );

        $export_data = [
            'file_url'               => $file_url,
            'file_name'              => $filename,
            'total_posts'            => $total_posts,
            'post_type_counts'       => $post_type_counts,
            'posts_with_metadata'    => $posts_with_metadata,
            'posts_without_metadata' => $posts_without_metadata,
        ];

        wp_send_json_success( $export_data );
    }

    /**
     * Handle the CSV import via AJAX.
     *
     * @since  1.0.0
     * @return void
     */
    public function handle_csv_import() {
        // Check nonce for security.
        check_ajax_referer( 'yoast_csv_import_nonce', 'nonce' );

        // Check user permissions.
        if ( ! $this->user_has_permission() ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Permission denied.', 'mie-yoast' ) ] );
        }

        if ( ! isset( $_FILES['csv_file'] ) || ! file_exists( $_FILES['csv_file']['tmp_name'] ) ) {
            wp_send_json_error( [ 'message' => esc_html__( 'No file uploaded.', 'mie-yoast' ) ] );
        }

        $file = $_FILES['csv_file'];

        // Verify the file type.
        $filetype = wp_check_filetype( $file['name'] );
        if ( $filetype['ext'] !== 'csv' ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid file type. Please upload a CSV file.', 'mie-yoast' ) ] );
        }

        // Open and read the CSV file.
        $handle = fopen( $file['tmp_name'], 'r' );
        if ( ! $handle ) {
            wp_send_json_error( [ 'message' => esc_html__( 'Could not open the uploaded file.', 'mie-yoast' ) ] );
        }

        $header = fgetcsv( $handle );
        if ( ! $header ) {
            fclose( $handle );
            wp_send_json_error( [ 'message' => esc_html__( 'Empty or invalid CSV file.', 'mie-yoast' ) ] );
        }

        // Map columns.
        $expected_headers = [ 'ID', 'Post Type', 'Slug', 'Keyphrase', 'SEO Title', 'SEO Description' ];
        if ( $header !== $expected_headers ) {
            fclose( $handle );
            wp_send_json_error( [ 'message' => esc_html__( 'Invalid CSV header. Please make sure the CSV file is in the correct format.', 'mie-yoast' ) ] );
        }

        // Retrieve settings.
        $settings            = get_option( 'miey_settings' );
        $selected_post_types = $settings['post_types'] ?? array_keys( get_post_types( [ 'public' => true ] ) );

        $updated_posts = 0;
        $errors        = [];

        // Initialize import statistics.
        $import_stats = [
            'total_rows'       => 0,
            'posts_updated'    => 0,
            'post_type_counts' => [],
            'field_updates'    => [
                'keyphrase'       => 0,
                'seo_title'       => 0,
                'seo_description' => 0,
            ],
        ];

        while ( ( $row = fgetcsv( $handle ) ) !== false ) {
            $import_stats['total_rows']++;

            $post_id         = intval( $row[0] );
            $post_type       = $row[1];
            $slug            = $row[2];
            $keyphrase       = $row[3];
            $seo_title       = $row[4];
            $seo_description = $row[5];

            // Check if post type is selected in settings.
            if ( ! in_array( $post_type, $selected_post_types, true ) ) {
                // Skip this post.
                continue;
            }

            // Check if post exists.
            $post = get_post( $post_id );
            if ( ! $post ) {
                $errors[] = sprintf( esc_html__( 'Post ID %d not found.', 'mie-yoast' ), $post_id );
                continue;
            }

            if ( ! isset( $import_stats['post_type_counts'][ $post_type ] ) ) {
                $import_stats['post_type_counts'][ $post_type ] = [
                    'posts_updated' => 0,
                    'field_updates' => [
                        'keyphrase'       => 0,
                        'seo_title'       => 0,
                        'seo_description' => 0,
                    ],
                ];
            }

            // Initialize variables to track whether fields were updated.
            $field_updated = false;

            // Update keyphrase if the value is different.
            $current_keyphrase = get_post_meta( $post_id, '_yoast_wpseo_focuskw', true );
            if ( $current_keyphrase !== $keyphrase ) {
                update_post_meta( $post_id, '_yoast_wpseo_focuskw', $keyphrase );
                $import_stats['field_updates']['keyphrase']++;
                $import_stats['post_type_counts'][ $post_type ]['field_updates']['keyphrase']++;
                $field_updated = true;
            }

            // Update title if the value is different.
            $current_seo_title = get_post_meta( $post_id, '_yoast_wpseo_title', true );
            if ( $current_seo_title !== $seo_title ) {
                update_post_meta( $post_id, '_yoast_wpseo_title', $seo_title );
                $import_stats['field_updates']['seo_title']++;
                $import_stats['post_type_counts'][ $post_type ]['field_updates']['seo_title']++;
                $field_updated = true;
            }

            // Update description if the value is different.
            $current_seo_description = get_post_meta( $post_id, '_yoast_wpseo_metadesc', true );
            if ( $current_seo_description !== $seo_description ) {
                update_post_meta( $post_id, '_yoast_wpseo_metadesc', $seo_description );
                $import_stats['field_updates']['seo_description']++;
                $import_stats['post_type_counts'][ $post_type ]['field_updates']['seo_description']++;
                $field_updated = true;
            }

            if ( $field_updated ) {
                $updated_posts++;
                $import_stats['posts_updated']++;
                $import_stats['post_type_counts'][ $post_type ]['posts_updated']++;
            }
        }

        fclose( $handle );

        $import_data = [
            'updated_posts' => $updated_posts,
            'errors'        => $errors,
            'import_stats'  => $import_stats,
        ];

        wp_send_json_success( $import_data );
    }

    /**
     * Register settings.
     *
     * @since  1.0.0
     * @return void
     */
    public function register_settings() {
        register_setting( 'miey_settings_group', 'miey_settings' );
        add_settings_section( 'miey_settings_section', '', null, 'miey-settings' );
        add_settings_field(
            'miey_post_types',
            esc_html__( 'Select Post Types', 'mie-yoast' ),
            [ $this, 'render_post_types_field' ],
            'miey-settings',
            'miey_settings_section'
        );
        add_settings_field(
            'miey_user_roles',
            esc_html__( 'Select User Roles', 'mie-yoast' ),
            [ $this, 'render_user_roles_field' ],
            'miey-settings',
            'miey_settings_section'
        );
    }

    /**
     * Render the post types field.
     *
     * @since  1.0.0
     * @return void
     */
    public function render_post_types_field() {
        $post_types          = get_post_types( [ 'public' => true ], 'objects' );
        $options             = get_option( 'miey_settings' );
        $selected_post_types = $options['post_types'] ?? array_keys( $post_types );

        foreach ( $post_types as $post_type ) {
            ?>
            <label>
                <input type="checkbox" name="miey_settings[post_types][]" value="<?php echo esc_attr( $post_type->name ); ?>" <?php checked( in_array( $post_type->name, $selected_post_types, true ) ); ?>>
                <?php echo esc_html( $post_type->labels->name ); ?>
            </label><br>
            <?php
        }
    }

    /**
     * Render the user roles field.
     *
     * @since  1.0.0
     * @return void
     */
    public function render_user_roles_field() {
        global $wp_roles;
        $roles          = $wp_roles->roles;
        $options        = get_option( 'miey_settings' );
        $selected_roles = $options['user_roles'] ?? [ 'administrator' ];

        foreach ( $roles as $role_key => $role ) {
            ?>
            <label>
                <input type="checkbox" name="miey_settings[user_roles][]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( in_array( $role_key, $selected_roles, true ) ); ?>>
                <?php echo esc_html( $role['name'] ); ?>
            </label><br />
            <?php
        }
    }

    /**
     * Render the settings page content.
     *
     * @since  1.0.0
     * @return void
     */
    public function render_settings_page() {
        // Check if the user has permission.
        if ( ! current_user_can( 'administrator' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'mie-yoast' ) );
        }
        ?>
        <div class="miey-container">
            <form method="post" action="options.php">
                <?php
                settings_fields( 'miey_settings_group' );
                do_settings_sections( 'miey-settings' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Check if the current user has permission to access the plugin's functionalities.
     *
     * @return bool True if the user has permission, false otherwise.
     *
     * @since  1.0.0
     * @return bool
     */
    private function user_has_permission() {
        $settings      = get_option( 'miey_settings' );
        $allowed_roles = $settings['user_roles'] ?? [ 'administrator' ];

        foreach ( $allowed_roles as $role ) {
            if ( current_user_can( $role ) ) {
                return true;
            }
        }

        return false;
    }
}

// Initialize the plugin.
new Metadata_Import_Export_Yoast();
