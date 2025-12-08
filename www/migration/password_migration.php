<?php
/**
 * Script de Migração de Senhas para bcrypt
 * 
 * Este script migra todas as senhas do sistema antigo (XOR/AES) para bcrypt
 * IMPORTANTE: Execute este script apenas uma vez e em horário de baixo tráfego
 * 
 * @author Sistema de Segurança E-Prepag
 * @version 1.0
 * @date 2025-01-27
 */

// Configurações
set_time_limit(0); // Remove limite de tempo
ini_set('memory_limit', '512M'); // Aumenta limite de memória

// Inclui arquivos necessários
$raiz_do_projeto = dirname(__FILE__) . "/../";
require_once $raiz_do_projeto . "db/connect.php";
require_once $raiz_do_projeto . "class/classEncryption.php";
require_once $raiz_do_projeto . "class/classSecureEncryption.php";

class PasswordMigration {
    
    private $pdo;
    private $legacyEncryption;
    private $secureEncryption;
    private $logFile;
    private $raiz_do_projeto;
    
    public function __construct() {
        global $raiz_do_projeto;
        // Conexão com banco
        $this->pdo = new PDO("pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_BANCO, DB_USER, DB_PASS);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Instâncias das classes de criptografia
        $this->legacyEncryption = new Encryption();
        $this->secureEncryption = new SecureEncryption();
        
        // Arquivo de log
        $this->raiz_do_projeto = $raiz_do_projeto;
        $this->logFile = $this->raiz_do_projeto . "arquivos_gerados/logs/password_migration_" . date('Y-m-d_H-i-s') . ".log";
        
        $this->log("=== INÍCIO DA MIGRAÇÃO DE SENHAS ===");
        $this->log("Data/Hora: " . date('Y-m-d H:i:s'));
    }
    
    /**
     * Executa a migração completa
     */
    public function executeMigration() {
        try {
            $this->log("Iniciando migração de senhas...");
            
            // Migra usuários PDV
            $this->migrateUsersTable();
            
            // Migra operadores
            $this->migrateOperatorsTable();
            
            // Migra usuários backoffice
            $this->migrateBackofficeUsers();
            
            $this->log("=== MIGRAÇÃO CONCLUÍDA COM SUCESSO ===");
            
        } catch (Exception $e) {
            $this->log("ERRO CRÍTICO: " . $e->getMessage());
            $this->log("Stack trace: " . $e->getTraceAsString());
            throw $e;
        }
    }
    
    /**
     * Migra tabela dist_usuarios_games (usuários PDV)
     */
    private function migrateUsersTable() {
        $this->log("--- Migrando tabela dist_usuarios_games ---");
        
        // Primeiro, adiciona coluna para marcar migração se não existir
        $this->addMigrationColumn('dist_usuarios_games', 'ug_senha_migrated');
        
        // Busca usuários que ainda não foram migrados
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
        
        $this->log("Encontrados {$total} usuários para migrar");
        
        foreach ($users as $user) {
            try {
                // Tenta descriptografar a senha antiga para verificar se está válida
                $decryptedPassword = $this->legacyEncryption->decrypt($user['ug_senha']);
                
                if (!empty($decryptedPassword)) {
                    // Gera novo hash bcrypt
                    $newHash = $this->secureEncryption->hashPassword($decryptedPassword);
                    
                    // Atualiza no banco
                    $updateSql = "UPDATE dist_usuarios_games 
                                  SET ug_senha = :new_hash, ug_senha_migrated = true 
                                  WHERE ug_id = :user_id";
                    
                    $updateStmt = $this->pdo->prepare($updateSql);
                    $updateStmt->execute([
                        ':new_hash' => $newHash,
                        ':user_id' => $user['ug_id']
                    ]);
                    
                    $migrated++;
                    $this->log("Usuário {$user['ug_login']} (ID: {$user['ug_id']}) migrado com sucesso");
                } else {
                    $this->log("AVISO: Senha vazia para usuário {$user['ug_login']} (ID: {$user['ug_id']})");
                    $errors++;
                }
                
            } catch (Exception $e) {
                $this->log("ERRO ao migrar usuário {$user['ug_login']} (ID: {$user['ug_id']}): " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->log("Migração dist_usuarios_games concluída: {$migrated} migrados, {$errors} erros");
    }
    
    /**
     * Migra tabela dist_usuarios_games_operador (operadores)
     */
    private function migrateOperatorsTable() {
        $this->log("--- Migrando tabela dist_usuarios_games_operador ---");
        
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
        
        $this->log("Encontrados {$total} operadores para migrar");
        
        foreach ($operators as $operator) {
            try {
                $decryptedPassword = $this->legacyEncryption->decrypt($operator['ugo_senha']);
                
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
                    $this->log("Operador {$operator['ugo_login']} (ID: {$operator['ugo_id']}) migrado com sucesso");
                } else {
                    $this->log("AVISO: Senha vazia para operador {$operator['ugo_login']} (ID: {$operator['ugo_id']})");
                    $errors++;
                }
                
            } catch (Exception $e) {
                $this->log("ERRO ao migrar operador {$operator['ugo_login']} (ID: {$operator['ugo_id']}): " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->log("Migração dist_usuarios_games_operador concluída: {$migrated} migrados, {$errors} erros");
    }
    
    /**
     * Migra tabela usuarios (backoffice)
     */
    private function migrateBackofficeUsers() {
        $this->log("--- Migrando tabela usuarios (backoffice) ---");
        
        // Verifica se a tabela existe
        $checkTable = "SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_schema = 'public' 
            AND table_name = 'usuarios'
        )";
        
        $stmt = $this->pdo->prepare($checkTable);
        $stmt->execute();
        $tableExists = $stmt->fetchColumn();
        
        if (!$tableExists) {
            $this->log("Tabela 'usuarios' não encontrada, pulando migração do backoffice");
            return;
        }
        
        // Adiciona coluna para marcar migração se não existir
        $this->addMigrationColumn('usuarios', 'shn_password_migrated');
        
        $sql = "SELECT shn_id, shn_login, shn_password 
                FROM usuarios 
                WHERE (shn_password_migrated IS NULL OR shn_password_migrated = false)
                AND shn_password IS NOT NULL 
                AND shn_password != ''
                ORDER BY shn_id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $backofficeUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $total = count($backofficeUsers);
        $migrated = 0;
        $errors = 0;
        
        $this->log("Encontrados {$total} usuários do backoffice para migrar");
        
        foreach ($backofficeUsers as $user) {
            try {
                // Para backoffice, pode usar AES ou base64, tenta ambos
                $decryptedPassword = $this->tryDecryptBackofficePassword($user['shn_password']);
                
                if (!empty($decryptedPassword)) {
                    $newHash = $this->secureEncryption->hashPassword($decryptedPassword);
                    
                    $updateSql = "UPDATE usuarios 
                                  SET shn_password = :new_hash, shn_password_migrated = true 
                                  WHERE shn_id = :user_id";
                    
                    $updateStmt = $this->pdo->prepare($updateSql);
                    $updateStmt->execute([
                        ':new_hash' => $newHash,
                        ':user_id' => $user['shn_id']
                    ]);
                    
                    $migrated++;
                    $this->log("Usuário backoffice {$user['shn_login']} (ID: {$user['shn_id']}) migrado com sucesso");
                } else {
                    $this->log("AVISO: Não foi possível descriptografar senha para usuário {$user['shn_login']} (ID: {$user['shn_id']})");
                    $errors++;
                }
                
            } catch (Exception $e) {
                $this->log("ERRO ao migrar usuário backoffice {$user['shn_login']} (ID: {$user['shn_id']}): " . $e->getMessage());
                $errors++;
            }
        }
        
        $this->log("Migração usuarios (backoffice) concluída: {$migrated} migrados, {$errors} erros");
    }
    
    /**
     * Tenta descriptografar senha do backoffice (pode ser AES ou base64)
     */
    private function tryDecryptBackofficePassword($encryptedPassword) {
        try {
            // Tenta AES primeiro
            if (class_exists('AES')) {
                $aes = new AES("chave_padrao_aes"); // Chave padrão para descriptografia
                $decrypted = $aes->decrypt(base64_decode($encryptedPassword));
                if (!empty($decrypted)) {
                    return $decrypted;
                }
            }
        } catch (Exception $e) {
            // Ignora erro e tenta próximo método
        }
        
        try {
            // Tenta descriptografia simples com Encryption
            return $this->legacyEncryption->decrypt($encryptedPassword);
        } catch (Exception $e) {
            // Ignora erro
        }
        
        try {
            // Tenta base64 decode simples
            $decoded = base64_decode($encryptedPassword);
            if ($decoded !== false && !empty($decoded)) {
                return $decoded;
            }
        } catch (Exception $e) {
            // Ignora erro
        }
        
        return null;
    }
    
    /**
     * Adiciona coluna de controle de migração se não existir
     */
    private function addMigrationColumn($tableName, $columnName) {
        try {
            $checkColumn = "SELECT column_name 
                           FROM information_schema.columns 
                           WHERE table_name = :table_name 
                           AND column_name = :column_name";
            
            $stmt = $this->pdo->prepare($checkColumn);
            $stmt->execute([
                ':table_name' => $tableName,
                ':column_name' => $columnName
            ]);
            
            if (!$stmt->fetchColumn()) {
                $alterSql = "ALTER TABLE {$tableName} ADD COLUMN {$columnName} BOOLEAN DEFAULT FALSE";
                $this->pdo->exec($alterSql);
                $this->log("Coluna {$columnName} adicionada à tabela {$tableName}");
            }
            
        } catch (Exception $e) {
            $this->log("ERRO ao adicionar coluna {$columnName} à tabela {$tableName}: " . $e->getMessage());
        }
    }
    
    /**
     * Grava log no arquivo
     */
    private function log($message) {
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;
        
        file_put_contents($this->logFile, $logMessage, FILE_APPEND | LOCK_EX);
        echo $logMessage; // Também exibe no console
    }
    
    /**
     * Gera relatório de migração
     */
    public function generateReport() {
        $this->log("--- RELATÓRIO DE MIGRAÇÃO ---");
        
        // Conta usuários migrados
        $tables = [
            'dist_usuarios_games' => 'ug_senha_migrated',
            'dist_usuarios_games_operador' => 'ugo_senha_migrated',
            'usuarios' => 'shn_password_migrated'
        ];
        
        foreach ($tables as $table => $column) {
            try {
                $sql = "SELECT 
                           COUNT(*) as total,
                           COUNT(CASE WHEN {$column} = true THEN 1 END) as migrated
                        FROM {$table}";
                
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $this->log("Tabela {$table}: {$result['migrated']}/{$result['total']} migrados");
                
            } catch (Exception $e) {
                $this->log("Erro ao gerar relatório para {$table}: " . $e->getMessage());
            }
        }
    }
}

// Execução do script
if (php_sapi_name() === 'cli') {
    echo "=== MIGRAÇÃO DE SENHAS PARA BCRYPT ===\n";
    echo "ATENÇÃO: Este processo irá alterar todas as senhas no banco de dados.\n";
    echo "Certifique-se de ter um backup antes de continuar.\n";
    echo "Deseja continuar? (s/N): ";
    
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    fclose($handle);
    
    if (trim(strtolower($line)) !== 's') {
        echo "Migração cancelada.\n";
        exit(0);
    }
    
    try {
        $migration = new PasswordMigration();
        $migration->executeMigration();
        $migration->generateReport();
        
        echo "\n=== MIGRAÇÃO CONCLUÍDA ===\n";
        echo "Verifique o arquivo de log para detalhes.\n";
        
    } catch (Exception $e) {
        echo "ERRO CRÍTICO: " . $e->getMessage() . "\n";
        exit(1);
    }
} else {
    echo "Este script deve ser executado via linha de comando (CLI).\n";
    echo "Exemplo: php password_migration.php\n";
}
?>