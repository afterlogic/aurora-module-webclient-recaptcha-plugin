'use strict';

var
	_ = require('underscore'),
	ko = require('knockout'),
	$ = require('jquery'),

	Settings = require('modules/%ModuleName%/js/Settings.js')
;

/**
 * @constructor
 */
function CMainView()
{
	this.bShown = false;
	if (!window.grecaptcha)
	{
		window.ShowRecaptcha = this.ShowRecaptcha;
		$.getScript('https://www.google.com/recaptcha/api.js?onload=ShowRecaptcha&render=explicit');
	}
	else
	{
		this.ShowRecaptcha();
	}
}

CMainView.prototype.ViewTemplate = '%ModuleName%_MainView';

CMainView.prototype.ShowRecaptcha = function ()
{
	if (window.grecaptcha)
	{
		if (!this.bShown)
		{
			var
				sKey = Settings ? Settings.PublicKey : ''
			;

			if (sKey === '')
			{
				sKey = "wrong-key";
			}
			window.grecaptcha.render('recaptcha-place', {
				'sitekey': sKey
			});
		}
		else
		{
			window.grecaptcha.reset();
		}

		this.bShown = true;
	}
};

CMainView.prototype.getParametersForSubmit = function ()
{
	var
		sParamName = Settings.ModuleName + "Token",
		oResult = {}
	;

	oResult[sParamName] = window.grecaptcha.getResponse();
	return oResult;
};

module.exports = new CMainView();
