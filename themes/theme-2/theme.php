<?php
// If you want to translate or customize this theme, just copy the file inside the folder
// wp-content/plugins/newsletter-pro-custom/themes
// (create it if it does not exist) and the a new theme called "theme-1" will appear
// on autocompose menu. You can rename the file if you want.




$menu_items = wp_get_nav_menu_items('Programación');
if ($menu_items == '') $menu_items = wp_get_nav_menu_items('Programacion');
if ($menu_items == '') $menu_items = wp_get_nav_menu_items('programación');
if ($menu_items == '') $menu_items = wp_get_nav_menu_items('programacion');
$ids = '';
foreach (  $menu_items as  $menu_item ) {
	$ids[] = $menu_item->object_id;	
}

$my_query = query_posts(array('post__in' => $ids));
global $post;
foreach ($my_query as $post) {
   $posts_by_id[$post->ID] = $post;
}


?>

<br />

<table cellspacing="0" align="center" border="0" style="max-width:600px; width:600px; background-color: #eee;" cellpadding="0" width="600px">
    <!-- Header -->
    <tr style="background: #455560; background-image: url(<?php echo plugins_url('header.jpg', __FILE__); ?>); height:80px;width:600px;" cellspacing="0" border="0" align="center" cellpadding="0" width="600" height="80">
        <td height="80" width="600" style="color: #fff; font-size: 30px; font-family: Arial;" align="center" valign="middle">
        <img src="<?php echo plugins_url('header.jpg', __FILE__); ?>" width="600" height="80" />
        </td>
    </tr>
    <tr style="background: #d0d0d0; height:20px;width:600px;">
        <td valign="top" height="20" width="600" bgcolor="#ffffff" align="center" style="font-family: Arial; font-size: 12px">
            <?php echo get_option('blogdescription'); ?>
        </td>
    </tr>
    <tr>
        <td>
            <table cellspacing="0" border="0" style="max-width:600px; width:600px; background-color: #eee;font-family:helvetica,arial,sans-serif;color:#555;font-size:13px;line-height:15px;" align="center" cellpadding="20" width="600px">
                <tr>
                    <td>
                        <table cellpadding="0" cellspacing="0" border="0" bordercolor="" width="100%" bgcolor="#ffffff">
                            <?php
                            
                            foreach ($ids as $id) {
														  if (!$post = $posts_by_id[$id]) continue;
														   setup_postdata($post);
                                $image = nt_post_image(get_the_ID());
                            ?>
                                <tr>
                                    <td style="font-family: Arial; font-size: 12px; border: 1px solid black;">
                                        <a href="<?php echo get_permalink(); ?>" style="color: #000; text-decoration: none"><b><?php the_title(); ?></b></a><br />

                                        <?php the_content(); ?>
                                    </td>
                                </tr>
														<?php 
														} 
                            ?>
                            </table>

                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="#ffffff" style="font-family: Arial; font-size: 12px">

                Este email se ha enviado a <b>{email}</b> porque estás suscrito a <?php echo get_option('blogname'); ?>.
            <br />

            <a href="{profile_url}">Acceso a tu suscripción</a> |

            <a href="{unsubscription_url}">Desuscribir</a>
        </td>
    </tr>
</table>
