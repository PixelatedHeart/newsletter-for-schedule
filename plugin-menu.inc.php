<?php

$level = $this->options_main['editor'] ? 7 : 10;

add_menu_page('Newsletter', 'Newsletter', $level, 'newsletter/intro.php', '', '');
//add_submenu_page('newsletter/intro.php', 'User Guide', 'User Guide', $level, 'newsletter/intro.php');

add_submenu_page('newsletter/intro.php', 'Main Configuration', 'Configuración General', $level, 'newsletter/main.php');
add_submenu_page('newsletter/intro.php', 'Subscription Process', 'Proceso de suscripción', $level, 'newsletter/options.php');
add_submenu_page('newsletter/intro.php', 'Subscription Form', 'Listas de usuario', $level, 'newsletter/profile.php');

add_submenu_page('newsletter/intro.php', 'Emails', 'Newsletters', $level, 'newsletter/emails.php');
add_submenu_page('newsletter/emails.php', 'Email Edit', 'Editar Newsletters', $level, 'newsletter/emails-edit.php');

add_submenu_page('newsletter/intro.php', 'Subscribers', 'Suscriptores', $level, 'newsletter/users.php');
add_submenu_page('newsletter/users.php', 'Subscribers Edit', 'Editar Suscriptores', $level, 'newsletter/users-edit.php');

add_submenu_page('newsletter/intro.php', 'Import/Export', 'Importar / Exportar', $level, 'newsletter/import.php');
?>