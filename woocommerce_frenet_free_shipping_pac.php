<?php
/**
 * Plugin Name: Frenet Free Shipping PAC
 * Description: Integra o PAC da Frenet ao Frete Grátis do WooCommerce, exibindo prazo de entrega em dias úteis, sem ocultar outros métodos de envio.
 * Version: 1.1.0
 * Requires PHP: 7.6
 * Requires at least: 6.6
 * Author: David William da Costa
 * Text Domain: frenet-free-shipping-pac
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Adiciona link "Settings" na listagem de plugins
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'frenet_free_shipping_action_links' );
function frenet_free_shipping_action_links( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=shipping&section=options' ) ) . '">' . esc_html__( 'Configurações', 'frenet-free-shipping-pac' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

/**
 * Adiciona configurações em WooCommerce > Ajustes > Entrega
 */
add_filter( 'woocommerce_shipping_settings', 'wc_frenet_shipping_settings' );
function wc_frenet_shipping_settings( $settings ) {
    $new = array(
        array(
            'title' => __( 'Frenet Free Shipping PAC', 'frenet-free-shipping-pac' ),
            'type'  => 'title',
            'id'    => 'wc_frenet_settings',
        ),
        array(
            'title' => __( 'Chave', 'frenet-free-shipping-pac' ),
            'id'    => 'wc_frenet_key',
            'type'  => 'text',
            'desc'  => __( 'Chave de acesso API Frenet.', 'frenet-free-shipping-pac' ),
        ),
        array(
            'title' => __( 'Senha', 'frenet-free-shipping-pac' ),
            'id'    => 'wc_frenet_secret',
            'type'  => 'password',
            'desc'  => __( 'Senha de acesso API Frenet.', 'frenet-free-shipping-pac' ),
        ),
        array(
            'title' => __( 'Token', 'frenet-free-shipping-pac' ),
            'id'    => 'wc_frenet_token',
            'type'  => 'text',
            'desc'  => __( 'Token API Frenet.', 'frenet-free-shipping-pac' ),
        ),
        array(
            'title' => __( 'CEP Origem', 'frenet-free-shipping-pac' ),
            'id'    => 'wc_frenet_origin',
            'type'  => 'text',
            'desc'  => __( 'CEP para cálculo.', 'frenet-free-shipping-pac' ),
        ),
        array(
            'type' => 'sectionend',
            'id'   => 'wc_frenet_settings',
        ),
    );
    return array_merge( $settings, $new );
}
add_action( 'woocommerce_update_options_shipping', 'wc_frenet_update_settings' );
function wc_frenet_update_settings() {
    foreach ( array( 'wc_frenet_key', 'wc_frenet_secret', 'wc_frenet_token', 'wc_frenet_origin' ) as $opt ) {
        if ( isset( $_POST[ $opt ] ) ) {
            update_option( $opt, sanitize_text_field( wp_unslash( $_POST[ $opt ] ) ) );
        }
    }
}

/**
 * Filtra os métodos: remove apenas o PAC Frenet e ajusta o Frete Grátis.
 * Não oculta outros métodos de entrega.
 */
add_filter( 'woocommerce_package_rates', 'wc_frenet_adjust_free_and_hide_pac', 100, 2 );
function wc_frenet_adjust_free_and_hide_pac( $rates, $package ) {
    // Captura prazo do PAC (rótulo contendo "PAC")
    $pac_deadline = '';
    foreach ( $rates as $rate ) {
        if ( false !== stripos( $rate->label, 'PAC' ) ) {
            if ( preg_match( '/\(([^)]+)\)/', $rate->label, $m ) ) {
                $pac_deadline = ' (' . $m[1] . ')';
            }
            break;
        }
    }

    $new_rates = array();
    foreach ( $rates as $id => $rate ) {
        // Oculta somente o método PAC Frenet
        if ( false !== stripos( $rate->label, 'PAC' ) ) {
            continue;
        }
        // Se for Free Shipping, ajusta o label
        if ( 'free_shipping' === $rate->method_id ) {
            $label = __( 'Frete grátis', 'frenet-free-shipping-pac' );
            if ( $pac_deadline ) {
                $label .= $pac_deadline;
            }
            $rate->label = $label;
        }
        $new_rates[ $id ] = $rate;
    }
    return $new_rates;
}

/**
 * Exibe dias restantes no carrinho (opcional)
 */
add_action( 'woocommerce_cart_totals_before_shipping', 'wc_frenet_show_remaining_days' );
function wc_frenet_show_remaining_days() {
    $packages = WC()->shipping->get_packages();
    if ( empty( $packages ) ) {
        return;
    }
    $days = wc_get_frenet_pac_deadline( $packages[0] );
    if ( $days ) {
        $left = $days;
        if ( $left > 0 ) {
            echo '<tr class="free-shipping-deadline"><th>' . esc_html__( 'Prazo do frete grátis:', 'frenet-free-shipping-pac' ) . '</th><td>' . esc_html( sprintf( __( 'Faltam %d dias', 'frenet-free-shipping-pac' ), $left ) ) . '</td></tr>';
        }
    }
}

/**
 * Backup: obtém prazo via API Frenet
 */
function wc_get_frenet_pac_deadline( $package ) {
    $key    = get_option( 'wc_frenet_key' );
    $secret = get_option( 'wc_frenet_secret' );
    $token  = get_option( 'wc_frenet_token' );
    $cep    = get_option( 'wc_frenet_origin' );
    if ( ! $key || ! $secret || ! $token || ! $cep ) {
        return false;
    }
    $products = array();
    foreach ( $package['contents'] as $item ) {
        $p = $item['data'];
        $products[] = array(
            'Weight'   => floatval( $p->get_weight() ),
            'Length'   => floatval( $p->get_length() ),
            'Height'   => floatval( $p->get_height() ),
            'Width'    => floatval( $p->get_width() ),
            'Quantity' => intval( $item['quantity'] ),
        );
    }
    $body = array(
        'ShippingServiceCode' => '04014',
        'From'                => array( 'PostalCode' => $cep ),
        'To'                  => array( 'PostalCode' => $package['destination']['postcode'] ),
        'Products'            => $products,
    );
    $response = wp_remote_post(
        'https://api.frenet.com.br/shipping/pricing',
        array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Token'        => $token,
                'Key'          => $key,
                'Secret'       => $secret,
            ),
            'body'    => wp_json_encode( $body ),
            'timeout' => 30,
        )
    );
    if ( is_wp_error( $response ) ) {
        return false;
    }
    $data = json_decode( wp_remote_retrieve_body( $response ), true );
    return ! empty( $data['ShippingServices'][0]['DeliveryTime'] ) ? $data['ShippingServices'][0]['DeliveryTime'] : false;
}
?>
