<?php
//Evita el acceso directo al fichero
if (!defined('ABSPATH')) {
    die('No puedes acceder a este fichero directamente');
}

// Función para conectarse a la API y obtener datos en función del endpoint (por defecto null)
function aceprensa_posts_conectar_api($endpoint = null)
{
    //Si hay una peticón con endpoint, lo guardamos, sino, usamos el del argumento
    $endpoint = (isset($_REQUEST['endpoint'])) ? esc_html($_REQUEST['endpoint']) : $endpoint;
    //Recogemos la url de los ajustes del plugin y la ponemos para el accesso a la api con el endpoint
    $site_url = esc_attr(get_option('aceprensa_site_url'));
    $api_url = $site_url . '/wp-json' . $endpoint;

    //Definimos unos headers con los argumentos de autenticación
    $headers = array(
        'Authorization' => 'Basic ' . esc_attr(get_option('aceprensa_salt'))
    );

    //Recogemos la respuesta de la petición a la api
    $response = wp_remote_get($api_url, array(
        'headers' => $headers,
    ));

    //Comprobamos el nonce de la patición ajax
    $nonce = (isset($_REQUEST['nonce'])) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : false ;
    if ($nonce && wp_verify_nonce($nonce, 'remote-posts') != false) {
        //Manejo de errores
        if (is_wp_error($response)) {
            wp_send_json_error('Ha habido un error al conectar con el servidor remoto');
        } else {
            //Recogemos el resultado en json y lo devolvemos a la llamada ajax
            $results = json_decode(wp_remote_retrieve_body($response), true);
            wp_send_json_success($results);
        }
    } else {
        //Si no hay nonce, es que la llamada no es ajax, devolvemos sin json
        $response_code = wp_remote_retrieve_response_code($response);
        if($response_code != 200) {
            error_log(wp_remote_retrieve_body($response));
            return false;
        }
        return wp_remote_retrieve_body($response);
    }
}

// Obtiene los posts del servidor remoto
function aceprensa_posts_obtener_posts()
{
    //Recogemos el transient 'aceprensa_posts_remote_query_tmp' en la variable $posts
    $posts = get_transient('aceprensa_posts_remote_query_tmp');
    //Si está vacío, lo vamos a crear
    if ($posts === false) {

        //Recogemos los parámetros de búsqueda de los ajustes del plugin
        $status = '?status=publish';
        $limit = (get_option('aceprensa_num_posts')) ? '&per_page=' . esc_attr(get_option('aceprensa_num_posts')) : "3";
        $cats = (get_option('aceprensa_selected_categories')) ? '&categories=' . wp_json_encode(array_keys(get_option('aceprensa_selected_categories'))) : "";
        $categories = ($cats) ? str_replace("[", "", str_replace("]", "", $cats)) : "";
        $relation = ($categories) ? '&tax_relation=OR' : "";
        $post_types = (get_option('aceprensa_post_types')) ? '&post_types=' . implode(',', array_keys(get_option('aceprensa_post_types'))) : "";
        $image_size = (get_option('aceprensa_image_size')) ? '&imagesize=' . esc_attr(get_option('aceprensa_image_size')) : "";

        //Añadimos los prámetros al endpoint
        $endpoint = "/custom/v2/all-posts" . $status . $post_types . $image_size . $categories . $relation . $limit;

        //Recogemos otros parámetros que usaremos más tarde
        $aceprensa_un_click = (get_option('aceprensa_un_click')) ? esc_attr(get_option('aceprensa_username')) : "";
        $cache = (get_option('aceprensa_cache_seconds')) ? esc_attr(get_option('aceprensa_cache_seconds')) : "43200";

        //Creamos los parámetros del enlace
        $utm_source = ($aceprensa_un_click) ? "utm_source=" . $aceprensa_un_click : "utm_source=Aceprensa%20posts";
        $utm_medium = "utm_medium=plugin_aceprensa_posts";
        $utm_campaign = ($aceprensa_un_click) ? "utm_campaign=Aceprensa1click" : "utm_campaign=Aceprensa%20posts";
        $user_aceprensa1click = ($aceprensa_un_click) ? "?user=" . $aceprensa_un_click . "&" : "?" ;
        $user_and_utm_link = $user_aceprensa1click . $utm_source . "&" . $utm_medium . "&" . $utm_campaign;

        //Recogemos los resultados de la petición de los posts
        if (aceprensa_posts_conectar_api($endpoint) === false) {
            $error = "Se ha producido un error al conectar al sitio remoto. Consulte el log del servidor.";
            echo esc_html($error);
            return false;
        }
        $results = json_decode(aceprensa_posts_conectar_api($endpoint));

        //Por cada post que recojamos comprobamos el firmante
        foreach ($results as $result) {
            $firmante = array();
            $firmante_link = array();
            for ($i=0; $i < count($result->firmante); $i++) { 
                $firmante[$i] = $result->firmante[$i];
                $firmante_link[$i] = $result->firmante_link[$i] . $user_and_utm_link;
            }
            $posts[] = array(
                'date' => $result->date,
                'title' => $result->title,
                'link' => $result->permalink . $user_and_utm_link,
                'excerpt' => $result->excerpt,
                'autor'   => $firmante,
                'autor_link' => $firmante_link,
                'image' => $result->featured_image
            );
        }

        //Añadimos el array $posts al transient 'aceprensa_posts_remote_query_tmp' y le ponemos un tiempo definido previamente
        set_transient('aceprensa_posts_remote_query_tmp', $posts, $cache); // tiempo en segundos
    }
    //Devolvemos los posts
    return $posts;
}
