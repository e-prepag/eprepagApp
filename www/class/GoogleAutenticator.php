<?php
declare(strict_types=1);

use PragmaRX\Google2FA\Google2FA;

require_once '/www/vendor/autoload.php';

class classGoogleAutenticator
{
    /**
     * @var Google2FA
     */
    protected Google2FA $google2fa;

    public function __construct(?Google2FA $google2fa = null)
    {
        $this->google2fa = $google2fa ?? new Google2FA();
    }

    /**
     *
     * @param int $secretLength
     * @return string
     */
    public function createSecret(int $secretLength = 16): string
    {
        return $this->google2fa->generateSecretKey($secretLength);
    }

    /**
     *
     * @param string   $secret
     * @param int|null $timeSlice
     * @return string
     */
    public function getCode(string $secret, ?int $timeSlice = null): string
    {
        if ($timeSlice === null) {
            return $this->google2fa->getCurrentOtp($secret);
        }

        return $this->google2fa->oathTotp($secret, $timeSlice);
    }

    /**
     *
     * @param string   $secret
     * @param string   $code
     * @param int      $discrepancy
     * @param int|null $currentTimeSlice
     * @return bool
     */
    public function verifyCode(
        string $secret,
        string $code,
        int $discrepancy = 1,
        ?int $currentTimeSlice = null
    ): bool {
        return $this->google2fa->verifyKey(
            $secret,
            $code,
            $discrepancy,
            $currentTimeSlice
        );
    }

    /**
     *
     * Retorna uma URL otpauth compatível com apps autenticadores.
     * Se você quiser manter o comportamento antigo de "URL do Google Chart",
     * veja o método getLegacyQRCodeGoogleUrl() abaixo.
     *
     * @param string      $name
     * @param string      $secret
     * @param string|null $title
     * @param array       $params
     * @return string
     */
    public function getQRCodeGoogleUrl(
        string $name,
        string $secret,
        ?string $title = null,
        array $params = []
    ): string {
        $company = $title ?? '';

        return $this->google2fa->getQRCodeUrl(
            $company,
            $name,
            $secret
        );
    }

    /**
     * Se você precisa preservar o retorno antigo do PHPGangsta,
     * que montava uma URL do Google Chart API, use este método.
     *
     * @param string      $name
     * @param string      $secret
     * @param string|null $title
     * @param array       $params
     * @return string
     */
    public function getLegacyQRCodeGoogleUrl(
        string $name,
        string $secret,
        ?string $title = null,
        array $params = []
    ): string {
        $width  = isset($params['width']) ? (int) $params['width'] : 200;
        $height = isset($params['height']) ? (int) $params['height'] : 200;
        $level  = isset($params['level']) ? (string) $params['level'] : 'M';

        $issuer = $title ?? '';
        $label  = $issuer !== '' ? $issuer . ':' . $name : $name;

        $otpUrl = sprintf(
            'otpauth://totp/%s?secret=%s%s',
            rawurlencode($label),
            rawurlencode($secret),
            $issuer !== '' ? '&issuer=' . rawurlencode($issuer) : ''
        );

        return sprintf(
            'https://chart.googleapis.com/chart?chs=%dx%d&chld=%s|0&cht=qr&chl=%s',
            $width,
            $height,
            rawurlencode($level),
            rawurlencode($otpUrl)
        );
    }

    /**
     * Opcional: expõe o objeto interno caso você precise usar
     * algum método nativo do pragmarx/google2fa.
     *
     * @return Google2FA
     */
    public function getProvider(): Google2FA
    {
        return $this->google2fa;
    }
}