<?php
/**
 * Plugin Name: VERTEX Security Demo
 * Description: Demostración académica de consultas preparadas,
 * sanitización, escape de salida y control de acceso.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Consulta segura mediante parámetros preparados.
 */
function vertex_buscar_producto_seguro($producto_id) {
    global $wpdb;

    $producto_id = absint($producto_id);

    if ($producto_id <= 0) {
        return null;
    }

    $sql = $wpdb->prepare(
        "SELECT ID, post_title
         FROM {$wpdb->posts}
         WHERE ID = %d
         AND post_type = %s
         LIMIT 1",
        $producto_id,
        'product'
    );

    return $wpdb->get_row($sql);
}

/**
 * Shortcode de demostración:
 * [vertex_seguridad]
 */
function vertex_shortcode_seguridad() {

    if (!current_user_can('manage_options')) {
        return '<p>Acceso no autorizado.</p>';
    }

    $mensaje = '';

    if (
        isset($_SERVER['REQUEST_METHOD']) &&
        $_SERVER['REQUEST_METHOD'] === 'POST' &&
        isset($_POST['vertex_demo_nonce'])
    ) {

        $nonce = sanitize_text_field(
            wp_unslash($_POST['vertex_demo_nonce'])
        );

        if (!wp_verify_nonce($nonce, 'vertex_demo_accion')) {
            return '<p>Solicitud no válida.</p>';
        }

        $entrada = isset($_POST['vertex_demo_texto'])
            ? sanitize_text_field(
                wp_unslash($_POST['vertex_demo_texto'])
            )
            : '';

        $mensaje = '<p>Texto procesado: ' .
            esc_html($entrada) .
            '</p>';
    }

    ob_start();
    ?>

    <div class="vertex-security-demo">

        <h3>Demostración de codificación segura</h3>

        <form method="post">

            <?php
            wp_nonce_field(
                'vertex_demo_accion',
                'vertex_demo_nonce'
            );
            ?>

            <label for="vertex_demo_texto">
                Ingrese un texto:
            </label>

            <input
                type="text"
                id="vertex_demo_texto"
                name="vertex_demo_texto"
                maxlength="100"
                required
            >

            <button type="submit">
                Procesar
            </button>

        </form>

        <?php echo $mensaje; // Salida escapada previamente. ?>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode(
    'vertex_seguridad',
    'vertex_shortcode_seguridad'
);