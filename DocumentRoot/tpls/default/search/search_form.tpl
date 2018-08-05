<form action="{DN="appl_webdir"}{DN="lng"}/search/" method="post" id="searchform">
 <input type="text" name="searchtext" value="{SEARCH_VALUE}" onkeyup="suggest(this.value)" class="disableAutoComplete" id="searchtext"/>
 <div id="autocomplete" style="display:none"></div>
 <input type="submit" name="seach_go" value="{DB_CONFIG="search_go"}" />
</form>