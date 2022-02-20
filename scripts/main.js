const eventCallbacks = {
  load: [],
  resize: [],
  hashchange: [],
}

const menu2 = {
  current: null,
  set: () => null,
  reset: () => {
    menu2.current = null
    menu2.style = {}
    menu2.set([])
    $(`#banner`).removeClass(`secondary`)
  }
}

eventCallbacks.load.push(() => $(`body`).click(({target}) => {
  target = $(target)
  do {
    if (target.attr(`id`) == `banner`) return
    target = target.parent()
  } while (target.length && target.prop(`tagName`) != `HTML`)
  menu2.reset()
}))

for (const eventName in eventCallbacks) {
  $(window).on(eventName, () => {
    for (let callback of eventCallbacks[eventName]) callback()
  })
}

const setLanguange = language => {
  document.cookie = `language=${language}; SameSite=Lax;`
  location.reload()
}
