<?php

@include_once 'commons.php';

$options = get_option('newsletter');

if ($action == 'save') {
    $options = stripslashes_deep($_POST['options']);
    $options['confirmed_url'] = trim($options['confirmed_url']);
    if ($errors == null) {
        update_option('newsletter', $options);
    }
}


if ($action == 'schedule') {
    $options = stripslashes_deep($_POST['options']);
    $options['confirmed_url'] = trim($options['confirmed_url']);
    if ($errors == null) {
        update_option('newsletter', $options);
    }
}

if ($action == 'reset') {
    @include_once(dirname(__FILE__) . '/languages/en_US_options.php');
    if (WPLANG != '') @include_once(dirname(__FILE__) . '/languages/' . WPLANG . '_options.php');
    $options = array_merge($options, $newsletter_default_options);
    update_option('newsletter', $options);
}

$nc = new NewsletterControls($options);
?>

<?php if ($options['novisual'] != 1) { ?>
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
    <?php } ?>

<div class="wrap">

    <?php $nc->errors($errors); ?>

    <h2>Suscripción y cancelación de suscripción</h2>
    <p>
        En este panel se pueden personalizar todos los mensajes.<br />
        Todas las etiquetas que se pueden utilizar en la personalización están en la zona de  <a href="#documentation">documentación (pincha aquí para verlo)</a>.
    </p>


    <form method="post" action="">
        <?php $nc->init(); ?>

        <h3>Proceso de suscripción</h3>
        <div>
        <p>Selecciona el formato de suscripción.</p>
        <table class="form-table">
            <tr valign="top">
                <th>Formato</th>
                <td>
                    <?php $nc->select('noconfirmation', array(0=>'Double Opt In', 1=>'Single Opt In')); ?>
                    <div class="hints">
                        <strong>Double Opt In</strong>: se envía al suscriptor un correo con un enlace de activación. Deben hacer clic en él para activar su suscripción a la Newsletter.<br />
                        <strong>Single Opt In</strong>: sólo se envía un correo indicando que ya está suscrito a la Newsletter.<br />
                    </div>
                </td>
            </tr>
        </table>
        <p class="submit">
            <?php $nc->button('save', __('Save', 'newsletter')); ?>
        </p>
        </div>
        
        <h3>Suscripción</h3>
        <div>
        <table class="form-table">
            <tr valign="top">
                <th>Texto de la página de suscripción</th>
                <td>
                    <?php $nc->editor('subscription_text'); ?>
                    <div class="hints">
                    Este texto se muestra antes del formulario de suscripción. <strong>Para crear la página de suscripción, por favor habla con Paco Olivares diciéndole qué texto debe incluirse en ella</strong>. 
                    
                    <!--&lt;form&gt;<br />
                    Your email: &lt;input type="text" name="ne"/&gt;<br />
                    &lt;input type="submit" value="Subscribe now!"/&gt;<br />
                    &lt;/form&gt;<br />
                    Field names are: "ne" email, "nn" name, "ns" surname, "nx" sex (values: f, m, n),
                    "nl[]" list (the field value must be the list number, you can use checkbox, radio, select or even hidden
                    HTML input tag), "npN" custom profile (with N from 1 to 19).-->
                    </div>
                </td>
            </tr>
        </table>

        <p class="submit">
            <?php $nc->button('save', 'Save'); ?>
        </p>
        </div>



        <h3>Correo de confirmación (rellenar sólo si hemos seleccionado "Double Opt In" como formato de suscripción)</h3>
        <div>
        <table class="form-table">
            <tr valign="top">
                <th>Mensaje de confirmación (1)</th>
                <td>
                    <?php $nc->editor('subscribed_text'); ?>
                    <div class="hints">
                        Éste es el mensaje que se enviará a quien haya pinchado en "suscribir", avisándole de que le llegará un correo en breve donde podrá activar su cuenta. <br/>Recuerda al usuario en el mensaje que revise su carpeta de SPAM y que siga las instrucciones del correo electrónico.
                    </div>
                </td>
            </tr>

            <!-- CONFIRMATION EMAIL -->
            <tr valign="top">
                <th>Mensaje de confirmación </th>
                <td>
                    <?php $nc->email('confirmation'); ?>
                    <div class="hints">
                        El mensaje donde puede confirmar su suscripción. No olvides el <strong>{subscription_confirm_url}</strong>. Si no lo añades, el usuario no podrá confirmar su suscripción. 
                    </div>
                </td>
            </tr>
        </table>

        <p class="submit">
            <?php $nc->button('save', 'Save'); ?>
        </p>
        </div>


        
        <h3>Bienvenida</h3>
        <div>
        <table class="form-table">
            <tr valign="top">
                <th>Mensaje de bienvenida</th>
                <td>
                    <?php $nc->editor('confirmed_text'); ?>
                    <div class="hints">
                        El mensaje que se muestra en la página cuando confirma su suscripción por correo (Double Opt In) o cuando se ha registrado (Single Opt In).<br />
                        Puedes usar <strong>{profile_form}</strong> para añadir un enlace a su perfil.
                    </div>
                </td>
            </tr>

            <tr valign="top">
                <th>Mensaje alternativo</th>
                <td>
                    <?php $nc->text('confirmed_url', 70); ?>
                    <div class="hints">
                        Si añades aquí una URL, el mensaje de bienvenida se ignorará y se redireccionará a esta página.
                    </div>
                </td>
            </tr>


            <!-- WELCOME/CONFIRMED EMAIL -->
            <tr valign="top">
                <th>
                    Correo de bienvenida<br /><small>The right place where to put bonus content link</small>
                </th>
                <td>
                    <?php $nc->email('confirmed'); ?>
                    <div class="hints">
                        El correo que se envía al usuario una vez confirmada la suscripción. Es el sitio perfecto para añadir más información sobre nosotros.<br />
                        También es buena idea añadir la <strong>{unsubscription_url}</strong> y la <strong>{profile_url}</strong>, que permiten al usuario cancelar, modificar o completar sus datos. 
                   </div>
                </td>
            </tr>

        </table>

        <p class="submit">
            <?php $nc->button('save', 'Save'); ?>
        </p>
        </div>


        <h3>Cancelación</h3>
        <div>

        <table class="form-table">
            <tr valign="top">
                <th>Mensaje de cancelación</th>
                <td>
                    <?php $nc->editor('unsubscription_text'); ?>
                    <div class="hints">
                        Este texto es el que se muestra al pulsar en "desuscribirse" en cualquier newsletter. <strong>Debe estar insertado</strong> el enlace de desuscripción <strong>{unsubscription_confirm_url}</strong>.
                    </div>
                </td>
            </tr>

            <!-- Text showed to the user on successful unsubscription -->
            <tr valign="top">
                <th>Mensaje de despedida</th>
                <td>
                    <?php $nc->editor('unsubscribed_text'); ?>
                    <div class="hints">
                        El mensaje de despedida :(.
                    </div>
                </td>
            </tr>

            <!-- GOODBYE EMAIL -->
            <tr valign="top">
                <th>Correo de despedida</th>
                <td>
                    <?php $nc->email('unsubscribed'); ?>
                    <div class="hints">
                        El corre de despedida que se envía tras haber confirmado la desuscripción. Si no quieres que se envíe, puedes dejar el asunto vacío. 
                    </div>
                </td>
            </tr>
        </table>
        <p class="submit">
            <?php $nc->button('save', 'Save'); ?>
        </p>
        </div>
        

        <a name="documentation"></a>
        <h3>Documentación</h3>
<div>
    <h4>Datos de usuario</h4>
    <p>
        <strong>{name}</strong>
        Nombre de usuario<br />
        <strong>{surname}</strong>
        Apellido(s) de usuario<br />
        <strong>{email}</strong>
        Correo electrónico<br />
        <strong>{ip}</strong>
        La IP desde la que se suscribió<br />
        <strong>{id}</strong>
        El ID del usuario<br />
        <strong>{token}</strong>
        La clave secreta de usuario (es interna del ssisteam)<br />
        <strong>{profile_N}</strong>
        El tipo de perfil de usuario (desde 1 a 19)<br />
    </p>

    <h4>URLs de acción</h4>
    <p>
        <strong>{subscription_confirm_url}</strong>
        Confirmación de la suscripción cuando usamos Double Opt In. Para usar en el correo de confirmación.<br />
        <strong>{unsubscription_url}</strong>
        URL para comenzar el proceso de cancelación. Para usarlo en cada newsletter, permitiendo al usuario cancelar su suscripción.<br />
        <strong>{unsubscription_confirm_url}</strong>
        URL de cancelación inmediata. Se utiliza en el segundo paso de la cancelación de suscripción, o en el primero si no se quiere el mensaje de confirmación de cancelación.<br />
        <strong>{profile_url}</strong>
        El acceso al perfil de usuario.<br />
    </p>
</div>

    </form>
</div>
