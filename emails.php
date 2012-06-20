<?php
@include_once 'commons.php';
$nc = new NewsletterControls();

$emails = $wpdb->get_results("select * from " . $wpdb->prefix . "newsletter_emails where type='email' order by id desc");

if ($nc->is_action('send')) {
    $newsletter->hook_newsletter();
}
?>

<div class="wrap">

<h2>Newsletters</h2>

<?php include dirname(__FILE__) . '/header.php'; ?>

<form method="post" action="admin.php?page=newsletter/emails.php">
    <?php $nc->init(); ?>

<p><a href="admin.php?page=newsletter/emails-edit.php&amp;id=0" class="button">Crear nueva newsletter</a></p>
<p>
    El sistema activará el siguiente envío en: <?php echo wp_next_scheduled('newsletter')-time(); ?> segundos
    <?php $nc->button('send', 'Enviar ahora'); ?> <br />Pulsar en "enviar ahora" pone en cola el envío en servidor AHORA y no cuando especifica el contador. El envío puede no ser inmediato. <br />
    Un envío no comenzará hasta que el anterior haya terminado.
</p>

    <table class="widefat">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>&nbsp;</th>
            </tr>
        </thead>

        <tbody>
            <?php foreach ($emails as &$email) { ?>
            <tr>
                <td><?php echo $email->id; ?></td>
                <td><a href="admin.php?page=newsletter/emails-edit.php&amp;id=<?php echo $email->id; ?>"><?php echo htmlspecialchars($email->subject); ?></a></td>
                <td><?php echo $email->date; ?></td>
                <td>
                    <?php echo $email->status; ?>
                    (<?php echo $email->sent; ?>/<?php echo $email->total; ?>)
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</form>
</div>
