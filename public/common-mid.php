
</head>
<body>
<div id='banner'>
  <button id='menu-button' onclick='$("body").toggleClass("menu-active")'>☰</button
  ><img id='banner-icon' onclick="window.location.href='/'" src='/banner.png' title='Go to homepage'
  ><div id='menu-bar' class='inline'>
    <a href="/stars"><?php echo get_text('stars', strtoupper); ?></a
    ><a href="/vids"><?php echo get_text('videos', strtoupper); ?></a
    ><form id='search-box' class='inline' action='/search.php'>
      <select id='search-type' name='type' onchange='searchDatabase()'>
        <option value='star'><?php echo get_text('stars', ucfirst); ?></option>
        <option value='vid'><?php echo get_text('videos', ucfirst); ?></option>
      </select
      ><input id='search-field' type="search" name='query' oninput='searchDatabase()' placeholder="<?php echo get_text('keyword'); ?>...">
      <div id='search-results' style="display: none;"></div>
      <button type='submit'><?php echo get_text('go', strtoupper); ?></button>
    </form><!--
    --><div class='inline' style='cursor:pointer;margin-left:16px'>
      <img src='/images/languages-white.png' width='24px' onclick='$("#lang-dropdown").toggle()'>
      <ul id='lang-dropdown' class='dropdown-list'>
        <li onclick='setLanguange("en")'>English</li>
        <li onclick='setLanguange("jp")'>日本語</li>
      </ul>
      <script>
        function setLanguange(language) {
          document.cookie = `language=${language}`
          location.reload()
        }
      </script>
    </div>
  </div>
</div>