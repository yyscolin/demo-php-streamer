const eventCallbacks = {
  resize: [],
  hashchange: [],
}

for (const eventName in eventCallbacks) {
  $(window).on(eventName, () => {
    for (let callback of eventCallbacks[eventName]) callback()
  })
}

const setLanguange = language => {
  document.cookie = `language=${language}; SameSite=Lax;`
  location.reload()
}

function pageOnload() {
  const cookies = document.cookie

  /** Set the current language of the <select> element */
  const currentLang = cookies.match(/language=.+;/)[0].split(/[=;]/)[1]
  $(`#lang-select`).val(currentLang)
}

pageOnload()
