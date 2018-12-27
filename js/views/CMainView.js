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
function CMainView(sModuleName)
{
	this.sModuleName = sModuleName;
	this.bShown = false;
	this.recaptchaPlace = ko.observable(null);
	this.recaptchaPlace.subscribe(function () {
		this.ShowRecaptcha();
	}, this);
	if (!window.grecaptcha)
	{
		window['ShowRecaptcha' + sModuleName] = this.ShowRecaptcha.bind(this);
		$.getScript('https://www.google.com/recaptcha/api.js?onload=ShowRecaptcha' + sModuleName + '&render=explicit');
	}
	else
	{
		this.ShowRecaptcha();
	}
}

CMainView.prototype.ViewTemplate = '%ModuleName%_MainView';

CMainView.prototype.ShowRecaptcha = function ()
{
	if (window.grecaptcha && this.recaptchaPlace())
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
			this.recaptchaPlace().append('<div id="recaptcha-place-' + this.sModuleName + '"></div>');
			this.widgetId = window.grecaptcha.render('recaptcha-place-' + this.sModuleName, {
				'sitekey': sKey
			});
		}
		else
		{
			window.grecaptcha.reset(this.widgetId);
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

	oResult[sParamName] = window.grecaptcha.getResponse(this.widgetId);
	return oResult;
};

module.exports = CMainView;
