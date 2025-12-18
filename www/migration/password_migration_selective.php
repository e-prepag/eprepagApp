<?php
/**
 * Script de Migração Seletiva de Senhas para bcrypt
 * 
 * Este script migra apenas usuários dos tipos:
 * - SYS: Usuários do backoffice/sistema (tabela usuarios)
 * - PDV: Usuários PDV e operadores (tabelas dist_usuarios_games e dist_usuarios_games_operador)
 * 
 * Autor: Sistema de Segurança E-Prepag
 * Data: 2024
 */

require_once __DIR__ . '/../db/connect.php';
require_once __DIR__ . '/../class/classSecureEncryption.php';
require_once "/www/includes/gamer/chave.php";
require_once __DIR__ . '/../includes/gamer/AES.class.php';
require_once __DIR__ . '/../class/classEncryption.php';

class PasswordMigrationSelective {
    private $pdo;
    private $secureEncryption;
    private $logFile;
    
    public function __construct() {
        try {
            // Conecta ao banco de dados
            $this->pdo = new PDO(
                "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_BANCO,
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
            
            $this->secureEncryption = new SecureEncryption();
            $this->logFile = __DIR__ . '/migration_selective_' . date('Y-m-d_H-i-s') . '.log';
            
            $this->log("=== INICIANDO MIGRAÇÃO SELETIVA BCRYPT ===");
            $this->log("Tipos de usuários: SYS (backoffice) e PDV");
            
        } catch (Exception $e) {
            die("Erro ao conectar ao banco: " . $e->getMessage());
        }
    }
    
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        echo $logMessage;
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    public function migrate() {
        try {
            $this->log("Iniciando migração seletiva...");
            
            // Migra apenas usuários PDV
            //$this->migratePdvUsers();
            //$this->migratePdvOperators();
            
            // Migra apenas usuários SYS (backoffice)
            $this->migrateSysUsers();
            
            $this->log("=== MIGRAÇÃO SELETIVA CONCLUÍDA COM SUCESSO ===");
            
        } catch (Exception $e) {
            $this->log("ERRO CRÍTICO: " . $e->getMessage());
            $this->log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Migra tabela dist_usuarios_games (usuários PDV)
     */
    private function migratePdvUsers() {
        $this->log("--- Migrando usuários PDV (dist_usuarios_games) ---");
        
        // Adiciona coluna para marcar migração se não existir
        $this->addMigrationColumn('dist_usuarios_games', 'ug_senha_migrated');
        
        $sql = "SELECT ug_id, ug_login, ug_senha 
                FROM dist_usuarios_games 
                WHERE (ug_senha_migrated IS NULL OR ug_senha_migrated = false)
                AND ug_senha IS NOT NULL 
                AND ug_senha != ''
                ORDER BY ug_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = count($users);
        $migrated = 0;
        $errors = 0;
        
        $this->log("Encontrados {$total} usuários PDV para migrar");
        
        foreach ($users as $user) {
            try {
                // Tenta descriptografar a senha atual
                $decryptedPassword = $this->tryDecryptPassword($user['ug_senha']);
                
                if (!empty($decryptedPassword)) {
                    $newHash = $this->secureEncryption->hashPassword($decryptedPassword);
                    
                    $updateSql = "UPDATE dist_usuarios_games 
                                  SET ug_senha = :new_hash, ug_senha_migrated = true 
                                  WHERE ug_id = :user_id";
                    
                    $updateStmt = $this->pdo->prepare($updateSql);
                    $updateStmt->execute([
                        ':new_hash' => $newHash,
                        ':user_id' => $user['ug_id']
                    ]);
                    
                    $migrated++;
                    $this->log("Usuário PDV {$user['ug_login']} (ID: {$user['ug_id']}) migrado com sucesso");
                } else {
                    $this->log("AVISO: Não foi possível descriptografar senha para usuário PDV {$user['ug_login']} (ID: {$user['ug_id']})");
                    $errors++;
                }
                
            } catch (Exception $e) {
                $this->log("ERRO ao migrar usuário PDV {$user['ug_login']} (ID: {$user['ug_id']}): " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->log("Migração PDV usuários concluída: {$migrated} migrados, {$errors} erros");
    }
    
    /**
     * Migra tabela dist_usuarios_games_operador (operadores PDV)
     */
    private function migratePdvOperators() {
        $this->log("--- Migrando operadores PDV (dist_usuarios_games_operador) ---");
        
        // Adiciona coluna para marcar migração se não existir
        $this->addMigrationColumn('dist_usuarios_games_operador', 'ugo_senha_migrated');
        
        $sql = "SELECT ugo_id, ugo_login, ugo_senha 
                FROM dist_usuarios_games_operador 
                WHERE (ugo_senha_migrated IS NULL OR ugo_senha_migrated = false)
                AND ugo_senha IS NOT NULL 
                AND ugo_senha != ''
                ORDER BY ugo_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = count($operators);
        $migrated = 0;
        $errors = 0;
        
        $this->log("Encontrados {$total} operadores PDV para migrar");
        
        foreach ($operators as $operator) {
            try {
                // Tenta descriptografar a senha atual
                $decryptedPassword = $this->tryDecryptPassword($operator['ugo_senha']);
                
                if (!empty($decryptedPassword)) {
                    $newHash = $this->secureEncryption->hashPassword($decryptedPassword);
                    
                    $updateSql = "UPDATE dist_usuarios_games_operador 
                                  SET ugo_senha = :new_hash, ugo_senha_migrated = true 
                                  WHERE ugo_id = :operator_id";
                    
                    $updateStmt = $this->pdo->prepare($updateSql);
                    $updateStmt->execute([
                        ':new_hash' => $newHash,
                        ':operator_id' => $operator['ugo_id']
                    ]);
                    
                    $migrated++;
                    $this->log("Operador PDV {$operator['ugo_login']} (ID: {$operator['ugo_id']}) migrado com sucesso");
                } else {
                    $this->log("AVISO: Não foi possível descriptografar senha para operador PDV {$operator['ugo_login']} (ID: {$operator['ugo_id']})");
                    $errors++;
                }
                
            } catch (Exception $e) {
                $this->log("ERRO ao migrar operador PDV {$operator['ugo_login']} (ID: {$operator['ugo_id']}): " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->log("Migração PDV operadores concluída: {$migrated} migrados, {$errors} erros");
    }
    
    /**
     * Migra tabela usuarios (usuários SYS/backoffice)
     */
    private function migrateSysUsers() {
        $this->log("--- Migrando usuários SYS (usuarios - backoffice) ---");
        
        // // Verifica se a tabela existe
        // $checkTable = "SELECT EXISTS (
        //     SELECT FROM information_schema.tables 
        //     WHERE table_schema = 'public' 
        //     AND table_name = 'usuarios'
        // )";
        
        // $stmt = $this->pdo->prepare($checkTable);
        // $stmt->execute();
        // $tableExists = $stmt->fetchColumn();
        
        // if (!$tableExists) {
        //     $this->log("Tabela 'usuarios' não encontrada, pulando migração SYS");
        //     return;
        // }
        
        // Adiciona coluna para marcar migração se não existir
        $this->addMigrationColumn('usuarios', 'shn_password_migrated');
        
        $sql = "SELECT id, shn_login, shn_password 
                FROM usuarios 
                WHERE (shn_password_migrated IS NULL OR shn_password_migrated = false)
                AND shn_password IS NOT NULL 
                AND shn_password != ''
                ORDER BY id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $sysUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = count($sysUsers);
        $migrated = 0;
        $errors = 0;
        
        $this->log("Encontrados {$total} usuários SYS para migrar");
        
        foreach ($sysUsers as $user) {
            try {
                // Para backoffice, pode usar AES ou base64, tenta ambos
                $decryptedPassword = $this->tryDecryptBackofficePassword($user['shn_password']);

                echo $decryptedPassword;
                
                if (!empty($decryptedPassword)) {
                    $newHash = $this->secureEncryption->hashPassword($decryptedPassword);
                    
                    $updateSql = "UPDATE usuarios 
                                  SET shn_password = :new_hash, shn_password_migrated = true 
                                  WHERE id = :user_id";
                    
                    $updateStmt = $this->pdo->prepare($updateSql);
                    $updateStmt->execute([
                        ':new_hash' => $newHash,
                        ':user_id' => $user['id']
                    ]);
                    
                    $migrated++;
                    $this->log("Usuário SYS {$user['shn_login']} (ID: {$user['id']}) migrado com sucesso");
                } else {
                    $this->log("AVISO: Não foi possível descriptografar senha para usuário SYS {$user['shn_login']} (ID: {$user['id']})");
                    $errors++;
                }
                
            } catch (Exception $e) {
                $this->log("ERRO ao migrar usuário SYS {$user['shn_login']} (ID: {$user['id']}): " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->log("Migração SYS usuários concluída: {$migrated} migrados, {$errors} erros");
    }
    
    /**
     * Adiciona coluna de migração se não existir
     */
    private function addMigrationColumn($table, $column) {
        try {
            $checkColumn = "SELECT column_name 
                           FROM information_schema.columns 
                           WHERE table_name = :table AND column_name = :column";
            
            $stmt = $this->pdo->prepare($checkColumn);
            $stmt->execute([':table' => $table, ':column' => $column]);
            
            if (!$stmt->fetch()) {
                $alterSql = "ALTER TABLE {$table} ADD COLUMN {$column} BOOLEAN DEFAULT FALSE";
                $this->pdo->exec($alterSql);
                $this->log("Coluna {$column} adicionada à tabela {$table}");
            }
        } catch (Exception $e) {
            $this->log("Erro ao adicionar coluna {$column} à tabela {$table}: " . $e->getMessage());
        }
    }
    
    /**
     * Tenta descriptografar senha usando diferentes métodos
     */
    private function tryDecryptPassword($encryptedPassword) {

        // Método 2: Encryption class
        try {
            if (class_exists('Encryption')) {
                $encryption = new Encryption();
                $decrypted = $encryption->decrypt($encryptedPassword);
                if (!empty($decrypted) && strlen($decrypted) >= 4) {
                    return $decrypted;
                }
            }
        } catch (Exception $e) {
            // Continua para próximo método
        }
        
        return null;
    }
    
    /**
     * Tenta descriptografar senha do backoffice
     */
    private function tryDecryptBackofficePassword($encryptedPassword) {
        // Método 1: AES (mais comum no backoffice)
        try {
            if (class_exists('AES')) {
                $chave256bits = new Chave();
                $aes = new AES($chave256bits->retornaChavePub());
                echo $chave256bits->retornaChavePub();
                $decrypted = $aes->decrypt(base64_decode($encryptedPassword));
                echo "\n---=== $encryptedPassword ===---\n";
                echo "\n---=== $decrypted ===---\n";
                if (!empty($decrypted) && strlen($decrypted) >= 4) {
                    //echo "---=== $decrypted ===---";
                    return $decrypted;
                }
            }
        } catch (Exception $e) {
            die($e->getMessage());
        }
        
        return null;
    }
    
    /**
     * Verifica se uma string é um hash bcrypt
     */
    private function isBcryptHash($string) {
        return preg_match('/^\$2[ayb]\$[0-9]{2}\$[A-Za-z0-9\.\/]{53}$/', $string);
    }
    
    /**
     * Gera relatório de migração
     */
    public function generateReport() {
        $this->log("=== RELATÓRIO DE MIGRAÇÃO SELETIVA ===");
        
        $tables = [
            'dist_usuarios_games' => 'ug_senha_migrated',
            'dist_usuarios_games_operador' => 'ugo_senha_migrated',
            'usuarios' => 'shn_password_migrated'
        ];
        
        foreach ($tables as $table => $column) {
            try {
                // Verifica se a tabela existe
                $checkTable = "SELECT EXISTS (
                    SELECT FROM information_schema.tables 
                    WHERE table_schema = 'public' 
                    AND table_name = :table
                )";
                
                $stmt = $this->pdo->prepare($checkTable);
                $stmt->execute([':table' => $table]);
                
                if (!$stmt->fetchColumn()) {
                    $this->log("Tabela {$table}: NÃO ENCONTRADA");
                    continue;
                }
                
                // Conta registros migrados
                $sql = "SELECT 
                    COUNT(*) as total,
                    COUNT(CASE WHEN {$column} = true THEN 1 END) as migrated,
                    COUNT(CASE WHEN {$column} IS NULL OR {$column} = false THEN 1 END) as pending
                FROM {$table}";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $this->log("Tabela {$table}:");
                $this->log("  - Total: {$result['total']}");
                $this->log("  - Migrados: {$result['migrated']}");
                $this->log("  - Pendentes: {$result['pending']}");
                
            } catch (Exception $e) {
                $this->log("Erro ao gerar relatório para {$table}: " . $e->getMessage());
            }
        }
    }
}

// Execução do script
if (php_sapi_name() === 'cli') {
    try {
        $migration = new PasswordMigrationSelective();
        $migration->migrate();
        //$migration->generateReport();
        
        echo "\nMigração seletiva concluída com sucesso!\n";
        echo "Verifique o arquivo de log para detalhes.\n";
        
    } catch (Exception $e) {
        echo "ERRO: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "Este script deve ser executado via linha de comando (CLI).\n";
    exit(1);
}
?>