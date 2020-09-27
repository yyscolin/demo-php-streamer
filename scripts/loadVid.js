$('#display').hover(() => {
  $('#display').addClass('active')
}, () => {
  $('#display').removeClass('active')
})

// $('#display').on('touchstart', () => {
//   $('#display').addClass('active')
  // $('#display').toggleClass('active')
// })
  
function loadVid(e) {
  let part;
  if (e) {
    part = e.getAttribute('part');

    selected.setAttribute('onclick', 'loadVid(this)');
    selected.removeAttribute('id');

    e.removeAttribute('onclick');
    e.setAttribute('id', 'selected');
  } else
    part = 1;
  
  $('#display').html(
    `<video ondblclick='toggleVideoPlay()' ` +
    `oncanplay='videoOncanplay($(this))'` +
    `><source src='/media/vids/${id}_${part}.mp4'>` +
    '</video>'
  )
}

let x1 = null                                                      
let y1 = null
let directionalCoefficient = null
let isSeeking = false
let timeToMoveTo = null

let firstClick = false
let clickTimeout

function videoOntouchstart() {
  event.preventDefault()
  isSeeking = true
  x1 = event.touches[0].clientX
  y1 = event.touches[0].clientY
}

function videoOntouchmove() {
  event.preventDefault()
  
  let isFirstMove = directionalCoefficient ? false : true
  if (!isFirstMove) {
    timeToMoveTo += directionalCoefficient
    $('#time-info-span').html(getFormattedTime(timeToMoveTo))
    $('#time-slider').val(Math.floor(timeToMoveTo))
    return
  }

  timeToMoveTo = $('video').get(0).currentTime
  let x2 = event.touches[0].clientX                                    
  let y2 = event.touches[0].clientY
  let xDiff = x2 - x1
  let yDiff = y1 - y2

  let isVertical = Math.abs(yDiff) > Math.abs(xDiff)
  if (isVertical)
    return toggleVideoPlay()

  let isLeft = xDiff < 0
  if (!isLeft)
    directionalCoefficient = 1
  else
    directionalCoefficient = -1
}

function videoOntouchend() {
  event.preventDefault()
  if (timeToMoveTo)
    $('video').get(0).currentTime = timeToMoveTo
  videoOntouchcancel()
}

function videoOntouchcancel() {
  x1 = null
  y1 = null
  directionalCoefficient = null
  isSeeking = false
  timeToMoveTo = null
}

function toggleVideoPlay() {
  let vid = $('video').get(0)
  if (vid.paused == true) {
    vid.play()
    $('#play-button').html('|  |')
    $('#display').get(0).requestFullscreen()

    if (isMobile) {
      $('video').removeAttr('dblclick')
      $('video').on('touchstart', videoOntouchstart)
      $('video').on('touchmove', videoOntouchmove)
      $('video').on('touchend', videoOntouchend)
      $('video').on('touchcancel', videoOntouchcancel)
    }
  } else {
    vid.pause()
    $('#play-button').html('➤')
    document.exitFullscreen()

    if (isMobile) {
      $('video').attr('dblclick', 'toggleVideoPlay()')
      $('video').off('touchstart')
      $('video').off('touchmove')
      $('video').off('touchend')
      $('video').off('touchcancel')
    }
  }
}

function videoOncanplay(vid) {
  let duration = vid.get(0).duration
  let time = getFormattedTime(duration)

  $('#display').append(
    `<div id='time-info' class='controls'><span id='time-info-span'>00:00:00</span> / ${time}</div>` +
    `<input id='time-slider' class='controls' type='range' ` +
    `oninput='sliderOninput($(this))' ` +
    `onchange='sliderOnchange($(this))'` +
    `min='0' max='${Math.floor(duration)}' value='0'>`
  )

  vid.removeAttr('oncanplay')
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

setInterval(updateControls, 400)