<?php
/**
 * This code is licensed under AGPLv3 license or Afterlogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\RecaptchaWebclientPlugin;

use Aurora\Modules\RecaptchaWebclientPlugin\Models\AuthFailure;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing Afterlogic Software License
 * @copyright Copyright (c) 2023, Afterlogic Corp.
 *
 * @ignore
 *
 * @property Module $oModule
 */
class Manager extends \Aurora\System\Managers\AbstractManager
{
    protected $recaptchaToken = null;
    protected $allowRecaptchaCheckOnLogin = true;

    /**
     * @param \Aurora\System\Module\AbstractModule $oModule
     */
    public function __construct(\Aurora\System\Module\AbstractModule $oModule = null)
    {
        parent::__construct($oModule);
    }

    public function isRecaptchaEnabledForIP()
    {
        return !in_array(\Aurora\System\Utils::getClientIp(), $this->oModule->oModuleSettings->WhitelistIPs);
    }

    public function memorizeRecaptchaWebclientPluginToken($aArgs)
    {
        if (isset($aArgs['RecaptchaWebclientPluginToken']) && !empty($aArgs['RecaptchaWebclientPluginToken'])) {
            $this->recaptchaToken = $aArgs['RecaptchaWebclientPluginToken'];
        }
    }

    public function disableRecaptchaCheckOnLogin()
    {
        $this->allowRecaptchaCheckOnLogin = false;
    }

    public function needToCheckRecaptchaOnLogin(string $sEmail = '')
    {
        if (!$this->allowRecaptchaCheckOnLogin) {
            return false;
        }

        if (!$this->isRecaptchaEnabledForIP()) {
            return false;
        }

        $authErrorCount = $this->getAuthErrorCount($sEmail);

        if ($authErrorCount >= $this->oModule->oModuleSettings->LimitCount) {
            return true;
        }

        return false;
    }

    public function checkIfRecaptchaError()
    {
        if ($this->recaptchaToken === null) {
            \Aurora\System\Api::Log('RECAPTCHA error: no token');
            return [
                'Error' => [
                    'Code' => Enums\ErrorCodes::RecaptchaVerificationError,
                    'ModuleName' => $this->oModule->GetName(),
                    'Override' => true
                ]
            ];
        }

        $privateKey = $this->oModule->oModuleSettings->PrivateKey;
        $recaptcha = new \ReCaptcha\ReCaptcha($privateKey, $this->getRequestMethod());
        $response = $recaptcha->verify($this->recaptchaToken);
        if (!$response->isSuccess()) {
            \Aurora\System\Api::Log('RECAPTCHA error: ' . implode(', ', $response->getErrorCodes()));
            return [
                'Error' => [
                    'Code' => Enums\ErrorCodes::RecaptchaUnknownError,
                    'ModuleName' => $this->oModule->GetName(),
                    'Override' => true
                ]
            ];
        }

        return false;
    }

    public function clearAuthErrorCount(string $sEmail = '')
    {
        if ($sEmail !== '') {
            AuthFailure::where('Email', $sEmail)
                ->where('IpAddress', \Aurora\System\Utils::getClientIp())
                ->delete();
        }

        $this->syncAuthErrorCookie(0);
    }

    public function incrementAuthErrorCount(string $sEmail = '')
    {
        if ($sEmail === '') {
            return;
        }

        $sIp = \Aurora\System\Utils::getClientIp();
        $this->cleanupExpiredRecords();

        $oRecord = AuthFailure::where('Email', $sEmail)->where('IpAddress', $sIp)->first();
        if (!$oRecord) {
            $oRecord = new AuthFailure();
            $oRecord->Email = $sEmail;
            $oRecord->IpAddress = $sIp;
        }

        $iUserId = \Aurora\System\Api::getUserIdByPublicId($sEmail);
        if ($iUserId) {
            $oRecord->UserId = $iUserId;
        }

        $oRecord->ErrorLoginsCount++;
        $oRecord->Time = time();
        $oRecord->save();

        $this->syncAuthErrorCookie((int) $oRecord->ErrorLoginsCount);
    }

    public function getAuthErrorCount(string $sEmail = ''): int
    {
        $oRecord = $this->getAuthFailureRecord($sEmail);
        return $oRecord ? (int) $oRecord->ErrorLoginsCount : 0;
    }

    private function getAuthFailureLifetimeSeconds(): int
    {
        $iMinutes = (int) $this->oModule->oModuleSettings->AuthFailureLifetimeMinutes;
        $iMinutes = $this->adjustAuthFilureLivetime($iMinutes);

        return $iMinutes * 60;
    }

    private function cleanupExpiredRecords(): void
    {
        AuthFailure::where('Time', '<', time() - $this->getAuthFailureLifetimeSeconds())->delete();
    }

    private function syncAuthErrorCookie(int $iCount): void
    {
        $iMinutes = (int) $this->oModule->oModuleSettings->AuthFailureLifetimeMinutes;
        $iMinutes = $this->adjustAuthFilureLivetime($iMinutes);

        \Aurora\System\Api::setCookie(
            'auth-error',
            $iCount,
            \strtotime('+' . $iMinutes . ' minutes'),
            false
        );
    }

    /**
     * Adjusts the auth failure lifetime in minutes.
     * Ensures that the provided value is at least 1 minute; if less, defaults to 60 minutes.
     *
     * @param int $iMinutes The number of minutes to set as lifetime.
     * @return int Adjusted number of minutes (at least 1, default is 1 if invalid).
     */
    private function adjustAuthFilureLivetime($iMinutes): int
    {
        if ($iMinutes < 1) {
            $iMinutes = 1;
        }

        return $iMinutes;
    }

    private function getAuthFailureRecord(string $sEmail): ?AuthFailure
    {
        $this->cleanupExpiredRecords();

        if ($sEmail === '') {
            return null;
        }

        return AuthFailure::where('Email', $sEmail)
            ->where('IpAddress', \Aurora\System\Utils::getClientIp())
            ->first();
    }

    private function getRequestMethod()
    {
        $sRequestMethod = $this->oModule->oModuleSettings->RequestMethod;
        switch ($sRequestMethod) {
            case Enums\RequestMethods::CurlPost:
                return new \ReCaptcha\RequestMethod\CurlPost();
            case Enums\RequestMethods::Post:
                return new \ReCaptcha\RequestMethod\Post();
            case Enums\RequestMethods::SocketPost:
            default:
                return new \ReCaptcha\RequestMethod\SocketPost();
        }
    }
}
