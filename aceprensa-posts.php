<?php
/**
 * Plugin Name: Aceprensa posts
 * Description: Un plugin para obtener los últimos artículos publicados en Aceprensa.
 * Version: 1.0.4
 * Author: Aceprensa
 * Author URI: https://www.aceprensa.com
 * Email: mrojas@aceprensa.com
 * License: GPLv3
 * Text Domain: aceprensa-posts
 * 
 * Aceprensa posts is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Aceprensa posts is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Incluye las funciones auxiliares del plugin
include plugin_dir_path(__FILE__) . '/inc/functions.php';

// Define los ajustes de la página en el menú de administración.
function aceprensa_posts_options_page()
{
    // Icono de administración obtenido del fichero y pasado como imagen en base64
    $svg = file_get_contents(plugin_dir_path(__FILE__) . 'assets/img/icon.svg');
    $menu_icon = 'data:image/svg+xml;base64,' . base64_encode($svg);
    add_menu_page(
        'Ajustes de Aceprensa posts',   // Título de la página
        'Aceprensa posts',              // Título del menú
        'manage_options',               // Permisos de acceso: 'capabilities'
        'aceprensa-posts',              // Slug de la página / Nombre fichero
        'aceprensa_posts_page',         // Función a ejecutar / null
        $menu_icon,                     // Icono del menú
        20                              // Posición del menú
    );
}

// Opciones por defecto
$default_settings = array(
    'aceprensa_site_url' => 'https://www.aceprensa.com',
    'aceprensa_cache_seconds' => '43200',
    'aceprensa_salt' => 'YWNlcHJlbnNhX3Bvc3RzOnFOOE0gV1M1MyBiZkYyIHZrd1cgQk9yayB0U2FE',
    'aceprensa_num_posts' => '3',
    'aceprensa_image_size' => 'medium'
);

// Agrega una acción para cargar la página de ajustes.
add_action('admin_menu', 'aceprensa_posts_options_page');

// Registra y guarda las opciones.
function aceprensa_posts_register_settings()
{
    register_setting('aceprensa-posts-settings-group', 'aceprensa_site_url');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_username');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_salt');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_selected_categories');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_num_posts');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_image_size');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_un_click');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_post_types');
    register_setting('aceprensa-posts-settings-group', 'aceprensa_cache_seconds');
}
add_action('admin_init', 'aceprensa_posts_register_settings');

// Aplica las opciones por defecto del plugin
foreach ($default_settings as $option => $setting) {
    add_option($option, $setting);
}

// Muestra la página de ajustes.
function aceprensa_posts_page()
{
    include plugin_dir_path(__FILE__) . 'admin-view.php';
    // Registrar y cargar admin.js y el CSS de la página de administración
    wp_register_script('admin-js', plugin_dir_url(__FILE__) . 'assets/js/admin.js', array('jquery'), '1.0.1', true);
    wp_enqueue_script('admin-js');
    wp_register_style('aceprensa_posts_preview_style', plugin_dir_url(__FILE__) . 'assets/css/preview.css', array(), '1.0.0', 'all');
    wp_enqueue_style('aceprensa_posts_preview_style');

    // Verifica si ya está cargada la librería Select2
    global $wp_scripts;
    if ( !in_array('dce-select2', $wp_scripts->queue) ) {
        // Registrar y cargar Select2
        wp_register_script( 'select2', plugin_dir_url(__FILE__) . 'assets/js/select2.min.js', array( 'jquery' ), '4.0.13', true ); 
        wp_enqueue_script( 'select2' );

        // Registrar y cargar el CSS de Select2
        wp_register_style( 'select2-css', plugin_dir_url(__FILE__) . 'assets/css/select2.min.css', false, '4.0.13' );
        wp_enqueue_style( 'select2-css' );
    }

    wp_localize_script('admin-js', 'remotePostsData', array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('remote-posts')
    ));
}

//Habilita la función conectar_api en el admin_ajax de wordpress
add_action('wp_ajax_conectar_api', 'aceprensa_posts_conectar_api');

//Crea la función que se encarga de borrar el transient de los posts
function aceprensa_posts_delete_posts_transient()
{
    if (get_transient('aceprensa_posts_remote_query_tmp') !== false) {
        delete_transient('aceprensa_posts_remote_query_tmp');
    }
}
//Si se actualiza cualquier ajuste, llamo a la función
add_action('update_option', 'aceprensa_posts_check_options_update', 10, 3);

//Recoge los ajustes de aceprensa y comprueba si son ellos los que se han actualizado
function aceprensa_posts_check_options_update($option, $old_value, $value)
{
    //Array con los ajustes del plugin
    $opciones_aceprensa = array(
        'aceprensa_site_url',
        'aceprensa_username',
        'aceprensa_password',
        'aceprensa_selected_categories',
        'aceprensa_num_posts',
        'aceprensa_image_size',
        'aceprensa_un_click',
        'aceprensa_post_types',
        'aceprensa_cache_seconds'
    );

    //Si hay un cambio en los ajustes del plugin, llama a la funcion que borra el transient
    if (in_array($option, $opciones_aceprensa)) {
        aceprensa_posts_delete_posts_transient();
    }
}
// Registra el fichero css para el shortcode pero no lo mete en la cola
function aceprena_posts_shortcode_extra_files()
{
    wp_register_style('aceprensa_posts_style', plugin_dir_url(__FILE__) . 'assets/css/shortcode.css', array(), '1.0.0', 'all');
}
add_action('wp_enqueue_scripts', 'aceprena_posts_shortcode_extra_files');

// Comprueba si hay una llamada a la función que ejecuta el shortcode
function aceprensa_posts_shortcode()
{
    // Si existe la llamada encola el estilo para que sólo se ejecute en las páginas donde se use
    wp_enqueue_style('aceprensa_posts_style');
    
    // Incluir el archivo shortcode.php y capturar la salida
    ob_start();
    include plugin_dir_path(__FILE__) . 'shortcode.php';
    $html = ob_get_clean();

    // Retornar el HTML generado por shortcode.php
    return $html; 
}
//Añade el shortcode
add_shortcode('aceprensa-posts', 'aceprensa_posts_shortcode');
