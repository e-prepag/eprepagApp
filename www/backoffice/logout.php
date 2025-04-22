<?php
session_start();

if(!empty($_SESSION["iduser_bko"]))
        session_destroy();

header("Location: /");
?>
