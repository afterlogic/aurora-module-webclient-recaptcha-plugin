'use strict';

module.exports = function (oAppData) {
	var
		_ = require('underscore'),

		App = require('%PathToCoreWebclientModule%/js/App.js'),
		TextUtils = require('%PathToCoreWebclientModule%/js/utils/Text.js'),
		Settings = require('modules/%ModuleName%/js/Settings.js')
	;

	Settings.init(oAppData);

	return {
		start: function (ModulesManager)
		{
			ModulesManager.run('StandardLoginFormWebclient', 'registerComposeExtentionComponent', [require('modules/%ModuleName%/js/views/MainView.js')]);
		}

	};

	return null;
};
