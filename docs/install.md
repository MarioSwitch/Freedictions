# Installation guide
This guide lists all steps required to install **Freedictions** on a web server, locally or online, private or public.

> [!WARNING]
> **YOU** (as server owner) are liable for published content. This includes usernames and predictions questions, details and outcomes.
>
> **IN NO EVENT** will initial developers (**MarioSwitch** and **yoshakami**) and all contributors (in any way) be liable for any published content on a public instance.
>
> For legally binding terms, see ["**Section 5 -- Disclaimer of Warranties and Limitation of Liability.**" of CC BY-SA 4.0 license](https://github.com/MarioSwitch/Freedictions/blob/v3/LICENSE#L316).

## Requirements
To run an instance of **Freedictions** on your server, you must have:
- A **web server** with **PHP** enabled. **Apache** is recommended.
- A **RDBMS** (relational database management system). **MariaDB** is recommended.

### Enabling ``RewriteRule``s
**``RewriteRule``s** are mandatory for **Freedictions** to run correctly.

To enable them, please check that:
1. ``mod_rewrite`` is enabled.
   1. Find your configuration file (e.g. ``/etc/apache2/conf/httpd.conf`` for Apache on a Linux distribution)
   2. In that file, find (with Ctrl+F) a line that says ``LoadModule rewrite_module modules/mod_rewrite.so`` and check it is uncommented (without ``#`` before). Uncomment this line, save, and quit if needed.
2. ``.htaccess`` rules are allowed in the corresponding directory
   1. In that same file, find the ``<Directory "/some/path">`` that matches **YOUR Freedictions directory**.
   2. Inside this element, check that ``AllowOverride`` is set to ``All``. Change the value if needed.
   3. Note that elements concerning parent paths might already allow overrides. It is still recommended to have a ``<Directory>`` element that specifically targets **Freedictions** to avoid mistakes in the future.

When those two things are verified, restart your web server to apply changes. It can then correctly apply ``RewriteRule``s specified in ``.htaccess``.

## Installing Freedictions
Download the latest version of **Freedictions** by either:
- Typing ``git clone https://github.com/MarioSwitch/Freedictions`` in a terminal. You will then have a ``Freedictions`` directory. With this method, you can also update your instance with ``git pull``.
- Downloading the [source code from GitHub](https://github.com/MarioSwitch/Freedictions/archive/refs/heads/v3.zip) and extract it. You will then have a ``Freedictions-v3`` directory.

You can then rename the directory or move it where you want, without forgetting updating the ``<Directory>`` element to target the new location.

## Configuring Freedictions
### Database
Create a database with the name you want, and initialise it with the ``database.sql`` given, available at project's root.

Define UTC as the default timezone of your RDBMS and enable events persistently. For MariaDB on a Linux distribution, this can be done by adding the following lines to ``/etc/mysql/my.cnf``:
```ini
[mysqld]
default_time_zone = '+00:00'
event_scheduler = ON
```

Save the file and restart your RDBMS to apply changes.

### ``config.php``
Declare configuration constants in a file named ``config.php`` located at project's root.

Below is an example of what this file should looks like (change values according to your instance and needs):
```php
<?php
// Database configuration
const CONFIG_DATABASE_HOST = "localhost";
const CONFIG_DATABASE_NAME = "predictions";
const CONFIG_DATABASE_USER = "root";
const CONFIG_DATABASE_PASSWORD = "";

// Other configuration
const CONFIG_COOKIES_EXPIRATION = 30*24*60*60; // Seconds before cookies (session and settings) expiration without client activity (default: 30 days)
const CONFIG_PATH = "/freedictions"; // Website directory relative to the web server root ("/var/www/html" or "C:\xampp\htdocs" for example), WITHOUT "/" or "\" at the end! It can be empty.
```

### ``.htaccess``
Update the ``.htaccess`` file by replacing the path at line 2 (``RewriteBase /freedictions/``) by **YOUR** website directory relative to the web server root. It should be the same as the one defined in ``CONFIG_PATH``, this time with the "/" or "\\" at the end.

## Finishing
### Adding an administrator
Create the first user with the web form (button "User +" located at the top-right corner or ``/signup``) and give it administrator role by executing this on your database (replace ``YourUsername`` by your actual username):
```sql
UPDATE `users` SET `mod` = 3 WHERE `username` = 'YourUsername';
```

Later, you can give other accounts verifier/moderator/administrator role by editing the targeted user with the web form (go to their user page > "Edit user" at the bottom (or ``/user/TargetedUsername/edit``), and change "Moderator" value). A detailed table of roles permissions is available [here](permissions.md).
