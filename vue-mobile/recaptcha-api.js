let widgetApi = null

export default {
  setApi (api) {
    widgetApi = api
  },

  getTokenParameters () {
    if (!widgetApi) {
      return false
    }
    return widgetApi.getTokenParameters()
  },

  reset () {
    if (widgetApi) {
      widgetApi.reset()
    }
  },

  onLoginFailed () {
    if (widgetApi?.onLoginFailed) {
      widgetApi.onLoginFailed()
    }
  },
}
