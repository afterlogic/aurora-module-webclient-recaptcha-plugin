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
	this.recaptchaPlace = ko.observable(null);
	this.recaptchaPlace.subscribe(function () {
		this.ShowRecaptcha();
	}, this);
	this.sId = Math.round(Math.random() * 1000).toString();
	if (!window.grecaptcha)
	{
		window['ShowRecaptcha' + this.sId] = this.ShowRecaptcha.bind(this);
		$.getScript('https://www.google.com/recaptcha/api.js?onload=ShowRecaptcha' + this.sId + '&render=explicit');
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

			this.recaptchaPlace().append('<div id="recaptcha-place' + this.sId+ '"></div>');
			window.grecaptcha.render('recaptcha-place' + this.sId, {
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

module.exports = CMainView;
