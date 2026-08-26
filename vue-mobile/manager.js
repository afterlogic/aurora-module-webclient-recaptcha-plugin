import _ from 'lodash'

import eventBus from 'src/event-bus'

import settings from './settings'
import recaptchaApi from './recaptcha-api'

const LOGIN_MODULE_NAMES = [
  'StandardLoginFormMobileWebclient',
  'MailLoginFormMobileWebclient',
]

const _getBeforeButtonsComponents = (params) => {
  if (!settings.getSetting('showRecaptcha') || !settings.getSetting('publicKey')) {
    return
  }

  if (!_.isArray(params.beforeButtonsComponents)) {
    params.beforeButtonsComponents = []
  }

  params.beforeButtonsComponents.push(() => import('./components/RecaptchaWidget'))
}

const _populateFormSubmitParameters = (params) => {
  if (!LOGIN_MODULE_NAMES.includes(params.Module) || !params.Parameters) {
    return
  }

  if (!settings.getSetting('showRecaptcha') || !settings.getSetting('publicKey')) {
    return
  }

  const tokenParameters = recaptchaApi.getTokenParameters()
  if (tokenParameters === false) {
    params.Reject = true
  } else if (tokenParameters) {
    _.extend(params.Parameters, tokenParameters)
  }
}

const _onLoginFailed = (params) => {
  if (LOGIN_MODULE_NAMES.includes(params.ModuleName)) {
    recaptchaApi.onLoginFailed()
  }
}

export default {
  moduleName: 'RecaptchaWebclientPlugin',

  requiredModules: [],

  init (appData) {
    settings.init(appData)
  },

  initSubscriptions () {
    LOGIN_MODULE_NAMES.forEach((moduleName) => {
      const eventName = `${moduleName}::GetBeforeButtonsComponents`
      eventBus.$off(eventName, _getBeforeButtonsComponents)
      eventBus.$on(eventName, _getBeforeButtonsComponents)
    })

    eventBus.$off('AnonymousUserForm::PopulateFormSubmitParameters', _populateFormSubmitParameters)
    eventBus.$on('AnonymousUserForm::PopulateFormSubmitParameters', _populateFormSubmitParameters)

    eventBus.$off('AnonymousUserForm::LoginFailed', _onLoginFailed)
    eventBus.$on('AnonymousUserForm::LoginFailed', _onLoginFailed)
  },
}
