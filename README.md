      __  ___   ____  _   _ ____   _____  _    ____ ____
     / / |__ \ |  _ \| | | |  _ \ |_   _|/ \  / ___/ ___|
    / /    / / | |_) | |_| | |_) |  | | / _ \| |  _\___ \
    \ \   |_|  |  __/|  _  |  __/   | |/ ___ \ |_| |___) |
     \_\  (_)  |_|   |_| |_|_|      |_/_/   \_\____|____/
                         A PHP source code tagging system.

# Installation
1. Clone this repository
2. Install composer: https://getcomposer.org/
3. In the repository root, run `composer install`
4. That's it, you probably want to configure the bin/phptags executable in your editor.

# Requirements
Should run on PHP 5.3 and newer.

# Usage: index
Use `phptags index` to create a new tags database in the current working directory and start indexing source files recursively.

```
$ phptags index [--help]
```

Or specify the target directory.

```
$ phptags index --root /path/to/source
```

By default, the database file name is `PHPTAGS.sqlite`, but that can be changed with the `--database` option. The value may be an absolute path, in or outside of the project directory.

```
$ phptags index --database TAGS
```

Source files are recognized by customizable regular expression on the file name. By default all files with the `.php` file extension are matched.

```
$ phptags index --pattern `\.(php|inc|module|profile|install)$`
```

# Usage: query
The command `phptags query` can be used to find tags in the database. It accepts the `--root` and `--database` options, and has several additional options to specify the query.

It searches for definitions only, except when the --reference options is given.

```
$ phptags query --help
$ phptags query ^FindThis$
$ phptags query findthis --type function
$ phptags query findthis --reference
$ phptags query findthis --format ctags
```

# License
    Copyright 2014 Joris Steyn

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
