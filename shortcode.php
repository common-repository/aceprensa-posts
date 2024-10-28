<?php
//Evita el acceso directo al fichero
if (!defined('ABSPATH')) {
    die('No puedes acceder a este fichero directamente');
}
//Obtenemos los posts en la variable
$posts = aceprensa_posts_obtener_posts();

if (!$posts) {
    die();
}

// Inicializar $html como una cadena vacía
$html = '<div class="aceprensa_posts-container">';

//Por cada post, añadimos una estructura html que lo va mostrando correctamente
$aceprensa_site_url = esc_attr(get_option('aceprensa_site_url'));
foreach ($posts as $post) {
    $post_image           = esc_js($post["image"]);
    $post_link            = esc_js($post["link"]);
    $post_autor_link      = $post["autor_link"];
    $post_title           = esc_js($post["title"]);
    $post_date            = DateTime::createFromFormat('d/m/Y', $post["date"]);
    $post_autor           = $post['autor'];
    $post_excerpt         = $post["excerpt"];

    $html .= <<<HTML
        <div class="aceprensa_post-container">
            <div class="aceprensa_posts-image"><img src="$post_image" /></div>
            <div class="aceprensa_posts-content">
                <div class="aceprensa_posts-title">
                    <h2>
                        <a href="$post_link" target="_blank">
                            $post_title
                        </a>
                    </h2>
                </div>
                <div class="aceprensa_posts-date">
                    {$post_date->format('d-M-Y')}
                </div>
                <?php if($post_autor): ?>
                <div class="aceprensa_posts-autor">
                    <?php for ($i = 0; $i < count($post_autor); $i++): ?>
                        <span class="firmante">
                            <a href="{$aceprensa_site_url}/firmantes/{$post_autor_link[$i]}" target="_blank">
                                {$post_autor[$i]}
                            </a>
                        </span>
                    <?php endfor; ?>
                </div>
                <?php endif; ?>
                <div class="aceprensa_posts-excerpt">{$post_excerpt}</div>
            </div>
        </div>
    HTML;
}
$html .= '</div>'; // Concatenar con punto

// Retornar el HTML generado
echo $html;