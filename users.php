<?php

@include_once 'commons.php';

$options = stripslashes_deep($_POST['options']);
$options_lists = get_option('newsletter_profile');
$options_main = get_option('newsletter_main');

$lists = array();
for ($i=1; $i<=40; $i++)
{
    $lists[''.$i] = '(' . $i . ') ' . $options_lists['list_' . $i];
}

if ($action == 'resend') {
    $user = $newsletter->get_user($options['subscriber_id']);
    $opts = get_option('newsletter');
    $newsletter->mail($user->email, $newsletter->replace($opts['confirmation_subject'], $user), $newsletter->replace($opts['confirmation_message'], $user));
}

if ($action == 'resend_welcome') {
    $user = $newsletter->get_user($options['subscriber_id']);
    $opts = get_option('newsletter');
    $newsletter->mail($user->email, $newsletter->replace($opts['confirmed_subject'], $user), $newsletter->replace($opts['confirmed_message'], $user));
}

if ($action == 'remove') {
    $wpdb->query($wpdb->prepare("delete from " . $wpdb->prefix . "newsletter where id=%d", $options['subscriber_id']));
    unset($options['subscriber_id']);
}

if ($action == 'remove_unconfirmed') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter where status='S'");
}

if ($action == 'remove_unsubscribed') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter where status='U'");
}

if ($action == 'confirm_all') {
    $wpdb->query("update " . $wpdb->prefix . "newsletter set status='C' where status='S'");
}

if ($action == 'remove_all') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter");
}

if ($action == 'list_add') {
    $wpdb->query("update " . $wpdb->prefix . "newsletter set list_" . $options['list'] . "=1");
}

if ($action == 'list_remove') {
    $wpdb->query("update " . $wpdb->prefix . "newsletter set list_" . $options['list'] . "=0");
}

if ($action == 'list_delete') {
    $wpdb->query("delete from " . $wpdb->prefix . "newsletter where list_" . $options['list'] . "<>0");
}

if ($action == 'status') {
    newsletter_set_status($options['subscriber_id'], $options['subscriber_status']);
}

if ($action == 'list_manage') {
    if ($options['list_action'] == 'move') {
        echo 'move';
        $wpdb->query("update " . $wpdb->prefix . 'newsletter set list_' . $options['list_1'] . '=0, list_' . $options['list_2'] . '=1' .
                ' where list_' . $options['list_1'] . '=1');
    }

    if ($options['list_action'] == 'add') {
        $wpdb->query("update " . $wpdb->prefix . 'newsletter set list_' . $options['list_2'] . '=1' .
                ' where list_' . $options['list_1'] . '=1');
    }
}


if ($action == 'search') {
    $list = newsletter_search($options['search_text'], $options['search_status'], $options['search_order'], $options['search_list'], $options['search_link']);
}
else {
    $list = array();
}

$nc = new NewsletterControls($options);
$nc->errors($errors);
$nc->messages($messages);

?>
<script type="text/javascript">
    function newsletter_remove(f, id)
    {
        f.elements["options[subscriber_id]"].value = id;
        f.submit();
    }

    function newsletter_set_status(f, id, status)
    {
        f.elements["options[subscriber_id]"].value = id;
        f.elements["options[subscriber_status]"].value = status;
        f.submit();
    }

    function newsletter_resend(f, id)
    {
        if (!confirm("<?php _e('Resend the subscription confirmation email?', 'newsletter'); ?>")) return;
        f.elements["options[subscriber_id]"].value = id;
        f.submit();
    }

    function newsletter_resend_welcome(f, id)
    {
        if (!confirm("<?php _e('Resend the welcome email?', 'newsletter'); ?>")) return;
        f.elements["options[subscriber_id]"].value = id;
        f.submit();
    }
</script>

<div class="wrap">
    <h2>Gestión de suscripciones</h2>

    <p><a href="admin.php?page=newsletter/users-edit.php&amp;id=0" class="button">Create a new user</a></p>

    <form method="post" action="">    
        <?php $nc->init(); ?>
        
        <table class="widefat" style="width: 300px;">
            <thead><tr><th>Estado</th><th>Total</th><th>Acciones</th></thead>
            <tr>
                <td>Confirmados</td>
                <td>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter where status='C'"); ?>
                </td>
                <td nowrap>
                </td>
            </tr>
            <tr>
                <td>No confirmados</td>
                <td>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter where status='S'"); ?>
                </td>
                <td nowrap>
                    <?php $nc->button_confirm('remove_unconfirmed', 'Borrar todos los confirmados', '¿Seguro que quieres BORRAR a los USUARIOS NO CONFIRMADOS?'); ?>
                    <?php $nc->button_confirm('confirm_all', 'Confirmar todos', '¿Seguro que quieres CONFIRMAR a TODOS los suscriptores?'); ?>
                </td>
            </tr>
            <tr>
                <td>Desuscritos</td>
                <td>
                    <?php echo $wpdb->get_var("select count(*) from " . $wpdb->prefix . "newsletter where status='U'"); ?>
                </td>
                <td>
                    <?php $nc->button_confirm('remove_unsubscribed', 'Borrar todos los desuscritos', '¿Seguro que quieres BORRAR a TODOS los USUARIOS DESUSCRITOS?'); ?>
                </td>
            </tr>
        </table>
    


        <h3>Acciones masivas</h3>
        <table class="form-table">
            <tr>
                <th>Acciones generales</th>
                <td>
                    <?php $nc->button_confirm('remove_all', 'Borrar todos', '¿Seguro que quieres BORRAR a TODOS los USUARIOS?'); ?>
                </td>
            </tr>
            <tr>
                <th>Listas</th>
                <td>
                    Lista <?php $nc->select('list', $lists); ?>:
                    <?php $nc->button_confirm('list_add', 'Añadir a todos los usuarios a esta lista', 'Confirmar acción'); ?>
                    <?php $nc->button_confirm('list_remove', 'Cancela a todos los usuarios de esta lista', 'Confirmar acción'); ?>
                    <?php $nc->button_confirm('list_delete', 'BORRA a los suscriptores de la lista DE LA BASE DE DATOS', 'Confirmar acción. Recuerda que vas a borrar TODOS LOS USUARIOS DE ESTA LISTA DE LA BASE DE DATOS'); ?>
                    <br /><br />
                    <?php $nc->select('list_action', array('move'=>'Mover', 'add'=>'Añadir')); ?>
                    todos los suscriptores de la lista <?php $nc->select('list_1', $lists); ?>
                    a la lista <?php $nc->select('list_2', $lists); ?>
                    <?php $nc->button_confirm('list_manage', 'Cambiar', 'Confirmar acción'); ?>
                </td>
            </tr>
        </table>
    </form>

    <form id="channel" method="post" action="">
        <?php $nc->init(); ?>
        <input type="hidden" name="options[subscriber_id]"/>
        <input type="hidden" name="options[subscriber_status]"/>

        <h3>Buscar</h3>
        <table class="form-table">
            <tr valign="top">
                <td>
                    texto: <?php $nc->text('search_text', 50); ?> (nombre parcial, correo electrónico, ...)<br />
                    <?php $nc->select('search_status', array(''=>'Cualquier estado', 'C'=>'Confirmado', 'S'=>'No confirmado', 'B'=>'Correo no responde')); ?>
                    <?php $nc->select('search_order', array('id'=>'Ordenar por ID', 'email'=>'Ordenar por email', 'name'=>'Ordenar por nombre')); ?>
                    <?php $nc->select('search_list', $lists, 'Cualquiera'); ?>
                    <?php $nc->button('search', 'Buscar'); ?>

                    <div class="hints">
                    Si no seleccionas filtro se mostrarán todas. Máx 100 resultados. Usa el panel de exportación para ver todos los suscriptores.
                    </div>
                </td>
            </tr>
        </table>

        <h3>Resultados de la búsqueda</h3>

<?php if (empty($list)) { ?>
<p>No hay resultados (o todavía no has buscado nada)</p>
<?php } ?>


<?php if (!empty($list)) { ?>

<table class="widefat">
    <thead>
<tr>
    <th>ID</th>
    <th><?php _e('Email', 'newsletter'); ?>/<?php _e('Nombre', 'newsletter'); ?></th>
    <th><?php _e('Estado', 'newsletter'); ?></th>
    <th>Listas</th>
    <th><?php _e('Acciones', 'newsletter'); ?></th>
    <th><?php _e('Perfil', 'newsletter'); ?></th>
    <?php if ($options['search_clicks'] == 1) { ?>
    <th><?php _e('Clicks', 'newsletter'); ?></th>
    <?php } ?>
</tr>
    </thead>
    <?php foreach($list as $s) { ?>
<tr class="<?php echo ($i++%2==0)?'alternate':''; ?>">
<td><?php echo $s->id; ?></td>
<td>
    <a href="admin.php?page=newsletter/users-edit.php&amp;id=<?php echo $s->id; ?>"><?php echo $s->email; ?><br /><?php echo $s->name; ?> <?php echo $s->surname; ?></a>
</td>
<td><small>
        <?php
        switch ($s->status) {
            case 'S': echo 'NO CONFIRMADO'; break;
            case 'C': echo 'CONFIRMADO'; break;
            case 'U': echo 'DESUSCRITO'; break;
        }
        ?>
</small></td>

<td>
    <small>
        <?php
        for ($i=1; $i<=NEWSLETTER_LIST_MAX; $i++) {
            $l = 'list_' . $i;
            if ($s->$l == 1) echo $lists['' . $i] . '<br />';
        }
        ?>
    </small>
</td>

<td>
    <?php $nc->button('remove', 'Borrar', 'newsletter_remove(this.form,' . $s->id . ')'); ?>
    <?php $nc->button('status', 'Confirmar', 'newsletter_set_status(this.form,' . $s->id . ',\'C\')'); ?>
    <?php $nc->button('status', 'No confirmar', 'newsletter_set_status(this.form,' . $s->id . ',\'S\')'); ?>
    <?php $nc->button('resend', 'Reenviar confirmación', 'newsletter_resend(this.form,' . $s->id . ')'); ?>
    <?php $nc->button('resend_welcome', 'Reenviar bienvenida', 'newsletter_resend_welcome(this.form,' . $s->id . ')'); ?>
</td>
<td><small>
        fecha: <?php echo $s->created; ?><br />
        <?php
        $query = $wpdb->prepare("select name,value from " . $wpdb->prefix . "newsletter_profiles where newsletter_id=%d", $s->id);
        $profile = $wpdb->get_results($query);
        foreach ($profile as $field) {
            echo htmlspecialchars($field->name) . ': ' . htmlspecialchars($field->value) . '<br />';
        }
        echo 'Token: ' . $s->token;
?>
</small></td>

</tr>
<?php } ?>
</table>
<?php } ?>
    </form>
</div>
