let middleNavWidthCoefficient
let pageNavAjax = null

eventCallbacks.load.push(resizeNavbarMiddle)
eventCallbacks.load.push(adjustNavCss)
eventCallbacks.load.push(openPage)
eventCallbacks.resize.push(resizeNavbarMiddle)
eventCallbacks.resize.push(adjustNavCss)
eventCallbacks.hashchange.push(openPage)

function getPageNumber() {
  const windowHash = window.location.hash
  if (!windowHash) return 1
  const hashValue = windowHash.substring(1)
  if (hashValue != parseInt(hashValue)) return 1
  if (!$(`[data-page=${hashValue}]`).length) return 1
  return hashValue
}

function updatePageTitle(pageNo) {
  const titleSplits = document.title.split(`-`)
  titleSplits[1] = ` Page ${pageNo} `
  document.title = titleSplits.join(`-`)
}

function openPage() {
  const pageNo = getPageNumber()
  const navButton = $(`[data-page=${pageNo}]`)
  if (navButton.hasClass(`selected`)) return

  function clearPageNavAjax() {
    if (pageNavAjax && pageNavAjax.readyState != 4) {
      pageNavAjax.abort()
      pageNavAjax = null
    }
  }

  function callpageNavAjax() {
    const url = `/api/page-items.php`
    const data = {type, "page-no": pageNo, "items-count": itemsPerPage}
    pageNavAjax = $.get(url, data, ajaxResponse => {
      $(`#loading-message`).hide()
      for (let i = 0; i < boxLinks.length; i++) {
        const boxLink = boxLinks[i]
        if (ajaxResponse.length > i) {
          $(boxLink).show()
          if (isMovie) {
            const movie = ajaxResponse[i]
            $(boxLink).find(`.title`).html(movie.name)
            $(boxLink).find(`a`).attr(`href`, `/pages/movie.php?id=${movie.id}`)
            $(boxLink).find(`img`).attr(`src`, movie.img)
          } else {
            const star = ajaxResponse[i]
            $(boxLink).find(`a`).attr(`href`, `/pages/star.php?id=${star.id}`)
            $(boxLink).find(`img`).attr(`src`, star.img)
            $(boxLink).find(`.name`).html(star.name)
            // $(boxLink).find(`.dob`).html(star.dob)
            $(boxLink).find(`.movie-count>span`).html(star.count)
          }
        }
      }
    })
  }

  const isMovie = type == `movie`
  const boxClassName = isMovie ? `.poster` : `.star-box`
  const boxLinks = $(boxClassName)
  boxLinks.hide()
  $(`#loading-message`).show()

  clearPageNavAjax()
  callpageNavAjax()
  adjustNavCss(pageNo)
  updatePageTitle(pageNo)

  /** Switch active selection */
  $(`.nav-item.selected`).removeClass(`selected`)
  navButton.addClass(`selected`)
}
    
function adjustNavCss(pageNo) {
  if (
    !$(`.nav-item-box.middle`).length ||
    $(`.nav-item-box.middle .nav-item`).length <= middleNavWidthCoefficient
  ) return

  let slideAnimatationTime = 300
  if (!pageNo) {
    slideAnimatationTime = 0
    pageNo = $(`.nav-item.selected`).attr(`data-page`)
  }

  const lowerPageLimit = (pageNo - 2) - ((middleNavWidthCoefficient - 1) / 2)
  const upperPageLimit = (noOfPages - 2) - (middleNavWidthCoefficient - 1)
  const targetPage = Math.min(Math.max(lowerPageLimit, 0), upperPageLimit)
  const navItemWidth = $(`.nav-item`).outerWidth()
  const scrollLeft = targetPage * navItemWidth

  $(`.nav-item-box.middle`).animate({scrollLeft}, slideAnimatationTime)
}

function resizeNavbarMiddle() {
  if (!$(`.nav-item-box.middle`).length) return

  if (window.innerWidth >= 1200) {
    var maxNavWidthCoefficient = 9
  } else if (window.innerWidth >= 1000) {
    var maxNavWidthCoefficient = 7
  } else if (window.innerWidth >= 800) {
    var maxNavWidthCoefficient = 5
  } else {
    var maxNavWidthCoefficient = 3
  }

  let middleNavItemsCount = $(`.nav-item-box.middle .nav-item`).length
  if (middleNavItemsCount > 3 && middleNavItemsCount % 2 == 0) middleNavItemsCount-- //assume odd number if >3
  middleNavWidthCoefficient = Math.min(middleNavItemsCount, maxNavWidthCoefficient)

  let navItemWidth = $(`.nav-item`).outerWidth()
  $(`.nav-item-box.middle`).width(navItemWidth * middleNavWidthCoefficient)
}
