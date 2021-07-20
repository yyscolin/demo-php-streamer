const eventCallbacks = {
  load: [],
  resize: []
}

const menu2 = {
  current: null,
  set: () => null,
  reset: () => {
    menu2.current = null
    menu2.set([])
    $(`#banner`).removeClass(`secondary`)
  }
}

eventCallbacks.load.push(() => $(`body`).click(({target}) => {
  target = $(target)
  do {
    if (target.attr(`id`) == `banner`) return
    target = target.parent()
  } while (target.prop(`tagName`) != `HTML`)
  menu2.reset()
}))

$(window).on(`load`, () => {
  for (let callback of eventCallbacks.load) callback()
})

$(window).on(`resize`, () => {
  for (let callback of eventCallbacks.resize) callback()
})

const setLanguange = language => {
  document.cookie = `language=${language}; SameSite=Lax;`
  location.reload()
}
