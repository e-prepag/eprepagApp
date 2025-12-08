<?php
/**
 * Classe de Criptografia Segura usando bcrypt
 * Substitui a classe Encryption insegura que usa XOR
 * 
 * @author Sistema de Segurança E-Prepag
 * @version 1.0
 * @date 2025-01-27
 */

class SecureEncryption {
    
    /**
     * Custo do bcrypt (entre 10-15, recomendado 12)
     * Maior custo = mais seguro mas mais lento
     */
    private $cost = 12;
    
    /**
     * Instância da classe Encryption antiga para migração
     */
    private $legacyEncryption;
    
    public function __construct() {
        // Carrega a classe antiga para migração de senhas
        if (class_exists('Encryption')) {
            $this->legacyEncryption = new Encryption();
        }
    }
    
    /**
     * Criptografa uma senha usando bcrypt
     * 
     * @param string $password Senha em texto plano
     * @return string Hash bcrypt da senha
     */
    public function hashPassword($password) {
        if (empty($password)) {
            throw new InvalidArgumentException('Senha não pode estar vazia');
        }
        
        $options = [
            'cost' => $this->cost,
        ];
        
        return password_hash($password, PASSWORD_BCRYPT, $options);
    }
    
    /**
     * Verifica se uma senha corresponde ao hash
     * 
     * @param string $password Senha em texto plano
     * @param string $hash Hash armazenado no banco
     * @return boolean True se a senha estiver correta
     */
    public function verifyPassword($password, $hash) {
        if (empty($password) || empty($hash)) {
            return false;
        }
        
        // Verifica se é um hash bcrypt válido
        if ($this->isBcryptHash($hash)) {
            return password_verify($password, $hash);
        }
        
        // Se não for bcrypt, tenta verificar com o sistema antigo
        return $this->verifyLegacyPassword($password, $hash);
    }
    
    /**
     * Verifica se uma senha precisa ser re-hash (migração ou upgrade de custo)
     * 
     * @param string $hash Hash atual
     * @return boolean True se precisar re-hash
     */
    public function needsRehash($hash) {
        if (!$this->isBcryptHash($hash)) {
            // Se não é bcrypt, precisa migrar
            return true;
        }
        
        $options = [
            'cost' => $this->cost,
        ];
        
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);
    }
    
    /**
     * Verifica se um hash é do tipo bcrypt
     * 
     * @param string $hash Hash para verificar
     * @return boolean True se for bcrypt
     */
    private function isBcryptHash($hash) {
        return (strlen($hash) === 60 && substr($hash, 0, 4) === '$2y$');
    }
    
    /**
     * Verifica senha usando o sistema antigo (para migração)
     * 
     * @param string $password Senha em texto plano
     * @param string $legacyHash Hash do sistema antigo
     * @return boolean True se a senha estiver correta
     */
    private function verifyLegacyPassword($password, $legacyHash) {
        if (!$this->legacyEncryption) {
            return false;
        }
        
        try {
            // Tenta criptografar a senha com o sistema antigo e comparar
            $encryptedPassword = $this->legacyEncryption->encrypt($password);
            return ($encryptedPassword === $legacyHash);
        } catch (Exception $e) {
            // Log do erro se necessário
            error_log("Erro na verificação de senha legacy: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Migra uma senha do sistema antigo para bcrypt
     * 
     * @param string $password Senha em texto plano
     * @param string $legacyHash Hash do sistema antigo
     * @return string|false Novo hash bcrypt ou false se falhar
     */
    public function migrateLegacyPassword($password, $legacyHash) {
        // Primeiro verifica se a senha está correta no sistema antigo
        if ($this->verifyLegacyPassword($password, $legacyHash)) {
            // Se estiver correta, gera novo hash bcrypt
            return $this->hashPassword($password);
        }
        
        return false;
    }
    
    /**
     * Gera uma senha aleatória segura
     * 
     * @param int $length Comprimento da senha (mínimo 12)
     * @return string Senha aleatória
     */
    public function generateSecurePassword($length = 16) {
        if ($length < 12) {
            $length = 12;
        }
        
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?';
        $password = '';
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        
        return $password;
    }
    
    /**
     * Método de compatibilidade com a classe antiga
     * DEPRECATED: Use hashPassword() em vez disso
     * 
     * @param string $password
     * @return string
     * @deprecated
     */
    public function encrypt($password) {
        trigger_error('Método encrypt() está deprecated. Use hashPassword()', E_USER_DEPRECATED);
        return $this->hashPassword($password);
    }
    
    /**
     * Método de compatibilidade - NÃO IMPLEMENTADO
     * bcrypt é unidirecional, não pode descriptografar
     * 
     * @param string $hash
     * @throws Exception
     * @deprecated
     */
    public function decrypt($hash) {
        throw new Exception('Decrypt não é possível com bcrypt. Use verifyPassword() para verificar senhas.');
    }
    
    /**
     * Define o custo do bcrypt
     * 
     * @param int $cost Custo entre 10-15
     */
    public function setCost($cost) {
        if ($cost < 10 || $cost > 15) {
            throw new InvalidArgumentException('Custo deve estar entre 10 e 15');
        }
        
        $this->cost = $cost;
    }
    
    /**
     * Obtém o custo atual
     * 
     * @return int
     */
    public function getCost() {
        return $this->cost;
    }
}
?>