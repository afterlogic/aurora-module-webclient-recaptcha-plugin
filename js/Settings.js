'use strict';

var
	ko = require('knockout'),
	_ = require('underscore'),

	Types = require('%PathToCoreWebclientModule%/js/utils/Types.js')
;

module.exports = {
	ModuleName: 'RecaptchaWebclientPlugin',
	PublicKey: '',

	/**
	 * Initializes settings from AppData object sections.
	 * 
	 * @param {Object} oAppData Object contained modules settings.
	 */
	init: function (oAppData)
	{
		var oAppDataSection = oAppData[this.ModuleName] || {};
		
		if (!_.isEmpty(oAppDataSection))
		{
			this.PublicKey = Types.pString(oAppDataSection.PublicKey, this.PublicKey);
		}
	}
};
