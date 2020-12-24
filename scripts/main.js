const callbacks = {
  load: [],
  resize: []
}

$(window).on('load', () => {
  for (let callback of callbacks.load) {
    callback()
  }
})

$(window).on('resize', () => {
  for (let callback of callbacks.resize) {
    callback()
  }
})
