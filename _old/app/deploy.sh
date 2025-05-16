wget --no-clobber --convert-links -nH -r -p -E -e robots=off -U mozilla http://the.independency.co.localhost/ -P /Applications/MAMP/htdocs/the.independency.co/docs

wget --no-clobber --convert-links -nH -r -p -E -e robots=off -U mozilla http://the.independency.co.localhost/sitemap.txt -P /Applications/MAMP/htdocs/the.independency.co/docs

mv /Applications/MAMP/htdocs/the.independency.co/docs/sitemap.txt.html /Applications/MAMP/htdocs/the.independency.co/docs/sitemap.txt