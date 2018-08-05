<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:media="http://search.yahoo.com/mrss/">
<channel>
	<title>{DN="page_title"}</title>
	<link>http://{DB_CONFIG="domain_name"}/{DN="lng"}/{DN="rss_url"}</link>
	<description>{DN="page_title"}</description>
	<language>{DN="lng"}</language>
	<lastBuildDate>{DN="rss_date"}</lastBuildDate>
	<ttl>30</ttl>
	<atom:link href="http://{DB_CONFIG="domain_name"}/{DN="lng"}/{DN="rss_url"}" rel="self" type="application/rss+xml" />

{DN="rss_items"}

</channel>
</rss>
