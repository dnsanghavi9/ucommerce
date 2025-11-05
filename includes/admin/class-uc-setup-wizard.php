<?php
/**
 * Setup wizard.
 *
 * Guides users through initial plugin setup.
 *
 * @package    UCommerce
 * @subpackage UCommerce/includes/admin
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Setup wizard class.
 */
class UC_Setup_Wizard {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_wizard_page' ) );
        add_action( 'admin_init', array( $this, 'process_wizard' ) );
    }

    /**
     * Add wizard page.
     */
    public function add_wizard_page() {
        add_submenu_page(
            null, // Hidden from menu
            __( 'U-Commerce Setup Wizard', 'u-commerce' ),
            __( 'Setup Wizard', 'u-commerce' ),
            'manage_options',
            'u-commerce-setup-wizard',
            array( $this, 'render_wizard_page' )
        );
    }

    /**
     * Process wizard form submission.
     */
    public function process_wizard() {
        if ( ! isset( $_POST['uc_setup_wizard'] ) || ! isset( $_POST['step'] ) ) {
            return;
        }

        check_admin_referer( 'uc_setup_wizard', 'uc_setup_nonce' );

        $step = sanitize_text_field( $_POST['step'] );

        switch ( $step ) {
            case 'general':
                $this->process_general_settings();
                break;

            case 'center':
                $this->process_center_setup();
                break;

            case 'complete':
                $this->complete_setup();
                break;
        }
    }

    /**
     * Process general settings.
     */
    private function process_general_settings() {
        $settings = get_option( 'u_commerce_settings', array() );

        if ( isset( $_POST['company_name'] ) ) {
            $settings['general']['company_name'] = sanitize_text_field( $_POST['company_name'] );
        }

        if ( isset( $_POST['company_address'] ) ) {
            $settings['general']['company_address'] = sanitize_textarea_field( $_POST['company_address'] );
        }

        if ( isset( $_POST['currency'] ) ) {
            $settings['general']['currency'] = sanitize_text_field( $_POST['currency'] );
        }

        update_option( 'u_commerce_settings', $settings );

        wp_redirect( admin_url( 'admin.php?page=u-commerce-setup-wizard&step=center' ) );
        exit;
    }

    /**
     * Process center setup.
     */
    private function process_center_setup() {
        if ( ! isset( $_POST['center_name'] ) ) {
            return;
        }

        $centers = new UC_Centers();

        $center_data = array(
            'name'         => sanitize_text_field( $_POST['center_name'] ),
            'type'         => 'main',
            'address'      => isset( $_POST['center_address'] ) ? sanitize_textarea_field( $_POST['center_address'] ) : '',
            'contact_info' => isset( $_POST['center_contact'] ) ? sanitize_textarea_field( $_POST['center_contact'] ) : '',
            'status'       => 'active',
        );

        $centers->create( $center_data );

        wp_redirect( admin_url( 'admin.php?page=u-commerce-setup-wizard&step=complete' ) );
        exit;
    }

    /**
     * Complete setup.
     */
    private function complete_setup() {
        delete_option( 'u_commerce_newly_installed' );
        wp_redirect( admin_url( 'admin.php?page=u-commerce' ) );
        exit;
    }

    /**
     * Render wizard page.
     */
    public function render_wizard_page() {
        $step = isset( $_GET['step'] ) ? sanitize_text_field( $_GET['step'] ) : 'general';
        ?>
        <div class="wrap uc-setup-wizard">
            <h1><?php esc_html_e( 'U-Commerce Setup Wizard', 'u-commerce' ); ?></h1>

            <?php
            switch ( $step ) {
                case 'general':
                    $this->render_general_step();
                    break;

                case 'center':
                    $this->render_center_step();
                    break;

                case 'complete':
                    $this->render_complete_step();
                    break;

                default:
                    $this->render_general_step();
            }
            ?>
        </div>
        <?php
    }

    /**
     * Render general settings step.
     */
    private function render_general_step() {
        $settings = get_option( 'u_commerce_settings', array() );
        ?>
        <div class="uc-wizard-step">
            <h2><?php esc_html_e( 'Step 1: General Settings', 'u-commerce' ); ?></h2>
            <p><?php esc_html_e( 'Configure your basic business information.', 'u-commerce' ); ?></p>

            <form method="post">
                <?php wp_nonce_field( 'uc_setup_wizard', 'uc_setup_nonce' ); ?>
                <input type="hidden" name="uc_setup_wizard" value="1">
                <input type="hidden" name="step" value="general">

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="company_name"><?php esc_html_e( 'Company Name', 'u-commerce' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="company_name" id="company_name" class="regular-text"
                                   value="<?php echo esc_attr( $settings['general']['company_name'] ?? '' ); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="company_address"><?php esc_html_e( 'Company Address', 'u-commerce' ); ?></label>
                        </th>
                        <td>
                            <textarea name="company_address" id="company_address" rows="3" class="large-text"><?php
                                echo esc_textarea( $settings['general']['company_address'] ?? '' );
                            ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="currency"><?php esc_html_e( 'Currency', 'u-commerce' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="currency" id="currency" class="regular-text"
                                   value="<?php echo esc_attr( $settings['general']['currency'] ?? 'INR' ); ?>" required>
                            <p class="description"><?php esc_html_e( 'Currency code (e.g., INR, USD, EUR)', 'u-commerce' ); ?></p>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        <?php esc_html_e( 'Continue', 'u-commerce' ); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render center setup step.
     */
    private function render_center_step() {
        ?>
        <div class="uc-wizard-step">
            <h2><?php esc_html_e( 'Step 2: Main Center Setup', 'u-commerce' ); ?></h2>
            <p><?php esc_html_e( 'Set up your main center/warehouse.', 'u-commerce' ); ?></p>

            <form method="post">
                <?php wp_nonce_field( 'uc_setup_wizard', 'uc_setup_nonce' ); ?>
                <input type="hidden" name="uc_setup_wizard" value="1">
                <input type="hidden" name="step" value="center">

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="center_name"><?php esc_html_e( 'Center Name', 'u-commerce' ); ?></label>
                        </th>
                        <td>
                            <input type="text" name="center_name" id="center_name" class="regular-text" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="center_address"><?php esc_html_e( 'Address', 'u-commerce' ); ?></label>
                        </th>
                        <td>
                            <textarea name="center_address" id="center_address" rows="3" class="large-text"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="center_contact"><?php esc_html_e( 'Contact Info', 'u-commerce' ); ?></label>
                        </th>
                        <td>
                            <textarea name="center_contact" id="center_contact" rows="2" class="large-text"></textarea>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary button-large">
                        <?php esc_html_e( 'Complete Setup', 'u-commerce' ); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Render complete step.
     */
    private function render_complete_step() {
        ?>
        <div class="uc-wizard-step">
            <h2><?php esc_html_e( 'Setup Complete!', 'u-commerce' ); ?></h2>
            <p><?php esc_html_e( 'Your U-Commerce plugin is now configured and ready to use.', 'u-commerce' ); ?></p>

            <form method="post">
                <?php wp_nonce_field( 'uc_setup_wizard', 'uc_setup_nonce' ); ?>
                <input type="hidden" name="uc_setup_wizard" value="1">
                <input type="hidden" name="step" value="complete">

                <p class="submit">
                    <button type="submit" class="button button-primary button-hero">
                        <?php esc_html_e( 'Go to Dashboard', 'u-commerce' ); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }
}
