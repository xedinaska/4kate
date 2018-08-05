<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" lang="ru">
 <head>
  <title>{DN="page_title"} - {DB_CONFIG="title_suffix"}</title>
  <meta name="keywords" content="{DN="page_keywords"}" />
  <meta name="description" content="{DN="page_description"}" />
  <link rel="stylesheet" href="{DN="appl_webdir"}{DN="tpls_dir"}{DB_CONFIG="stylename"}/css/default.css" type="text/css" />
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <script type="text/javascript" src="/autosuggest/autosearch.js"></script>

  <script language="JavaScript" type="text/javascript">
  window.onload=disable_autocomplete;


  function suggest(x)
   {
    var xmlhttp = createXHR(xmlhttp); 
    
    url = '/{DN="lng"}/search_ac/?searchtext='+x;
  
    xmlhttp.open("GET",url,false);
    xmlhttp.send(null);
  
    answer = xmlhttp.responseText;
  
    if (answer.length != 0)
     {
      document.getElementById('autocomplete').style.display='block';
      document.getElementById('autocomplete').innerHTML=answer;
     }
      else
     {
      document.getElementById('autocomplete').style.display='none';
     }
   }
 

  </script> 

 </head>
 <body>
  
  <div id="container">
   <div id="header">
    <p style="text-align:center; color:#fff;">header</p>
   </div>
    <div id="content">