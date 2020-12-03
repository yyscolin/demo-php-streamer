const callbacks = {
  resize: []
}

$(window).resize(() => {
  for (let callback of callbacks.resize) {
    callback()
  }
})
