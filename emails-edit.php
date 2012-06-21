<?php
@include_once 'commons.php';
$nc = new NewsletterControls();

if (isset($_GET['id'])) {
    $nc->load($wpdb->prefix . 'newsletter_emails', $_GET['id']);
    if (empty($nc->data['id'])) {
        $nc->data['status'] = 'new';
        $nc->data['subject'] = 'Aquí va el título de la newsletter';
        $nc->data['message'] = '<p>Un correo vacío para empezar :).</p>';
        $nc->data['theme'] = 'blank';
    }
}
else {
    if ( $nc->is_action('save') || $nc->is_action('send') ) {
        $nc->save($wpdb->prefix . 'newsletter_emails');
    }

    if ($nc->is_action('send')) {

        // Fake value representing the WordPress users as target
        if ($nc->data['list'] == -1) {
            $query = "select count(*) from " .  $wpdb->prefix . "users " . $nc->data['query'];
        }
        else {
            if (!empty($nc->data['query'])) $query = "select count(*) from " . $wpdb->prefix . "newsletter " . $nc->data['query'];
            else {
                $query = "select count(*) from " . $wpdb->prefix . "newsletter where status='C'";
                if ($nc->data['list'] != 0) $query .= " and list_" . $nc->data['list'] . "=1";
                if (!empty($nc->data['sex'])) $query .= " and sex='" . $nc->data['sex'] . "'";
            }
        }
        $newsletter->log($query, 3);
        $newsletter->log('total: ' . $wpdb->get_var($query), 3);
        
        $nc->data['total'] = $wpdb->get_var($query);
        $nc->data['sent'] = 0;
        $nc->data['status'] = 'sending';
        $nc->data['last_id'] = 0;
        $nc->save($wpdb->prefix . 'newsletter_emails');
        $nc->load($wpdb->prefix . 'newsletter_emails', $nc->data['id']);
    }

    if ($nc->is_action('pause')) {
        $nc->update($wpdb->prefix . 'newsletter_emails', 'status', 'paused');
        $nc->load($wpdb->prefix . 'newsletter_emails', $nc->data['id']);
    }

    if ($nc->is_action('continue')) {
        $wpdb->query("update " . $wpdb->prefix . "newsletter_emails set status='sending' where id=" . $nc->data['id']);
        $nc->load($wpdb->prefix . 'newsletter_emails', $nc->data['id']);
    }

    if ($nc->is_action('abort')) {
        $wpdb->query("update " . $wpdb->prefix . "newsletter_emails set last_id=0, status='new' where id=" . $nc->data['id']);
        $nc->load($wpdb->prefix . 'newsletter_emails', $nc->data['id']);
    } 

    if ($nc->is_action('delete')) {
        $wpdb->query("delete from " . $wpdb->prefix . "newsletter_emails where id=" . $nc->data['id']);
        ?><script>location.href="admin.php?page=newsletter/emails.php";</script><?php
        return;
    }

    if ($nc->is_action('compose')) {
        if ($nc->data['theme'][0] == '*') $file = ABSPATH . 'wp-content/plugins/newsletter-custom/themes/' . substr($nc->data['theme'], 1) .
                '/theme.php';
        else $file = dirname(__FILE__) . '/themes/' . $nc->data['theme'] . '/theme.php';

        ob_start();
        @include($file);
        $nc->data['message'] = ob_get_contents();
        ob_end_clean();
    }

    if ($nc->is_action('test')) {
        $nc->save($wpdb->prefix . 'newsletter_emails');
        $users = newsletter_get_test_subscribers();
        $email = new stdClass();
        $email->message = $nc->data['message'];
        $email->subject = $nc->data['subject'];
        $email->track = $nc->data['track'];
        $email->type = 'email';
        $newsletter->send($email, $users);
    }
}


$options_main = get_option('newsletter_main', array());

$options_profile = get_option('newsletter_profile', array());
$lists = array('0' => 'A todos los suscriptores', '-1'=>'A los usuarios de WP');
for ($i = 1; $i <= 40; $i++) {
    $lists['' . $i] = '(' . $i . ') ' . $options_profile['list_' . $i];
}

// Themes
$themes[''] = 'Plantillas de newsletter';
$themes['blank'] = 'Vacía';
//$themes['theme-1'] = 'Newsletter theme 1';
$themes['theme-2'] = 'Programación';
$themes['theme-3'] = 'Sala de Prensa';

$nc->errors($errors);
$nc->messages($messages);

function newsletter_get_theme_file($theme) {
    if ($theme[0] == '*') $file = ABSPATH . 'wp-content/plugins/newsletter-custom/themes/' . substr($theme, 1) . '/theme.php';
    else $file = dirname(__FILE__) . '/themes/' . $theme . '/theme.php';
}

function newsletter_get_theme_css_url($theme) {
    if ($theme[0] == '*') $file = 'newsletter-custom/themes/' . substr($theme, 1) . '/style.css';
    else $file = 'newsletter/themes/' . $theme . '/style.css';
    if (!file_exists(ABSPATH . 'wp-content/plugins/' . $file)) return get_option('siteurl') . '/wp-content/plugins/newsletter/themes/empty.css';
    return get_option('siteurl') . '/wp-content/plugins/' . $file;
}

?>

<script type="text/javascript" src="<?php echo get_option('siteurl'); ?>/wp-content/plugins/newsletter/tiny_mce/tiny_mce.js"></script>
<script type="text/javascript">
    tinyMCE.init({
        mode : "specific_textareas",
        editor_selector : "visual",
        theme : "advanced",
        plugins: "table,fullscreen,paste",
        theme_advanced_disable : "styleselect",
        theme_advanced_buttons1_add: "forecolor,blockquote,code,pastetext,pasteword,selectall",
        theme_advanced_buttons3 : "tablecontrols,fullscreen",
        relative_urls : false,
        remove_script_host : false,
        theme_advanced_toolbar_location : "top",
        document_base_url : "<?php echo get_option('home'); ?>/",
        content_css: "<?php echo newsletter_get_theme_css_url($nc->data['theme']) . '?' . time(); ?>"
    });
</script>


<div class="wrap">

    <h2>Crear Newsletter</h2>

    <form method="post" action="admin.php?page=newsletter/emails-edit.php">
        <?php $nc->init(); ?>
        <?php $nc->hidden('id'); ?>
        <?php $nc->hidden('status'); ?>

        <table class="form-table">

            <tr valign="top">
                <th>Tema</th>
                <td>
                    <?php $nc->select_grouped('theme', array(
                            array_merge(array(''=>'Custom themes'), newsletter_get_themes()),
                            $themes,
                            $themes_panel
                            ));
                    ?>
                    <?php $nc->button('compose', 'Generar'); ?> (Se generará una nueva newsletter con los artículos del menú "Newsletter" o "Programación", según corresponda). 
                    <div class="hints">
                        Cambiar el tema no guarda la newsletter. Tienes que guardarla o programarla para mantenerla. <br/>
                        Cuando envías una newsletter directamente, ésta se guarda en el sistema.
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th>Título</th>
                <td>
                    <?php $nc->text('subject', 70); ?>
                   <div class="hints">
                        Etiquetas: Puedes utilizar <strong>{name}</strong> para poner el nombre del usuario.
                   </div>
                </td>
            </tr>

            <tr valign="top">
                <th>Mensaje</th>
                <td>
                    <?php $nc->data['editor'] == 0?$nc->editor('message', 20):$nc->textarea_fixed('message', '100%', 400); ?>
                    <br />
                    <?php $nc->select('editor', array(0=>'Utilizar el editor visual', 1=>'Utilizar la vista HTML')); ?>
                    <div class="hints">
                        Etiquetas: <strong>{name}</strong> nombre del usuario;
                        <strong>{unsubscription_url}</strong> URL para desuscribirse (debería estar siempre);
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th>Para...</th>
                <td>
                    lista: <?php $nc->select('list', $lists); ?>
                    <div class="hints">
                        Cuando seleccionas WordPress users, no se puede cancelar la suscripción. 
                    </div>
                </td>
            </tr>
	<script>
           jQuery(document).ready(function() {
           jQuery( "#day" ).datepicker( { firstDay: 1 } );
           });

    </script>

            <tr valign="top">
                <th>Programar</th>
                <td>
                    Día: <input type="text" id="day" name="day" value="" size="20" tabindex="6" /></div>
                    Hora: <select name="hour" id="hour" class="" tabindex="7">
                    <?php
						$start = "00:00";
						$end = "23:45";
						
						$tStart = strtotime($start);
						$tEnd = strtotime($end);
						$tNow = $tStart;
						
						while($tNow <= $tEnd){
							$date = date("H:i",$tNow);
							echo "<option value='$date'>$date</option>";
						 	 $tNow = strtotime('+15 minutes',$tNow);
						}
					?>
						</select>
                    
                        <div class="hints">
                        Puedes programar una fecha y pinchar en "Programar". Tu newsletter se programará para enviarse en el momento señalado.
                    </div>
                </td>
            </tr>


            <!--
            <tr valign="top">
                <th>Query<br/><small>Really advanced!</small></th>
                <td>
                    select * from wp_newsletter<br />
                    <?php $nc->textarea('query'); ?>
                    <br />
                    and id>... order by id limit ...
                    <div class="hints">
                        If you want to specify a different query to extract subscriber from Newsletter Pro database, here you
                        can write it. Be aware that the query starts and ends as specified, so your SQL snippet needs to create a
                        complete and working query.<br />
                        Leave this area empty to leave Newsletter Pro doing the work.<br />
                        When you specify a query, options like the target list will be ignored.<br />
                        For examples of queries study the documentation panel.
                    </div>
                </td>
            </tr>
            -->

        </table>

        <p class="submit">
            <?php if ($nc->data['status'] != 'sending') $nc->button('save', 'Programar'); ?>
            <?php if ($nc->data['status'] != 'sending') $nc->button_confirm('test', 'Salvar y probar', 'Salva la newsletter y se envía a los usuarios de prueba'); ?>

            <?php if ($nc->data['status'] == 'new') {
                $nc->button_confirm('send', 'Enviar', 'Enviar la newsletter');
            } ?>
            <?php if ($nc->data['status'] == 'sending') $nc->button_confirm('pause', 'Pause', 'Pause the delivery?'); ?>
            <?php if ($nc->data['status'] == 'paused') $nc->button_confirm('continue', 'Continue', 'Continue the delivery?'); ?>
            <?php if ($nc->data['status'] != 'new') $nc->button_confirm('abort', 'Abort', 'Abort the delivery?'); ?>
            <?php if ($nc->data['id'] != 0) $nc->button_confirm('delete', 'Borrar newsletter', '¿Quieres borrar esta newsletter?'); ?>
            (Estado de la newsletter: <?php echo $nc->data['status']; ?>)
        </p>

    </form>
</div>
