<?php
//Evita el acceso directo al fichero
if (!defined('ABSPATH')) {
    die('No puedes acceder a este fichero directamente');
}

$posts = aceprensa_posts_obtener_posts();

?>
<!--Aquí se crea y estructura el formulario de ajustes del plugin -->
<div class="wrap">
    <h1>Ajustes de Aceprensa posts</h1>
    <div id="dashboard-widgets-wrap">
        <div id="dashboard-widgets" class="metabox-holder">
            <div id="postbox-container-1" class="postbox-container">
                <form method="post" action="options.php">
                    <?php settings_fields('aceprensa-posts-settings-group'); ?>
                    <?php do_settings_sections('aceprensa-posts-settings-group'); ?>
                    <?php $selected_categories = get_option('aceprensa_selected_categories'); ?>
                    <?php $selected_post_types = get_option('aceprensa_post_types'); ?>
                    <input type="hidden" name="savedcats" class="savedcats" value='<?php echo wp_json_encode($selected_categories); ?>'>
                    <input type="hidden" name="aceprensa_salt" value="<?php echo esc_attr(get_option('aceprensa_salt')); ?>" />
                    <input type="hidden" name="aceprensa_site_url" value="<?php echo esc_attr(get_option('aceprensa_site_url')); ?>" />
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">Integrar con Aceprensa a un click</th>
                            <td>
                                <?php $checked = (get_option('aceprensa_un_click')) ? "checked" : ""; ?>
                                <input type="checkbox" id="aceprensa_un_click" name="aceprensa_un_click" <?php echo esc_attr($checked); ?>>
                            </td>
                        </tr>
                        <tr valign="top" id="aceprensa_username_row" style="display: none;">
                            <th scope="row">Nombre de Usuario</th>
                            <td>
                                <?php
                                $aceprensa_username_value = (get_option('aceprensa_username')) ? " value='" . esc_attr(get_option('aceprensa_username')) . "' " : "" ;
                                ?>
                                <input type="text" name="aceprensa_username" placeholder="Usuario un click" <?php echo $aceprensa_username_value; ?> />
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Categorías</th>
                            <td>
                                <select name="aceprensa_categories" id="aceprensa_categories" multiple="multiple" style="min-width: 300px;">
                                    <!-- Aquí generaremos las opciones dinámicamente con JavaScript -->
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Número artículos</th>
                            <td>
                                <select name="aceprensa_num_posts" id="aceprensa_num_posts">
                                    <?php
                                    $num_posts = (get_option('aceprensa_num_posts')) ? esc_attr(get_option('aceprensa_num_posts')) : "";
                                    ?>
                                    <option selected value="<?php echo esc_attr($num_posts); ?>"><?php echo esc_attr($num_posts); ?></option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4</option>
                                    <option value="5">5</option>
                                    <option value="6">6</option>
                                    <option value="7">7</option>
                                    <option value="8">8</option>
                                    <option value="9">9</option>
                                    <option value="10">10</option>
                                    <option value="11">11</option>
                                    <option value="12">12</option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Tamaño de la imagen</th>
                            <td>
                                <select name="aceprensa_image_size" id="aceprensa_image_size">
                                    <?php
                                    $image_size = (get_option('aceprensa_image_size')) ? esc_attr(get_option('aceprensa_image_size')) : "";
                                    ?>
                                    <option selected value="<?php echo esc_attr($image_size); ?>"><?php echo esc_attr($image_size); ?></option>
                                    <option value="thumbnail">Thumbnail</option>
                                    <option value="medium">Medium</option>
                                    <option value="large">Large</option>
                                    <option value="full">Full</option>
                                </select>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Selecciona los tipos de artículos</th>
                            <td>
                                <div>
                                    <input type="checkbox" name="aceprensa_post_types[post]" id="aceprensa_post_types[post]" <?php echo checked(isset($selected_post_types['post'])); ?>>
                                    <label for="aceprensa_post_types[post]">Artículos</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="aceprensa_post_types[cine_y_series]" id="aceprensa_post_types[cine_y_series]" <?php echo checked(isset($selected_post_types['cine_y_series']));; ?>>
                                    <label for="aceprensa_post_types[cine_y_series]">Cine y Series</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="aceprensa_post_types[libros]" id="aceprensa_post_types[libros]" <?php echo checked(isset($selected_post_types['libros'])); ?>>
                                    <label for="aceprensa_post_types[libros]">Libros</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="aceprensa_post_types[juegos]" id="aceprensa_post_types[juegos]" <?php echo checked(isset($selected_post_types['juegos'])); ?>>
                                    <label for="aceprensa_post_types[juegos]">Videojuegos</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="aceprensa_post_types[resenas-espectaculos]" id="aceprensa_post_types[resenas-espectaculos]" <?php echo checked(isset($selected_post_types['resenas-espectaculos'])); ?>>
                                    <label for="aceprensa_post_types[resenas-espectaculos]">Espectáculos</label>
                                </div>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row">Tiempo en segundos que se guardarán los posts hasta la próxima consulta. Por defecto 43200 (12 horas):</th>
                            <td>
                                <?php $cache = (get_option('aceprensa_cache_seconds')) ? esc_attr(get_option('aceprensa_cache_seconds')) : "43200"; ?>
                                <input type="number" min=3600 name="aceprensa_cache_seconds" value="<?php echo  esc_attr($cache); ?>">
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
            <div id="postbox-container-2" class="postbox-container">
                <div id="dashboard_aceprensa_posts_preview" class="postbox ">
                    <div class="postbox-header">
                        <h2 class="hndle">Previsualización</h2>
                    </div>
                    <div class="inside">
                        <div id="aceprensa_posts_preview-widget">
                            <div id="published-posts" class="aceprensa_posts_preview-block">
                            <?php include 'shortcode.php'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>