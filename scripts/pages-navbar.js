let middleNavWidthCoefficient
eventCallbacks.load.push(resizeNavbarMiddle)
eventCallbacks.load.push(adjustNavCss)
eventCallbacks.resize.push(resizeNavbarMiddle)
eventCallbacks.resize.push(adjustNavCss)

let pageNavAjax = null
function openPage(button) {
  const pageNo = parseInt($(button).attr('data-page'))
  const clearPageNavAjax = function() {
    if (pageNavAjax && pageNavAjax.readyState != 4) {
      pageNavAjax.abort()
      pageNavAjax = null
    }
  }
  const callpageNavAjax = function() {
    const url = `/api/page-items.php`
    const data = {type, 'page-no': pageNo, 'items-count': itemsPerPage}
    const onSuccess = function(response) {
      let boxClassName = type == 'vid' ? '.poster' : '.star-box'
      let boxLinks = $(boxClassName)
      for (let i = 0; i < boxLinks.length; i++) {
        let boxLink = boxLinks[i]
        if (response.length > i) {
          $(boxLink).show()
          if (type == 'vid') {
            let vid = response[i]
            $(boxLink).find('.title').html(vid.name)
            $(boxLink).find('a').attr('href', `/vid/${vid.id}`)
            $(boxLink).find('img').attr('src', vid.img)
          } else {
            let star = response[i]
            $(boxLink).find('a').attr('href', `/star/${star.id}`)
            $(boxLink).find('img').attr('src', star.img)
            $(boxLink).find('.name').html(star.name)
            $(boxLink).find('.dob').html(star.dob)
            $(boxLink).find('.vid-count>span').html(star.count)
          }
        } else {
          $(boxLink).hide()
        }
      }
    }
    pageNavAjax = $.get(url, data, onSuccess)
  }

  if ($(button).hasClass('selected')) return

  clearPageNavAjax()
  callpageNavAjax()
  adjustNavCss(pageNo)
  
  /** Switch active selection */
  $('.nav-item.selected').removeClass('selected')
  $(button).addClass('selected')
}
    
function adjustNavCss(pageNo) {
  if (
    !$('.nav-item-box.middle').length ||
    $('.nav-item-box.middle .nav-item').length <= middleNavWidthCoefficient
  ) return

  let slideAnimatationTime = 300
  if (!pageNo) {
    slideAnimatationTime = 0
    pageNo = $('.nav-item.selected').attr('data-page')
  }
  
  const lowerPageLimit = (pageNo - 2) - ((middleNavWidthCoefficient - 1) / 2)
  const upperPageLimit = (noOfPages - 2) - (middleNavWidthCoefficient - 1)
  const targetPage = Math.min(Math.max(lowerPageLimit, 0), upperPageLimit)
  const navItemWidth = $('.nav-item').outerWidth()
  const scrollLeft = targetPage * navItemWidth

  $('.nav-item-box.middle').animate({scrollLeft}, slideAnimatationTime)
}

function resizeNavbarMiddle() {
  if (!$('.nav-item-box.middle').length) return

  if (window.innerWidth >= 1200) {
    var maxNavWidthCoefficient = 9
  } else if (window.innerWidth >= 1000) {
    var maxNavWidthCoefficient = 7
  } else if (window.innerWidth >= 800) {
    var maxNavWidthCoefficient = 5
  } else {
    var maxNavWidthCoefficient = 3
  }

  let middleNavItemsCount = $('.nav-item-box.middle .nav-item').length
  if (middleNavItemsCount > 3 && middleNavItemsCount % 2 == 0) middleNavItemsCount-- //assume odd number if >3
  middleNavWidthCoefficient = Math.min(middleNavItemsCount, maxNavWidthCoefficient)

  let navItemWidth = $('.nav-item').outerWidth()
  $('.nav-item-box.middle').width(navItemWidth * middleNavWidthCoefficient)
}
