function disable_autocomplete()
 {
   
  if (document.getElementsByTagName)
   {
    var inputElements = document.getElementsByTagName("input");
    for (i=0; inputElements[i]; i++)
     {
      if (inputElements[i].className && (inputElements[i].className.indexOf("disableAutoComplete") != -1))
       {
        inputElements[i].setAttribute("autocomplete","off");
       }
       //if current input element has the disableAutoComplete class set.
     }//loop thru input elements
   }//basic DOM-happiness-check
 }


// Функция для создания объекта XMLHTTPRequest
function createXHR(XmlHttpRequest)
 {
 
  if (XmlHttpRequest == null)
   {
    if (window.XMLHttpRequest)
     {
      XmlHttpRequest = new XMLHttpRequest();
     }
    else
     {
      XmlHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
     }
   }
  return XmlHttpRequest;
 }



