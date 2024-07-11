# FAQ for `WEB_ROOT` and `APP_ROOT`

## How to detect my document root to use it in the `WEB_ROOT` constant?

Create a PHP file somewhere inside your project structure (or just use an
existing one) and add the follwing line.

```php
echo 'My document root is "' . $_SERVER['DOCUMENT_ROOT'] . '"';
```

Now run the script and copy the part wrapped in quotes and use it as value
for the `WEB_ROOT` constant.

## What's that `APP_ROOT` thing?

Your project root (or "app root") is not necessarily the same directory as your document root.

The project root is the top most directory where all of your
files are stored that belong to the project, or website.

The document root on the other hand is the directory that is publicly accessible. When
creating a file `test.php` inside your document root, you will be able to run it
directly in your browser by entering `https://your-awesome-website.com/test.php`.

> In some frameworks, the document root points into a `public` directory that exists inside
> your project root.

## Example for project root and document root

Your document root can be a directory inside your project root:

```text
/project-root                      <- This is your project root
|-- config
|   |-- database.php
|   |-- app.php
|
|-- public                         <- This is most likely your document root
|   |-- index.php
|   |-- .htaccess
```

But your document root can also be the same directory as your project root:

```text
/project-root                      <- This is your project root and also your document root.
|-- config
|   |-- database.php
|   |-- app.php
|-- index.php
|-- .htaccess
```

## A little help for finding the correct target paths

### Project root

- Your project root is most likely where a `.git` directory exists (in case you're working with git).
- Common files that can be found in the project root are `composer.json`, `.env` (
  or `.env.example`), `.gitignore`, `.editorconfig`, `Readme.md` or the (usually hidden) `.git` directory.

### Document root

- Your document root is most likely the directory where the `index.php` and/or `.htaccess` files reside.
- Other common files that can be found in the document root are `favicon.ico`, `robots.txt` or `sitemap.xml`.
- You can find out the document root by printing `$_SERVER['DOCUMENT_ROOT']` somewhere in your PHP code.

> Popular options for naming the document root directory (if it's not the project root itself)
> are `public`, `www`, `web` or `webroot`.