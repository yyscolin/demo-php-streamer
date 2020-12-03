  <script><?php
  echo "
    const noOfPages = $no_of_pages
    const type = '$type'
    const itemsPerPage = $items_per_page
    ";?>

    adjustNavCss()

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
          let boxClassName = `.${type.substr(0, type.length - 1)}-box`
          let boxLinks = $(boxClassName)
          for (let i = 0; i < boxLinks.length; i++) {
            let boxLink = boxLinks[i]
            if (response.length > i) {
              $(boxLink).show()
              if (type == 'vids') {
                  let vid = response[i]
                $(boxLink).attr('href', `/vid/${vid.id}`)
                $(boxLink).find('p').html(vid.id)
                $(boxLink).find('img').attr('src', `/media/covers/${vid.id}.jpg`)
              } else {
                let star = response[i]
                let jpName = star.name_j ? star.name_j : ''
                let enName = star.name_f
                if (star.name_l) enName += ' ' + star.name_l
                let dob = star.dob ? star.dob : ''
                $(boxLink).find('img').attr('onclick', `window.location.href="/star/${star.id}"`)
                $(boxLink).find('img').attr('src', `/media/stars/${star.id}.jpg`)
                $(boxLink).find('.en-name').html(enName)
                $(boxLink).find('.jp-name').html(jpName)
                $(boxLink).find('.dob').html(dob)
                $(boxLink).find('.vid-count').attr('href', `/star/${star.id}`)
                $(boxLink).find('.vid-count').html(`${star.count} Videos`)
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
      let navWidthCoefficient = 0
      if (window.innerWidth >= 1200) {
        navWidthCoefficient = 3
      } else if (window.innerWidth >= 1000) {
        navWidthCoefficient = 2
      } else if (window.innerWidth >= 800) {
        navWidthCoefficient = 1
      }

      if (!pageNo) pageNo = $('.nav-item.selected').attr('data-page')
      const lowerPageLimit = pageNo - (3 + navWidthCoefficient)
      const upperPageLimit = noOfPages - (5 + 2 * navWidthCoefficient)
      const targetPage = Math.min(Math.max(lowerPageLimit, 0), upperPageLimit)
      const navItemWidth = $('.nav-item').outerWidth()
      const scrollLeft = targetPage * navItemWidth

      $('.nav-item-box:nth-child(2)').animate({scrollLeft}, 600)
    }
  </script>
