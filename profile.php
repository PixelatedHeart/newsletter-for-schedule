<?php
@include_once 'commons.php';

$nc = new NewsletterControls();

if (!$nc->is_action()) {
    $nc->data = get_option('newsletter_profile');
}
else {
    if ($nc->is_action('save')) {
        update_option('newsletter_profile', $nc->data);
    }
    if ($nc->is_action('reset')) {
        include dirname(__FILE__) . '/languages/en_US.php';
        @include dirname(__FILE__) . '/languages/' . WPLANG . '.php';
        update_option('newsletter_profile', $defaults_profile);
        $nc->data = $defaults_profile;
    }
}

$nc->errors($errors);
$nc->messages($messages);

$status = array(0=>'Desactivado', 1=>'Sólo en la página de perfil', 2=>'Aparece en la página de suscripción');
?>
<script type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/newsletter/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        mode : "specific_textareas",
        editor_selector : "visual",
        theme : "advanced",
        theme_advanced_disable : "styleselect",
        relative_urls : false,
        remove_script_host : false,
        theme_advanced_buttons3: "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_resizing : true,
        theme_advanced_statusbar_location: "bottom",
        document_base_url : "<?php echo get_option('home'); ?>/",
        content_css : "<?php echo get_option('blogurl'); ?>/wp-content/plugins/newsletter/editor.css?" + new Date().getTime()
    });
</script>

<div class="wrap">

    <h2>Listas de usuarios</h2>

    <?php //include dirname(__FILE__) . '/header.php'; ?>
    

    <form action="" method="post">
    <?php $nc->init(); ?>

        




        <h3>Listas</h3>
        <p>
            Recuerda que las listas no son listas de usuario separadas. Son opciones que selecciona el suscriptor (o el administrador) y que se suelen referir a los temas y/o los destinadarios de las newsletters.
        </p>
        <table class="form-table">
            <tr>
                <th>Listas / Opciones</th>
                <td>
                    <table class="widefat">
                       <thead>
                    <tr>
                        <th>Campo</th><th>Dónde</th><th>Configuración</th>
                    </tr>
                        </thead>
                    <?php for ($i=1; $i<=40; $i++) { ?>
                        <tr><td>Lista <?php echo $i; ?></td><td><?php $nc->select('list_' . $i . '_status', $status); ?></td><td>nombre: <?php $nc->text('list_' . $i); ?></td></tr>
                    <?php } ?>
                    </table>
                    <div class="hints">
                        Si desactivas una lista no le aparecerá a los usuarios, pero pueden ser asignadas por los administradores. Se pueden considerar listas privadas. 
                    </div>
                </td>
            </tr>
        </table>
        <p class="submit"><?php $nc->button('save', 'Save'); ?></p>

       
    </form>
</div>