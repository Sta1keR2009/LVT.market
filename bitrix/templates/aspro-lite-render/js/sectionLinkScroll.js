var options = 
{
    tabs_wrapper: ".line-block",
    tab_item: "> .line-block__item",
} 

InitLinkScroll = function () {
    $(".dropdown-select__list").scrollTab(options);
  };
  
  $(document).ready(function () {
    InitLinkScroll();
  });