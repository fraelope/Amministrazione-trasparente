<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

extract(shortcode_atts(array('col' => '1', 'bar' => '0', 'con' => '0'), $atts));

switch ($col) {
    case 2:
        $atscss = "width:49%;float:left;";
        $atw2 = "width:98%;float:left;";
        break;
    case 3:
        $atscss = "width:31%;float:left;";
        $atw2 = "width:62%;float:left;";
        break;
    default:
        $atscss = "";
        break;
}

echo '
<style type="text/css">
.at-tableclass {' . $atscss . 'padding:0px 0px 0px 5px;position:relative;min-width: 200px;}
';

if (!empty($atw2)) {
    echo '#at-s-23 { ' . $atw2 . ' }';
}

echo '
.at-tableclass h3 a { text-decoration:none; cursor: default; }
.at-tableclass h3 { border: 1px solid #eee; padding: 8px 10px; background: #FBFBFB; }
.at-tableclass ul li a { text-decoration: none; }
.at-number { float: right;
  border-radius: 20px;
  background-color: white;
  height: 20px;
  width: 20px;
  border: 1px solid #eee;
  text-align: center;
  font-size: 0.8em;
  font-weight: bold; }
.at-children ul { margin-left: 1em; list-style-type: disc; }
</style>
<!-- Generato con il Plugin Wordpress Amministrazione Trasparente v.' . sanitize_text_field( get_option('at_version_number') ) . '-->';

if ($bar) {
    echo '<div style="border: 1px solid #eee; padding: 8px 10px; background: #FBFBFB;">';
    if ($bar > 2) {
        echo do_shortcode('[at-desc]');
    }
    if ($bar > 1) {
        echo '<span style="float:right;"><a href="' . get_post_type_archive_link('amm-trasparente') . '"><small>Ultimi inseriti</small></a></span>';
    }
    echo do_shortcode('[at-search]') . '</div>';
}

/**
 * Funzione ricorsiva per mostrare le sottocategorie di una tipologia
 */
function at_render_subterms($parent_id) {
    $children = get_terms(array(
        'taxonomy'   => 'tipologie',
        'parent'     => $parent_id,
        'hide_empty' => false,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ));

    if (empty($children) || is_wp_error($children)) {
        return '';
    }

    $output = '<ul class="at-children">';
    foreach ($children as $child) {
        $query = new WP_Query(array(
            'posts_per_page' => -1,
            'post_type' => 'amm-trasparente',
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'tipologie',
                    'field'    => 'term_id',
                    'terms'    => $child->term_id,
                )
            )
        ));
        $found_posts = $query->found_posts;
        wp_reset_postdata();

        $output .= '<li>';
        $output .= '<a href="' . esc_url(get_term_link($child)) . '">' . esc_html($child->name) . '</a>';
        if ($found_posts) {
            $output .= ' <span style="font-size:0.8em;color:#999;">(' . $found_posts . ')</span>';
        }

        // Richiamo ricorsivo per eventuali sottolivelli
        $output .= at_render_subterms($child->term_id);
        $output .= '</li>';
    }
    $output .= '</ul>';
    return $output;
}

$atcontatore = $atct = 0;

foreach (at_get_taxonomy_groups() as $groupName) {

    $tipologieGruppo = at_getGroupConf(sanitize_title($groupName));
    $atcontatore++;
    $atreturn = '<ul>';
    $atcounter = 0;

    foreach ($tipologieGruppo as $idTipologia) {
        $term = get_term_by('id', $idTipologia, 'tipologie');
        if (!$term || $term->parent != 0) {
            // Mostra solo categorie radice nel gruppo
            continue;
        }

        $query = new WP_Query(array(
            'posts_per_page' => -1,
            'post_type' => 'amm-trasparente',
            'post_status' => 'publish',
            'tax_query' => array(
                array(
                    'taxonomy' => 'tipologie',
                    'field'    => 'term_id',
                    'terms'    => $idTipologia,
                )
            )
        ));
        $found_posts = $query->found_posts;
        wp_reset_postdata();

        $atcounter += $found_posts;

        $atreturn .= '<li>';
        $atreturn .= '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
        if ($found_posts) {
            $atreturn .= ' <span style="font-size:0.8em;color:#999;">(' . $found_posts . ')</span>';
        }

        // 🔁 Mostra i figli
        $atreturn .= at_render_subterms($term->term_id);

        $atreturn .= '</li>';
    }

    $atreturn .= '</ul>';

    echo '<div class="at-tableclass" id="at-s-' . ++$atct . '">';
    $sez_l = sanitize_title($groupName);
    echo '<h3>';
    if ($con) {
        echo '<div class="at-number">' . esc_attr($atcounter) . '</div>';
    }
    echo '<a id="' . $sez_l . '" href="#' . $sez_l . '">' . esc_attr($groupName) . '</a></h3>';
    echo $atreturn;
    echo '</div>';

    if ($col && $atcontatore == $col) {
        echo '<div class="clear"></div>';
        $atcontatore = 0;
    }
}

if (at_option('show_love')) {
    echo '<span style="width:98%;border: 1px solid #eee;padding: 8px 10px;background: #FBFBFB;float: left;font-size: 0.7em;">
        <span style="float:right;">
            <a href="http://www.wpgov.it" target="_blank" alt="Software WPGov" title="Software WPGov">wpgov.it</a>
        </span>
        Powered by <a href="http://wordpress.org/plugins/amministrazione-trasparente/" rel="nofollow" title="Plugin Amministrazione Trasparente per Wordpress">Amministrazione Trasparente</a>
        </span>';
}

echo '<div class="clear"></div>';
?>
