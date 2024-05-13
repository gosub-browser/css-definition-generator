# Definition Generator

These scripts will generate a definition and typedef file from all the CSS properties currently available. It does so by
scraping the MDN website for the CSS properties and their descriptions.

Note that this is not a perfect solution, as the MDN website is not always up to date with the latest CSS properties. However, it is 
a good starting point. The properties we are scraping are available from:

https://www.w3.org/Style/CSS/all-properties.en.json


## Usage

To generate the definition and typedef files, simply run the following command:

```bash
$ php definitions.php
```

This will create the definitions.json file AND the ./cache directories. The cache dir
is nice if you want to run the same script multiple times, as it will not have to re-scrape 
the MDN website.

Once you have the `definition.json` file generated. You can run the following command to generate the typedef file:

```bash
$ php typedefs.php > typedefs.raw.txt
$ php cleanup.php typedefs.raw.txt > typedefs.json 
```

This will generate the `typedefs.json` file, which you can then use in your project.

If you like, you can use `missing.php` to check if there are any missing properties in the `definitions.json` file.
