# The Heritage Kitchen

The Heritage Kitchen is a PHP and MySQL family cookbook. It preserves recipes,
family lineages, stories, images, and moderated comments in a public site with a
separate administration area.

## Features

- Public recipe catalogue with family-lineage navigation
- Recipe search with lineage filtering and pagination
- Friendly recipe permalinks
- Recipe image uploads
- Public comments with CAPTCHA and moderation
- Member registration and login
- Administration for recipes, lineages, comments, and users

## Requirements

- Apache with `mod_rewrite` enabled
- PHP with the PDO MySQL extension
- MySQL or MariaDB
- A web root that permits `.htaccess` overrides

The project is designed to run in XAMPP from its `htdocs` directory.

## Local Setup

1. Create a MySQL database named `heritage_kitchen`.
2. Confirm the connection values in `connect.php` match the local database.
3. Place the project inside the Apache document root.
4. Start Apache and MySQL from XAMPP.
5. Open the project URL in a browser.
6. Visit `admin/setup.php` to create the first administrator account.

For the current folder name, the local URL is:

```text
http://localhost/Web%20Development%202/heritage-kitchen/
```

## Project Structure

```text
admin/               Administration and authentication pages
uploads/             Runtime recipe images (ignored by Git)
captcha.php          CAPTCHA image endpoint
connect.php          PDO database connection
functions.php        Shared validation and URL helpers
image_functions.php  Image validation and storage helpers
index.php            Public recipe catalogue
lineage.php          Recipes grouped by family lineage
recipe.php           Recipe detail and comment submission
register.php         Member registration
search.php           Recipe search and pagination
style.css            Site styles
```

## Security Notes

The application uses prepared PDO statements for user-provided database values,
password hashing for accounts, session ID regeneration after login, output
escaping, validated upload types, and role checks for administration pages.

Uploaded images and local secrets are excluded from version control by
`.gitignore`.

