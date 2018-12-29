<?php
/**
 * This code is licensed under AGPLv3 license or AfterLogic Software License
 * if commercial version of the product was purchased.
 * For full statements of the licenses see LICENSE-AFTERLOGIC and LICENSE-AGPL3 files.
 */

namespace Aurora\Modules\RecaptchaWebclientPlugin;

/**
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 * @license https://afterlogic.com/products/common-licensing AfterLogic Software License
 * @copyright Copyright (c) 2018, Afterlogic Corp.
 *
 * @package Modules
 */
class Module extends \Aurora\System\Module\AbstractModule
{
	public function init()
	{
		$this->aErrors = [
			Enums\ErrorCodes::RecaptchaVerificationError	=> $this->i18N('ERROR_RECAPTCHA_VERIFICATION_DID_NOT_COMPLETE'),
			Enums\ErrorCodes::RecaptchaUnknownError		=> $this->i18N('ERROR_UNKNOWN_RECAPTCHA_ERROR'),
		];

		\Aurora\System\EventEmitter::getInstance()->onArray(
			[
				['StandardLoginFormWebclient::Login::before', [$this, 'onLogin'], 90],
				['MailSignup::Signup::before', [$this, 'onLogin'], 90]
			]
		);
	}

	/**
	 * Obtains list of module settings for authenticated user.
	 * @return array
	 */
	public function GetSettings()
	{
		\Aurora\System\Api::checkUserRoleIsAtLeast(\Aurora\System\Enums\UserRole::Anonymous);

		return [
			'PublicKey' => $this->getConfig('PublicKey', '')
		];
	}

	public function onLogin($aArgs, &$mResult, &$mSubscriptionResult)
	{
		if (!isset($aArgs['RecaptchaWebclientPluginToken']) || empty($aArgs['RecaptchaWebclientPluginToken']))
		{
			$mSubscriptionResult = [
				'Error' => [
					'Code'		=> Enums\ErrorCodes::RecaptchaVerificationError,
					'ModuleName'	=> $this->GetName(),
					'Override'		=> true
				]
			];
			return true;
		}
		$sPrivateKey = $this->getConfig('PrivateKey', '');
		$oRecaptcha = new \ReCaptcha\ReCaptcha($sPrivateKey);
		$oResponse = $oRecaptcha->verify($aArgs['RecaptchaWebclientPluginToken']);
		if (!$oResponse->isSuccess())
		{
			\Aurora\System\Api::Log("RECAPTCHA error: " . implode(', ', $oResponse->getErrorCodes()));
			$mSubscriptionResult = [
				'Error' => [
					'Code'		=> Enums\ErrorCodes::RecaptchaUnknownError,
					'ModuleName'	=> $this->GetName(),
					'Override'		=> true
				]
			];
			return true;
		}
	}
}
