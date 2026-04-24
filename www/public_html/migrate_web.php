<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . "/../includes/load_dotenv.php";

if (isset($_POST["migration_dev_key"]) && $_POST["migration_dev_key"] == getenv("migration_dev_key")) {
    require __DIR__ . "/../migration/migrate.php";
} else {
    http_response_code(404);
    ?>
    <!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
    <html>

    <head>
        <title>404 Not Found</title>
    </head>

    <body>
        <h1>Not Found</h1>
        <p>The requested URL was not found on this server.</p>
    </body>

    </html>
    <?php
}
