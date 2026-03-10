<?php
/**
 * Custom per-page dataLayer variables (Pro only).
 * This file is not included in the WordPress.org build so the free plugin has no locked features.
 *
 * @package DataLayer_Manager
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Handles custom variables UI and save. Loaded only when not FREE_VERSION.
 */
class DataLayer_Manager_Custom_Variables {

    /**
     * Render the Custom Variables section in the meta box.
     *
     * @param DataLayer_Manager $main Main plugin instance.
     * @param WP_Post           $post Post object.
     */
    public static function render_section( $main, $post ) {
        $custom_variables = get_post_meta( $post->ID, '_datalayer_manager_custom_variables', true );
        if ( ! is_array( $custom_variables ) ) {
            $custom_variables = array();
        }
        $auto_detected_keys = $main->get_auto_detected_variable_keys( $post );
        $filtered_custom_variables = array();
        foreach ( $custom_variables as $key => $value ) {
            if ( ! in_array( $key, $auto_detected_keys, true ) ) {
                $filtered_custom_variables[ $key ] = $value;
            }
        }
        ?>
        <div style="margin-top: 20px; padding-top: 15px; border-top: 1px solid #ddd;">
            <p>
                <strong><?php esc_html_e( 'Custom Variables', 'scripts-and-pixels-datalayer-manager' ); ?></strong>
            </p>
            <p class="description">
                <?php esc_html_e( 'Add custom dataLayer variables that will merge with auto-detected variables for this page.', 'scripts-and-pixels-datalayer-manager' ); ?>
            </p>
            
            <?php if ( ! $main->is_premium_active() ) : ?>
                <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin: 15px 0;">
                    <p style="margin: 0 0 10px 0;">
                        <strong><?php esc_html_e( 'Premium Feature', 'scripts-and-pixels-datalayer-manager' ); ?></strong>
                    </p>
                    <p style="margin: 0 0 10px 0;">
                        <?php
                        printf(
                            /* translators: %s: Link to plugin website. */
                            esc_html__( 'Custom variables allow you to add page-specific dataLayer variables for tracking campaign codes, affiliate IDs, and other custom data. This feature is available in the premium version. %s', 'scripts-and-pixels-datalayer-manager' ),
                            '<a href="' . esc_url( 'https://scriptsandpixels.studio' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more', 'scripts-and-pixels-datalayer-manager' ) . '</a>'
                        );
                        ?>
                    </p>
                    <p style="margin: 0;">
                        <a href="<?php echo esc_url( admin_url( 'options-general.php?page=scripts-and-pixels-datalayer-manager&screen=license' ) ); ?>" class="button button-primary">
                            <?php esc_html_e( 'Activate License', 'scripts-and-pixels-datalayer-manager' ); ?>
                        </a>
                    </p>
                </div>
            <?php else : ?>
            <div id="datalayer-custom-variables">
                <?php if ( ! empty( $filtered_custom_variables ) ) : ?>
                    <table class="wp-list-table widefat fixed striped" style="margin-top: 10px;">
                        <thead>
                            <tr>
                                <th style="width: 30%;"><?php esc_html_e( 'Name', 'scripts-and-pixels-datalayer-manager' ); ?></th>
                                <th style="width: 40%;"><?php esc_html_e( 'Value', 'scripts-and-pixels-datalayer-manager' ); ?></th>
                                <th style="width: 20%;"><?php esc_html_e( 'Type', 'scripts-and-pixels-datalayer-manager' ); ?></th>
                                <th style="width: 10%;"><?php esc_html_e( 'Actions', 'scripts-and-pixels-datalayer-manager' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $index = 0; ?>
                            <?php foreach ( $filtered_custom_variables as $key => $value ) : ?>
                                <?php
                                $type = self::get_value_type( $value );
                                $display_value = self::format_value_for_edit( $value, $type );
                                ?>
                                <tr class="datalayer-variable-row">
                                    <td>
                                        <input type="text" name="datalayer_variables[<?php echo esc_attr( $index ); ?>][key]" value="<?php echo esc_attr( $key ); ?>" class="regular-text datalayer-variable-key" pattern="[A-Za-z0-9_]+" required />
                                    </td>
                                    <td>
                                        <input type="text" name="datalayer_variables[<?php echo esc_attr( $index ); ?>][value]" value="<?php echo esc_attr( $display_value ); ?>" class="regular-text" required />
                                    </td>
                                    <td>
                                        <select name="datalayer_variables[<?php echo esc_attr( $index ); ?>][type]" class="regular-text">
                                            <option value="string" <?php selected( $type, 'string' ); ?>><?php esc_html_e( 'String', 'scripts-and-pixels-datalayer-manager' ); ?></option>
                                            <option value="number" <?php selected( $type, 'number' ); ?>><?php esc_html_e( 'Number', 'scripts-and-pixels-datalayer-manager' ); ?></option>
                                            <option value="boolean" <?php selected( $type, 'boolean' ); ?>><?php esc_html_e( 'Boolean', 'scripts-and-pixels-datalayer-manager' ); ?></option>
                                        </select>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small remove-variable-row"><?php esc_html_e( 'Remove', 'scripts-and-pixels-datalayer-manager' ); ?></button>
                                    </td>
                                </tr>
                                <?php $index++; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <p class="description" style="margin-top: 10px;">
                        <?php esc_html_e( 'No custom variables added yet. Click "Add Variable" to create one.', 'scripts-and-pixels-datalayer-manager' ); ?>
                    </p>
                <?php endif; ?>
            </div>
            
                <p style="margin-top: 15px;">
                    <button type="button" class="button button-secondary" id="add-datalayer-variable">
                        <?php esc_html_e( '+ Add Variable', 'scripts-and-pixels-datalayer-manager' ); ?>
                    </button>
                </p>
                
                <input type="hidden" id="datalayer-auto-detected-keys" value="<?php echo esc_attr( wp_json_encode( $auto_detected_keys ) ); ?>" />
            
                <?php
                $reserved_msg = esc_js( __( 'This key is reserved for auto-detected variables and cannot be used.', 'scripts-and-pixels-datalayer-manager' ) );
                $l_name       = esc_js( __( 'Name', 'scripts-and-pixels-datalayer-manager' ) );
                $l_value      = esc_js( __( 'Value', 'scripts-and-pixels-datalayer-manager' ) );
                $l_type       = esc_js( __( 'Type', 'scripts-and-pixels-datalayer-manager' ) );
                $l_actions    = esc_js( __( 'Actions', 'scripts-and-pixels-datalayer-manager' ) );
                $l_string     = esc_js( __( 'String', 'scripts-and-pixels-datalayer-manager' ) );
                $l_number     = esc_js( __( 'Number', 'scripts-and-pixels-datalayer-manager' ) );
                $l_boolean    = esc_js( __( 'Boolean', 'scripts-and-pixels-datalayer-manager' ) );
                $l_remove     = esc_js( __( 'Remove', 'scripts-and-pixels-datalayer-manager' ) );
                $meta_box_js  = "(function(\$){\$(document).ready(function(){var autoDetectedKeys=[];try{autoDetectedKeys=JSON.parse(\$('#datalayer-auto-detected-keys').val()||'[]');}catch(e){autoDetectedKeys=[];}
function validateVariableKey(key,inputElement){if(autoDetectedKeys.indexOf(key)!==-1){inputElement.css('border-color','#dc3232');var e=inputElement.siblings('.datalayer-error-message');if(e.length===0){inputElement.after('<span class=\"datalayer-error-message\" style=\"color:#dc3232;font-size:11px;display:block;margin-top:3px;\">" . $reserved_msg . "</span>');}return false;}else{inputElement.css('border-color','');inputElement.siblings('.datalayer-error-message').remove();return true;}}
\$('#add-datalayer-variable').on('click',function(){var i=Date.now(),tbody=\$('#datalayer-custom-variables tbody');if(tbody.length===0){var tbl='<table class=\"wp-list-table widefat fixed striped\" style=\"margin-top:10px;\"><thead><tr><th style=\"width:30%;\">" . $l_name . "</th><th style=\"width:40%;\">" . $l_value . "</th><th style=\"width:20%;\">" . $l_type . "</th><th style=\"width:10%;\">" . $l_actions . "</th></tr></thead><tbody></tbody></table>';\$('#datalayer-custom-variables').html(tbl);tbody=\$('#datalayer-custom-variables tbody');}
var row='<tr class=\"datalayer-variable-row\"><td><input type=\"text\" name=\"datalayer_variables['+i+'][key]\" value=\"\" class=\"regular-text datalayer-variable-key\" pattern=\"[A-Za-z0-9_]+\" required /></td><td><input type=\"text\" name=\"datalayer_variables['+i+'][value]\" value=\"\" class=\"regular-text\" required /></td><td><select name=\"datalayer_variables['+i+'][type]\" class=\"regular-text\"><option value=\"string\">" . $l_string . "</option><option value=\"number\">" . $l_number . "</option><option value=\"boolean\">" . $l_boolean . "</option></select></td><td><button type=\"button\" class=\"button button-small remove-variable-row\">" . $l_remove . "</button></td></tr>';tbody.append(row);});
\$(document).on('blur','.datalayer-variable-key',function(){var k=\$(this).val().trim();if(k){validateVariableKey(k,\$(this));}});
\$(document).on('click','.remove-variable-row',function(){\$(this).closest('.datalayer-variable-row').remove();});});})(jQuery);";
                wp_add_inline_script( 'datalayer-manager-meta-box', $meta_box_js );
                ?>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Save custom variables from the meta box. Only runs when premium is active (caller checks).
     *
     * @param DataLayer_Manager $main    Main plugin instance.
     * @param int               $post_id Post ID.
     * @param WP_Post           $post    Post object.
     */
    public static function save_section( $main, $post_id, $post ) {
        if ( ! $main->is_premium_active() ) {
            return;
        }
        $allowed_types = array( 'post', 'page' );
        if ( $main->is_woocommerce_active() ) {
            $allowed_types[] = 'product';
        }
        $custom_post_types = get_post_types( array( 'public' => true, '_builtin' => false ), 'names' );
        if ( ! empty( $custom_post_types ) ) {
            $allowed_types = array_merge( $allowed_types, $custom_post_types );
        }
        $allowed_types = apply_filters( 'datalayer_manager_meta_box_post_types', $allowed_types );
        if ( ! in_array( $post->post_type, $allowed_types, true ) ) {
            return;
        }
        $auto_detected_keys = $main->get_auto_detected_variable_keys( $post );
        $custom_variables = array();
        if ( isset( $_POST['datalayer_variables'] ) && is_array( $_POST['datalayer_variables'] ) ) {
            $datalayer_variables = wp_unslash( $_POST['datalayer_variables'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            foreach ( $datalayer_variables as $var ) {
                $key   = isset( $var['key'] ) ? trim( sanitize_text_field( wp_unslash( $var['key'] ) ) ) : '';
                $value = isset( $var['value'] ) ? trim( sanitize_text_field( wp_unslash( $var['value'] ) ) ) : '';
                $type  = isset( $var['type'] ) ? sanitize_text_field( wp_unslash( $var['type'] ) ) : 'string';
                if ( empty( $key ) || ! preg_match( '/^[A-Za-z0-9_]+$/', $key ) || in_array( $key, $auto_detected_keys, true ) ) {
                    continue;
                }
                $converted_value = self::convert_value_by_type( $value, $type );
                if ( null !== $converted_value ) {
                    $custom_variables[ $key ] = $converted_value;
                }
            }
        }
        if ( ! empty( $custom_variables ) ) {
            update_post_meta( $post_id, '_datalayer_manager_custom_variables', $custom_variables );
        } else {
            delete_post_meta( $post_id, '_datalayer_manager_custom_variables' );
        }
    }

    /**
     * Get custom variables for a post (for frontend merge).
     *
     * @param int $post_id Post ID.
     * @return array Custom variables array.
     */
    public static function get_for_post( $post_id ) {
        $custom_variables = get_post_meta( $post_id, '_datalayer_manager_custom_variables', true );
        return is_array( $custom_variables ) ? $custom_variables : array();
    }

    /**
     * @param mixed $value Value.
     * @return string Type label.
     */
    private static function get_value_type( $value ) {
        if ( is_bool( $value ) ) {
            return 'boolean';
        }
        if ( is_int( $value ) || is_float( $value ) ) {
            return 'number';
        }
        return 'string';
    }

    /**
     * @param mixed  $value Value.
     * @param string $type  Type.
     * @return string Formatted for form input.
     */
    private static function format_value_for_edit( $value, $type ) {
        if ( 'boolean' === $type ) {
            return $value ? 'true' : 'false';
        }
        return (string) $value;
    }

    /**
     * @param string $value Value to convert.
     * @param string $type  Target type.
     * @return mixed Converted value or null if invalid.
     */
    private static function convert_value_by_type( $value, $type ) {
        switch ( $type ) {
            case 'number':
                if ( ! is_numeric( $value ) ) {
                    return null;
                }
                return strpos( $value, '.' ) !== false ? (float) $value : (int) $value;
            case 'boolean':
                $lower_value = strtolower( trim( $value ) );
                if ( 'true' === $lower_value || '1' === $lower_value || 'yes' === $lower_value ) {
                    return true;
                }
                if ( 'false' === $lower_value || '0' === $lower_value || 'no' === $lower_value || '' === $lower_value ) {
                    return false;
                }
                return null;
            case 'string':
            default:
                return $value;
        }
    }
}
