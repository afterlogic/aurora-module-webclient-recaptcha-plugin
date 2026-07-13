<template>
  <div v-show="visible" ref="containerRef" class="recaptcha-place-cover" />
</template>

<script>
import { ref, onMounted, onBeforeUnmount, watch, nextTick } from 'vue'
import VueCookies from 'vue-cookies'

import settings from '../settings'
import recaptchaApi from '../recaptcha-api'

const CALLBACK_NAME = 'ShowRecaptchaStandardLoginFormMobileWebclient'

function getAuthErrorCount () {
  const value = VueCookies.get('auth-error')
  return value ? parseInt(value, 10) || 0 : 0
}

function loadScript (callbackName) {
  return new Promise((resolve, reject) => {
    if (window.grecaptcha) {
      resolve()
      return
    }

    const scriptUrl = `https://www.google.com/recaptcha/api.js?onload=${callbackName}&render=explicit`
    const existingScript = document.querySelector(`script[src^="https://www.google.com/recaptcha/api.js"]`)
    if (existingScript) {
      existingScript.addEventListener('load', resolve, { once: true })
      existingScript.addEventListener('error', reject, { once: true })
      return
    }

    window[callbackName] = () => {
      resolve()
    }

    const script = document.createElement('script')
    script.src = scriptUrl
    script.async = true
    script.defer = true
    script.onerror = reject
    document.head.appendChild(script)
  })
}

export default {
  name: 'RecaptchaWidget',

  setup () {
    const containerRef = ref(null)
    const visible = ref(false)
    let widgetId = null

    const shouldShowRecaptcha = () => {
      if (!settings.getSetting('showRecaptcha') || !settings.getSetting('publicKey')) {
        return false
      }

      const limitCount = settings.getSetting('limitCount')
      return getAuthErrorCount() >= limitCount
    }

    const updateVisibility = () => {
      visible.value = shouldShowRecaptcha()
    }

    const renderWidget = () => {
      if (!window.grecaptcha || !containerRef.value || widgetId !== null) {
        return
      }

      widgetId = window.grecaptcha.render(containerRef.value, {
        sitekey: settings.getSetting('publicKey') || 'wrong-key',
      })
    }

    const initRecaptcha = async () => {
      if (!settings.getSetting('showRecaptcha') || !settings.getSetting('publicKey')) {
        return
      }

      updateVisibility()

      if (!window.grecaptcha) {
        await loadScript(CALLBACK_NAME)
      }

      if (visible.value) {
        await nextTick()
        renderWidget()
      }
    }

    const getTokenParameters = () => {
      if (!shouldShowRecaptcha()) {
        return null
      }

      if (!window.grecaptcha || widgetId === null) {
        return false
      }

      const token = window.grecaptcha.getResponse(widgetId)
      if (!token) {
        return false
      }

      return {
        [settings.getSetting('moduleName') + 'Token']: token,
      }
    }

    const reset = () => {
      if (typeof window.grecaptcha !== 'undefined' && widgetId !== null) {
        window.grecaptcha.reset(widgetId)
      }
    }

    const onLoginFailed = async () => {
      const wasVisible = visible.value
      updateVisibility()

      if (!visible.value) {
        return
      }

      if (!window.grecaptcha) {
        await initRecaptcha()
        return
      }

      if (wasVisible) {
        reset()
      } else {
        await nextTick()
        renderWidget()
      }
    }

    watch(visible, async (isVisible) => {
      if (isVisible && window.grecaptcha && widgetId === null) {
        await nextTick()
        renderWidget()
      }
    })

    onMounted(async () => {
      recaptchaApi.setApi({ getTokenParameters, reset, onLoginFailed })
      await initRecaptcha()
    })

    onBeforeUnmount(() => {
      delete window[CALLBACK_NAME]
      recaptchaApi.setApi(null)
    })

    return {
      containerRef,
      visible,
    }
  },
}
</script>

<style lang="scss" scoped>
.recaptcha-place-cover {
  margin: 0 auto;
  display: table;
}
</style>
