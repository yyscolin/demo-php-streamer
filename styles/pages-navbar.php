  <style>
    #nav-pages {
      bottom: 0;
      height: 40px;
      line-height: 20px;
      padding-bottom: 20px;
      padding-top: 20px;
      position: fixed;
      width: 100%;
      z-index: 2;
    }
    .nav-item-box {
      display: inline-block;
      height: 80%;
      margin: 5px 0;
      position: relative;
      width: 100px;

      -ms-overflow-style: none;  /* IE and Edge */
      scrollbar-width: none;  /* Firefox */
    }
    .nav-item-box::-webkit-scrollbar {
      display: none;
    }
    .nav-item-box:nth-child(2) {
      overflow-x: hidden;
      width: 900px;
    }
    .nav-item {
      background-color: darkslateblue;
      border: 5px solid black;
      border-radius: 15px;
      color: white;
      cursor: pointer;
      display: inline-block;
      font-size: 20px;
      padding: 0;
      margin: 0 !important;
      width: 90px;
    }
    .nav-item.selected {
      background-color: darkblue;
      cursor: default;
    }<?php
    if (!$is_mobile) echo "
    .nav-item:not(.selected):hover {
        background-color: darkmagenta;
    }";?>


    @media only screen and (max-width: 1200px) {
      .nav-item-box:nth-child(2) {
        width: 700px;
      }
    }
    @media only screen and (max-width: 1000px) {
      .nav-item-box:nth-child(2) {
        width: 500px;
      }
    }
      
    @media only screen and (max-width: 800px) {
      .nav-item-box:nth-child(2) {
        width: 300px;
      }
    }

    @media only screen and (max-width: 600px) {
      .nav-item-box {
        width: 60px;
      }
      .nav-item-box:nth-child(2) {
        width: 180px;
      }
      .nav-item {
        border: 3px solid black;
        border-radius: 9px;
        width: 54px;
      }
    }
  </style>
