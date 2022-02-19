const TopBanner = () => {
  const [menu2Items, setMenu2] = React.useState([])
  menu2.set = setMenu2

  const searchField = React.useRef()
  const searchType = React.useRef()

  function toggleLanguageMenu1() {
    if (menu2.current == `language-menu`) return menu2.reset()
    menu2.style = {
      color: `darkslateblue`,
      right: `20px`,
      top: `80px`,
    }
    toggleLanguageMenu()
  }

  function toggleLanguageMenu2() {
    if (menu2.current == `language-menu`) return menu2.reset()
    menu2.style = {color:`white`}
    toggleLanguageMenu()
  }

  const toggleLanguageMenu = () => {
    menu2.current = `language-menu`
    let menuList = languages.map(_ => <li onClick={() => setLanguange(_.code)}>{_.name}</li>)
    menuList.unshift(<li className="short-banner-item" onClick={hideMenu2}>ᐊ GO BACK</li>)
    menu2.set(menuList)
    $(`#banner`).addClass(`secondary`)
  }
  const hideMenu2 = () => {
    menu2.current = null
    $(`#banner`).removeClass(`secondary`)
  }

  const searchAjax = {
    current: null,
    get: () => {
      searchAjax.clear()
      let query = searchField.current.value
      if (!query.length) return menu2.reset()

      let url = `/api/search-database.php`
      let type = searchType.current.value
      let data = {query, type}
      searchAjax.current = $.get(url, data, ({type, results}) => {
        menu2.current = `search-results`
        menu2.style = {
          "color": `darkslateblue`,
          "max-width": `700px`,
          "min-width": `150px`,
          "right": `100px`,
          "text-align": `left`,
          "top": `65px`,
        }
        menu2.set(results.map(({id, name}) => {
          const redirect = () => window.location.href = `/${type}/${id}`
          return <li onClick={redirect}>{name}</li>
        }))
      })
    },
    clear: () => {
      if (searchAjax.current) {
        searchAjax.current.abort()
        searchAjax.current = null
      }
    }
  }

  return <>
    <button id="menu-button" onClick={() => $(`body`).toggleClass(`menu-active`)}>☰</button>
    <img id="banner-icon" onClick={() => window.location.href=`/`} src="/banner.png" title="Go to homepage"/>
    <div id="menu-bar">
      <a href="/stars">{displayText.stars}</a>
      <a href="/movies">{displayText.movies}</a>
      <a class="short-banner-item" href="/search.php">SEARCH</a>
      <a class="short-banner-item" onClick={toggleLanguageMenu2}>LANGUAGE ▷</a>
      <form class="long-banner-item" action="/search.php" style={{"margin-left":`32px`}}>
        <select id="search-type" ref={searchType} name="type" onChange={searchAjax.get}>
          <option value="star">{displayText.stars}</option>
          <option value="movie">{displayText.movies}</option>
        </select>
        <input ref={searchField} type="search" name="query" onInput={searchAjax.get} onFocus={searchAjax.get} placeholder={`${displayText.keyword}...`}/>
        <button type="submit" style={{margin: `0 .4vw`}}>{displayText.go}</button>
      </form>
      <div class="long-banner-item" style={{cursor:`pointer`,margin:`16px`}}>
        <img src="/images/languages-white.png" width="24px" onClick={toggleLanguageMenu1}/>
      </div>
    </div>
    <div id="menu-secondary" style={menu2.style}>{menu2Items}</div>
  </>
}

ReactDOM.render(
  <TopBanner/>,
  document.getElementById(`banner`)
)
