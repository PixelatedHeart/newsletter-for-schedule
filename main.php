<?php

@include_once 'commons.php';

$nc = new NewsletterControls();

if (!$nc->is_action()) {
    $nc->data = get_option('newsletter_main');
}
else {
    if ($nc->is_action('remove')) {

        $wpdb->query("delete from " . $wpdb->prefix . "options where option_name like 'newsletter%'");

        $wpdb->query("drop table " . $wpdb->prefix . "newsletter, " . $wpdb->prefix . "newsletter_stats, " .
                $wpdb->prefix . "newsletter_emails, " . $wpdb->prefix . "newsletter_profiles, " .
                $wpdb->prefix . "newsletter_work");

        echo 'Newsletter plugin destroyed. Please, deactivate it now.';
        return;
    }

    if ($nc->is_action('save')) {
        $errors = null;

        // Validation
        $nc->data['sender_email'] = $newsletter->normalize_email($nc->data['sender_email']);
        if (!$newsletter->is_email($nc->data['sender_email'])) {
            $errors = __('El correo de envío no es correcto');
        }

        $nc->data['return_path'] = $newsletter->normalize_email($nc->data['return_path']);
        if (!$newsletter->is_email($nc->data['return_path'], true)) {
            $errors = __('El correo de retorno no es correcto');
        }
        // With some providers the return path must be left empty
        //if (empty($options['return_path'])) $options['return_path'] = $options['sender_email'];

        $nc->data['test_email'] = $newsletter->normalize_email($nc->data['test_email']);
        if (!$newsletter->is_email($nc->data['test_email'], true)) {
            $errors = __('El correo de testeo no es correcto');
        }

        $nc->data['reply_to'] = $newsletter->normalize_email($nc->data['reply_to']);
        if (!$newsletter->is_email($nc->data['reply_to'], true)) {
            $errors = __('El correo de respuesta no es correcto');
        }

        $nc->data['mode'] = (int)$nc->data['mode'];
        $nc->data['logs'] = (int)$nc->data['logs'];

        if ($errors == null) {
            update_option('newsletter_main', $nc->data);
        }
    }


    if ($action == 'test') {
        for ($i=0; $i<5; $i++) {
            if (!empty($nc->data['test_email_' . $i])) {
                $r = $newsletter->mail($nc->data['test_email_' . $i],
                        'Prueba de correo', '<p>Esto es un mensaje de prueba del plugin de Newsletter. Si lo estás leyendo, el plugin está funcionando correctamente.</p>',
                        true, null, 1);
            }
        }
        $messages = 'Correos de prueba enviados. Revisa los buzones de correo.';
    }
}


$nc->errors($errors);
$nc->messages($messages);
?>

<div class="wrap">

    <h2>Configuración de Newsletter</h2>

    <?php include dirname(__FILE__) . '/header.php'; ?>


    <form method="post" action="">
        <?php $nc->init(); ?>


        <h3>Configuración general</h3>


        <table class="form-table">
            <tr valign="top">
                <th>Nombre de envío</th>
                <td>
                    dirección de correo (requerida): <?php $nc->text('sender_email', 40); ?>
                    nombre (opcional): <?php $nc->text('sender_name', 40); ?>

                </td>
            </tr>
            <tr>
                <th>Direcciones para testeo</th>
                <td>
                    <?php for ($i=0; $i<5; $i++) { ?>
                    dirección de correo: <?php $nc->text('test_email_' . $i, 30); ?> nombre: <?php $nc->text('test_name_' . $i, 30); ?>
                    <br />
                    <?php } ?>
                    <div class="hints">
                        Estas son las direcciones a las que se enviará la newsletter cuando pulsemos "Test".<br />
                        <strong>No utilices la misma dirección de envío para recibir la newsletter. Normalmente no funciona</strong>.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Emails máximos por hora</th>
                <td>
                    <?php $nc->text('scheduler_max', 5); ?>
                    <div class="hints">
                        Si hay que enviar más de 1000 correos, debería hacerse por bloques. Aquí podemos especificar cuántos queremos enviar por hora.<br />
                        Dejar en blanco para activar restricciones.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Dirección de retorno</th>
                <td>
                    <?php $nc->text('return_path', 40); ?> (debe ser una dirección válida)
                    <div class="hints">
                        Cuando una newsletter no se puede enviar (es rechazada por el receptor) llega un correo notificándolo. Llegará a esta dirección.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Responder a</th>
                <td>
                    <?php $nc->text('reply_to', 40); ?> (debe ser una dirección válida)
                    <div class="hints">
                        Si los lectores responden por correo a una newsletter, enviarán un correo a esta dirección. 
                    </div>
                </td>
            </tr>


        </table>
        <p class="submit">
            <?php $nc->button('save', 'Save'); ?>
            <?php $nc->button('test', 'Send a test email'); ?>
        </p>




        <h3>Parámetros de configuración</h3>

        <table class="form-table">
            <!--
            <tr valign="top">
                <th><?php _e('Popup form number', 'newsletter'); ?></th>
                <td>
                    <?php $nc->text('popup_form', 40); ?>
                    <br />
                    <?php _e('
                    Form to be used for integration with wp-super-popup. Leave it empty to use the default form'); ?>
                </td>
            </tr>
            -->
            <tr valign="top">
                <th>Forzar receptor</th>
                <td>
                    <?php $nc->text('receiver', 40); ?> (debe ser una dirección válida)
                    <div class="hints">
                        Si está activado, TODO EL CORREO llegará a esta dirección EN VEZ DE a los suscriptores. Utilizar sólo para testeo.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Notificaciones</th>
                <td>
                    <?php $nc->yesno('notify'); ?>
                    <div class="hints">
                    Activa o desactiva las notificaciones de suscripción o desuscripción al administrador.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Acceso a editores</th>
                <td>
                    <?php $nc->yesno('editor'); ?>
                    <div class="hints">
                    Si está activo, los editores también podrán enviar newsletters.
                    </div>
                </td>
            </tr>

        </table>
        <p class="submit">
            <?php $nc->button('save', 'Save'); ?>
        </p>


        <h3>Chequeo del sistema</h3>

        <table class="form-table">
            <tr valign="top">
                <th>Cron</th>
                <td>
                    <?php if (defined('DISABLE_WP_CRON') && DISABLE_WP_CRON) { ?>
                    <strong>Cron NO está funcionando.</strong> Los correos no se enviarán. Chequea tu wp-config.php. Si existe la cadena DISABLE_WP_CRON, bórrala.
                    <?php } else { ?>
                    <strong>Cron está funcionando.</strong> Esto significa que la programación de correos está funcionando sin problemas.
              		<?php } ?>
                </td>
            </tr>
            <tr valign="top">
                <th>Base de datos</th>
                <td>
                    <?php $wait_timeout = $wpdb->get_var("select @@wait_timeout"); ?>
                    Tiempo en espera: <?php echo $wait_timeout; ?> segundos
                    <br />
                    <?php if ($wait_timeout > 300) { ?>
                    El tiempo de espera es correcto
                    <?php } else { ?>
                        <?php $wpdb->query("set session wait_timeout=300"); ?>
                        <?php if (300 != $wpdb->get_var("select @@wait_timeout")) { ?>
                        No se puede subir el tiempo de espera. 
                        <?php } else { ?>
                        El tiempo de espera se ha fijado por el sistema de Newsletter en 300 segundos. 
                        <?php } ?>
                    <?php } ?>
                        <div class="hints">
                            Las conexiones a la base de datos tienen un tiempo de espera. Eso quiere decir que, durante ese tiempo, si un correo no se ha enviado correctamente, el sistema lo reintentará.
                        </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Tiempo máximo de ejecución PHP</th>
                <td>
                    Tiempo máximo: <?php echo ini_get('max_execution_time'); ?> segundos
                    <div class="hints">
                       El tiempo máximo que el script puede tardar en lanzarse. Normalmente utiliza sólo 2 segundos.
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th>Límite de memoria</th>
                <td>
                    <?php echo @ini_get('memory_limit'); ?>
                    <div class="hints">
                        Lo idea es configurar el servidor a 256 MB para aguantar envíos de hasta 100.000 correos simultáneos.
                    </div>
                </td>
            </tr>

        </table>

     <!--   <p class="submit">
            <?php $nc->button_confirm('remove', 'Totally remove this plugin', 'Really sure to totally remove this plugin. All data will be lost!'); ?>
        </p> -->

    </form>
    <p></p>
</div>
