<?php

return function (PDO $pdo) {
    $pdo->exec("CREATE INDEX CONCURRENTLY idx_dugsl_user_date
  					ON dist_usuarios_games_saldo_log (dugsl_ug_id, dugsl_data_inclusao);");
                    
    $pdo->exec("CREATE INDEX CONCURRENTLY idx_ugsl_user_date
  					ON usuarios_games_saldo_log (ugsl_ug_id, ugsl_data_inclusao);");
};