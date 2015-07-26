# authorea-github-tex-Reader

A small and simple Reader (just a PHP script) for showing Authorea-Files (in LaTex) stored on GitHub in an HTML-Enviroment. Including a reference browser [modfied version of bibtexbrowser](https://github.com/monperrus/bibtexbrowser/) and a simple cachingsystem.

Demo: [live.offene-doktorarbeit.de](http://live.offene-doktorarbeit.de)

Version: alpha 0.201505

# Important
The folders "cache" and "data" need to be writable by the webserver. For Permalinks you need a .htaccess containing the following Parameters:
```
Options +FollowSymLinks
RewriteEngine On
RewriteRule ^chapter/([^.]*)[/]?$ index.php?chapter=$1
RewriteRule ^uebersicht index.php
RewriteRule ^literatur/all index.php?bibtex=all
RewriteRule ^quelle/([^.]*)[/]?$ /includes/bibtexbrowser.php?key=$1&bib=ADD-URL-TO-BIBTEX-FILE-HERE
```
