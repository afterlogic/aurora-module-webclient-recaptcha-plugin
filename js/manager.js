'use strict';

module.exports = function (oAppData) {
	var
		_ = require('underscore'),

		App = require('%PathToCoreWebclientModule%/js/App.js'),
		Settings = require('modules/%ModuleName%/js/Settings.js')
	;

	Settings.init(oAppData);

	if (App.getUserRole() === Enums.UserRole.Anonymous)
	{
		return {
			start: function (ModulesManager)
			{
				var CMainView = require('modules/%ModuleName%/js/views/CMainView.js');
				ModulesManager.run('StandardLoginFormWebclient', 'registerExtentionComponent', [new CMainView()]);
				ModulesManager.run('MailSignup', 'registerExtentionComponent', [new CMainView()]);
			}

		};
	}
	return null;
};
