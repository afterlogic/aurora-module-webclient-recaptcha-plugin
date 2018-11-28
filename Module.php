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
			$mSubscriptionResult = ['ErrorCode' => Enums\ErrorCodes::CaptchaError];
			return true;
		}
		$sPrivateKey = $this->getConfig('PrivateKey', '');
		$oRecaptcha = new \ReCaptcha\ReCaptcha($sPrivateKey);
		$oResponse = $oRecaptcha->verify($aArgs['RecaptchaWebclientPluginToken']);
		if (!$oResponse->isSuccess())
		{
			$mSubscriptionResult = ['Error' => $oResponse->getErrorCodes()];
			return true;
		}
	}
}
