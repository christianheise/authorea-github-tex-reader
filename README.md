# authorea-github-tex-Reader

A small and simple reader (PHP script) for showing [Authorea](http://authorea.com) files (written in LaTeX & stored on GitHub) in an HTML enviroment. Including a reference browser ([modfied version of bibtexbrowser](https://github.com/monperrus/bibtexbrowser/)) and a simple caching system.

Demo: [live.offene-doktorarbeit.de](http://live.offene-doktorarbeit.de)

Version: alpha 0.37

# Installation Notes
1. The folders "cache" and "data" need to be writable by the webserver
2. Add the URL to your dynamic BibTeX file at `$_GET[Q_FILE]="URL-HERE";` in includes/bibtexbrowser.php (line 3949)
3. Edit the `General Settings` and `Settings for Import` section in the `index.php` file
4. For Permalinks you need to create a .htaccess file in the root directory containing the following parameters:
```
Options +FollowSymLinks
RewriteEngine On
RewriteRule ^chapter/([^.]*)[/]?$ index.php?chapter=$1
RewriteRule ^uebersicht index.php
RewriteRule ^literatur/all index.php?bibtex=all
RewriteRule ^quelle/([^.]*)[/]?$ /includes/bibtexbrowser.php?key=$1&bib=ADD-URL-TO-BIBTEX-FILE-HERE
```
5. For the initial use, you have to click through every chapter so all files are created initially
