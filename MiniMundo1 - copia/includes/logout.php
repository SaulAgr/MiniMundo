<?php
session_start();
session_unset();
session_destroy();
header("Location: ../public.php?mensaje=Sesión%20cerrada%20correctamente");
exit;
?>