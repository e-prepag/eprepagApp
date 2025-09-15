<?php

return function (PDO $pdo) {
    $pdo->exec(" ALTER TABLE public.dist_usuarios_games ADD ug_estilo text NULL;
                            ALTER TABLE public.dist_usuarios_games ALTER COLUMN ug_estilo SET STORAGE EXTENDED;
                            
                            ALTER TABLE public.dist_usuarios_games ADD ug_logo bytea NULL;
                            ALTER TABLE public.dist_usuarios_games ALTER COLUMN ug_logo SET STORAGE EXTENDED;");
};
