{FILE="header_news.tpl"}
{FILE="menu.tpl"}
 
 <div class="center">
  {DN="bread_crumbs"}
  <h1><a href="/{DN="lng"}{DN="appl_webdir"}news/tags/">[+]</a> {DN="page_name"}</h1>

  <ul class="news_list">  
   <li>{DN="powered_by"}</li>
  </ul>
  
  {DN="pagination"}
  
  <ul class="news_list">
   {DN="news_list"}
  </ul>
  
  
 </div>

 <div class="right">
  Banners
 </div>
 <div class="clear-all"></div>
{FILE="footer.tpl"}