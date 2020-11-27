function loadVideo(partBtn) {
  if (partBtn) {
    var partNo = $(partBtn).attr('part');

    $('#selected').attr('onclick', 'loadVideo(this)');
    $('#selected').removeAttr('id');

    $(partBtn).removeAttr('onclick');
    $(partBtn).attr('id', 'selected');
  } else {
    var partNo = 1
  }
  
  let videoAttributes = isAndroid
    ? `ontouchstart='videoOntouchstart()' ontouchmove='videoOntouchmove()' ontouchend='videoOntouchend()' ontouchcancel='videoOntouchcancel()' oncanplay='videoOncanplay(this)'`
    : `onloadedmetadata='this.play()' controls`
  $('#display').html(
    `<video ${videoAttributes}>` +
    `<source src='/media/vids/${id}_${partNo}.mp4'>` +
    `</video>`
  )
}

let x1 = null                                                      
let y1 = null
let directionalCoefficient = undefined
let isSeeking = false
let timeToMoveTo = null
let videoOnclickTimeout = undefined
let videoOnclickLocked = undefined

function videoOntouchstart() {
  event.preventDefault()
  x1 = event.touches[0].clientX
  y1 = event.touches[0].clientY
}

function videoOntouchmove() {
  event.preventDefault()
  controlVideoUI('show')

  /** If was already registered as vertical swipe */
  if (directionalCoefficient === 'vertical') {
    return
  }
  
  /** If was already registered as horizontal swipe */
  if (directionalCoefficient) {
    timeToMoveTo = Math.max(timeToMoveTo + directionalCoefficient, 1)
    $('#time-info-span').html(getFormattedTime(timeToMoveTo))
    $('#time-slider').val(timeToMoveTo)
    return
  }

  /** Acknowledge that the touch was at least moved */
  directionalCoefficient = false

  let x2 = event.touches[0].clientX                                    
  let y2 = event.touches[0].clientY

  let xDiff = x2 - x1
  let yDiff = Math.abs(y2 - y1)

  let isVertical = yDiff > Math.abs(xDiff)
  if (isVertical) {
    let minMovementThreshold = 100
    if (yDiff >= minMovementThreshold) {
      directionalCoefficient = 'vertical'
    }
  } else {
    let minMovementThreshold = 20
    if (Math.abs(xDiff) >= minMovementThreshold) {
      isSeeking = true
      timeToMoveTo = Math.floor($('video').get(0).currentTime)

      let isRight = xDiff > 0
      directionalCoefficient = isRight ? 1 : -1
    }
  }
}

function videoOntouchend() {
  event.preventDefault()
  const vid = $('video').get(0)

  /** Vertical swipe */
  if (directionalCoefficient === 'vertical') {
    controlVideo('pause')
  }

  /** Tapping */
  else if (directionalCoefficient === undefined) {
    videoOnclick()
  }

  /** Fast forwarding */
  else if (timeToMoveTo) {
    vid.currentTime = timeToMoveTo
  }

  videoOntouchcancel()
}

function videoOntouchcancel() {
  x1 = null
  y1 = null
  directionalCoefficient = undefined
  isSeeking = false
  timeToMoveTo = null
}

function videoOnclick() {

  function lockVideoOnclick() {
    clearTimeout(videoOnclickLocked)
    videoOnclickLocked = setTimeout(function() {
      videoOnclickLocked = undefined
    }, 500)
  }
  
  /** Ignore all continuous clicks after successful double click */
  if (videoOnclickLocked) {
    lockVideoOnclick()
    return
  }

  /** Successful double click */
  if (videoOnclickTimeout) {
    clearTimeout(videoOnclickTimeout)
    videoOnclickTimeout = undefined
    lockVideoOnclick()
    controlVideo('toggle')
    return
  }

  /** Register as single click if second click does not come quickly */
  videoOnclickTimeout = setTimeout(function() {
    controlVideoUI('toggle')
    videoOnclickTimeout = undefined
  }, 200)
}

function controlVideo(action) {
  const display = $('#display').get(0)
  const vid = $('video').get(0)
  switch (action) {
    case 'play':
      display.requestFullscreen()
      vid.play()
      break
    case 'pause':
      document.exitFullscreen()
      vid.pause()
      break
    case 'toggle':
      let isFullscreen = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen
      let nextAction = isFullscreen ? 'pause' : 'play'
      controlVideo(nextAction)
  }
}

function videoOncanplay(vid) {
  let duration = $(vid).get(0).duration
  let time = getFormattedTime(duration)

  $('#display').append(
    `<div id='time-info' class='controls'><span id='time-info-span'>00:00:00</span> / ${time}</div>` +
    `<input id='time-slider' class='controls' type='range' ` +
    `oninput='sliderOninput($(this))' ` +
    `onchange='sliderOnchange($(this))'` +
    `min='0' max='${Math.floor(duration)}' value='0'>`
  )

  $(vid).removeAttr('oncanplay')
  controlVideo('play')

  /** Sync video play to fullscreen if out of sync */
  setInterval(() => {
    const isFullscreen = document.fullScreen || document.mozFullScreen || document.webkitIsFullScreen
    const vid = $('video').get(0)
  
    if (isFullscreen && vid.paused) {
      vid.play()
    } else if (!isFullscreen && !vid.paused) {
      vid.pause()
    }
  }, 200);
  
  setInterval(updateControls, 400)
}

function getFormattedTime(duration) {
  let hour = ("0" + Math.floor(duration / 3600)).slice(-2)
  let mins = ("0" + Math.floor(duration % 3600 / 60)).slice(-2)
  let secs = ("0" + Math.ceil(duration % 3600 % 60)).slice(-2)
  return `${hour}:${mins}:${secs}`
}

function updateControls() {
  let vid = $('video').get(0)
  if (!vid || vid.paused || isSeeking) return

  let time = vid.currentTime
  $('#time-info-span').html(getFormattedTime(time))
  $('#time-slider').val(Math.floor(time))
}

let showControlsTimeout = null
function controlVideoUI(action) {
  switch(action) {
    case 'show':
      $('#display').addClass('active')
      if (showControlsTimeout) clearInterval(showControlsTimeout)
      showControlsTimeout = setTimeout(() => {
        controlVideoUI('hide')
      }, 4000)
      break
    case 'hide':
      $('#display').removeClass('active')
      if (showControlsTimeout) {
        clearInterval(showControlsTimeout)
        showControlsTimeout = null
      }
      break
    case 'toggle':
      let nextAction = showControlsTimeout ? 'hide' : 'show'
      controlVideoUI(nextAction)
  }
}

function sliderOninput(slider) {
  let time = slider.val()
  $('#time-info-span').html(getFormattedTime(time))
  isSeeking = true
}

function sliderOnchange(slider) {
  let time = slider.val()
  let vid = $('video').get(0)
  vid.currentTime = time
  isSeeking = false
}

