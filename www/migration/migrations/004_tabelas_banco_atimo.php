<?php

return function (PDO $pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS AT_ApisUsoCpf (
                    id SERIAL PRIMARY KEY,
                    retorno TEXT NOT NULL,
                    chamada TEXT NOT NULL,
                    data TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                );
                    ");

    $pdo->exec("CREATE TABLE IF NOT EXISTS AT_ApisUso (
                    id BIGSERIAL PRIMARY KEY,
                    token VARCHAR(100) NOT NULL,
                    datahora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    ip VARCHAR(100) NOT NULL,
                    endpoint VARCHAR(100) NOT NULL,
                    dados TEXT NOT NULL,
                    endereco VARCHAR(150) NOT NULL,
                    retorno TEXT NOT NULL
                );
                    ");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_access_tokens (
                    access_token VARCHAR(40) PRIMARY KEY,  -- System generated access token. Use appropriate collation for case-sensitive tokens.
                    client_id VARCHAR(80),                 -- OAUTH_CLIENTS.CLIENT_ID
                    user_id VARCHAR(80),                   -- OAUTH_USERS.USER_ID
                    expires TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  -- When the token becomes invalid
                    scope VARCHAR(4000)                    -- Space-delimited list of scopes token can access
                );
                ");

    $pdo->exec("CREATE OR REPLACE FUNCTION update_expires_column()
                RETURNS TRIGGER AS $$
                BEGIN
                    NEW.expires := CURRENT_TIMESTAMP;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;");

    $pdo->exec("CREATE TRIGGER trigger_update_expires
                BEFORE UPDATE ON oauth_access_tokens
                FOR EACH ROW
                EXECUTE FUNCTION update_expires_column();
                ");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_authorization_codes (
                    authorization_code VARCHAR(40) PRIMARY KEY,  -- System generated authorization code
                    client_id VARCHAR(80),                        -- OAUTH_CLIENTS.CLIENT_ID
                    user_id VARCHAR(80),                          -- OAUTH_USERS.USER_ID
                    redirect_uri VARCHAR(2000) NOT NULL,         -- URI to redirect user after authorization
                    expires TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,  -- When the code becomes invalid
                    scope VARCHAR(4000),                          -- Space-delimited list scopes code can request
                    id_token VARCHAR(1000)                        -- JSON web token used for OpenID Connect
                );");

    $pdo->exec("CREATE TRIGGER trigger_update_expires
                BEFORE UPDATE ON oauth_authorization_codes
                FOR EACH ROW
                EXECUTE FUNCTION update_expires_column();");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_clients (
                    client_id VARCHAR(80) PRIMARY KEY,      -- A unique client identifier
                    client_secret VARCHAR(80),              -- Used to secure Client Credentials Grant
                    redirect_uri VARCHAR(2000),             -- Redirect URI used for Authorization Grant
                    grant_types VARCHAR(80),                -- Space-delimited list of permitted grant types
                    scope VARCHAR(4000),                    -- Space-delimited list of permitted scopes
                    user_id VARCHAR(80)                     -- OAUTH_USERS.USER_ID
                );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_jti (
                    jti VARCHAR(2000) PRIMARY KEY,          -- JSON Token Identifier
                    subject VARCHAR(80),                    -- Related user/client
                    issuer VARCHAR(80),                     -- JWT issuer
                    audience VARCHAR(80) DEFAULT NULL,
                    expires TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP  -- When the token becomes invalid
                );");

    $pdo->exec("CREATE TRIGGER trigger_update_expires
                BEFORE UPDATE ON oauth_jti
                FOR EACH ROW
                EXECUTE FUNCTION update_expires_column();");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_jwt (
                    client_id VARCHAR(80) PRIMARY KEY,     -- OAUTH_CLIENTS.CLIENT_ID
                    subject VARCHAR(80),                    -- Related user/client
                    public_key TEXT                         -- PEM encoded public key
                );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_public_keys (
                    client_id VARCHAR(80) PRIMARY KEY,     -- OAUTH_CLIENTS.CLIENT_ID
                    public_key TEXT,                        -- PEM encoded public key
                    private_key TEXT,                       -- PEM encoded private key
                    encryption_algorithm VARCHAR(100) DEFAULT 'RS256'        -- Algorithm used for signing/encryption
                );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_refresh_tokens (
                    refresh_token VARCHAR(40) PRIMARY KEY,  -- System generated refresh token
                    client_id VARCHAR(80),                   -- OAUTH_CLIENTS.CLIENT_ID
                    user_id VARCHAR(80),                     -- OAUTH_USERS.USER_ID
                    expires TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, -- When the token becomes invalid
                    scope VARCHAR(4000)                      -- Space-delimited list of scopes token can access
                );");

    $pdo->exec("CREATE TRIGGER trigger_update_expires
                BEFORE UPDATE ON oauth_refresh_tokens
                FOR EACH ROW
                EXECUTE FUNCTION update_expires_column();");
                
    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_scopes (
                    scope VARCHAR(80) PRIMARY KEY,          -- Name of the scope
                    is_default BOOLEAN                      -- If this scope is granted by default
                );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS oauth_users (
                    username VARCHAR(80) PRIMARY KEY,      -- Username / user ID
                    password VARCHAR(255),                 -- Hashed password
                    first_name VARCHAR(80),
                    last_name VARCHAR(80),
                    email VARCHAR(80),
                    email_verified BOOLEAN,                -- tinyint(1) ? BOOLEAN
                    scope VARCHAR(4000)                    -- Space-delimited list of scopes
                );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS \"user\" (
                    id_new BIGSERIAL PRIMARY KEY,
                    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    accountType VARCHAR(2) CHECK (accountType IN ('PF', 'PJ')),
                    accountCreationStatus VARCHAR(20) NOT NULL DEFAULT 'ON_APPROVAL'
                        CHECK (accountCreationStatus IN ('ON_APPROVAL','COMPLETED','SUSPENDED','DISABLED','MANUAL_APPROVAL')),
                    documentId VARCHAR(255),
                    cpf VARCHAR(255),
                    status VARCHAR(20) DEFAULT 'ON_APPROVAL'
                        CHECK (status IN ('ON_APPROVAL','COMPLETED','SUSPENDED','DISABLED','MANUAL_APPROVAL','ON_EDIT')),
                    fullName VARCHAR(255),
                    preferredName VARCHAR(255),
                    id_eprepag INTEGER,
                    auto_saldo smallint DEFAULT 0
                );");

    $pdo->exec("CREATE TABLE IF NOT EXISTS situacao_chave_api (
                    id_situacao SERIAL PRIMARY KEY,
                    cod_situacao INTEGER NOT NULL, -- 1 = ativo; 2 = inativo
                    cod_usuario INTEGER NOT NULL,
                    criado VARCHAR(70)
                );");
};