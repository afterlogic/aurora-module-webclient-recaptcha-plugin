import types from 'src/utils/types'

class RecaptchaSettings {
  constructor (appData) {
    const recaptchaData = types.pObject(appData.RecaptchaWebclientPlugin)
    this.moduleName = 'RecaptchaWebclientPlugin'
    this.publicKey = types.pString(recaptchaData.PublicKey, '')
    this.limitCount = types.pInt(recaptchaData.LimitCount, 0)
    this.showRecaptcha = types.pBool(recaptchaData.ShowRecaptcha, false)
  }
}

let settings = null

export default {
  init (appData) {
    settings = new RecaptchaSettings(appData)
  },

  getSetting (settingName) {
    return settings ? settings[settingName] : null
  },
}
