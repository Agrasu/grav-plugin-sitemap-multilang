# Grav Sitemap Plugin

`Sitemap-Multilang` is a [Grav](http://github.com/getgrav/grav) Plugin that generates a [map of your pages](http://en.wikipedia.org/wiki/Site_map) in `XML` format that is easily understandable and indexable by Search engines.

# Installation

Installing the Sitemap plugin can be done in one of two ways. Our GPM (Grav Package Manager) installation method enables you to quickly and easily install the plugin with a simple terminal command, while the manual method enables you to do so via a zip file. 


## Manual Installation

To install this plugin, just download the zip version of this repository and unzip it under `/your/site/grav/user/plugins`. Then, rename the folder to `sitemap-multilang`. You can find these files either on [GitHub](https://github.com/mazaka/grav-plugin-sitemap-multilang).

You should now have all the plugin files under

    /your/site/grav/user/plugins/sitemap-multilang

>> NOTE: This plugin is a modular component for Grav which requires [Grav](http://github.com/getgrav/grav), the [Error](https://github.com/getgrav/grav-plugin-error) and [Problems](https://github.com/getgrav/grav-plugin-problems) plugins, and a theme to be installed in order to operate.


# Usage

The `sitemap-multilang` plugin works out of the box. You can just go directly to `http://yoursite.com/sitemap` and you will see the generated `XML`.

# Config Defaults

```
enabled: true
route: '/sitemap'
ignores:
  - /blog/blog-post-to-ignore
  - /ignore-this-route
```

You can ignore your own pages by providing a list of routes to ignore.

## Only allow access to the .xml file

If you want your sitemap to only be accessible via `sitemap.xml` for example, set the route to `/sitemap` and add this to your `.htaccess` file:

`Redirect 301 /sitemap /sitemap.xml`
