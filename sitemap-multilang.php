<?php
namespace Grav\Plugin;

use Grav\Common\Data;
use Grav\Common\Page\Page;
use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Common\Page\Pages;
use RocketTheme\Toolbox\Event\Event;

class SitemapMultilangPlugin extends Plugin
{
    /**
     * @var array
     */
    protected $sitemap = array();
    protected $isRoot = false;

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onBlueprintCreated' => ['onBlueprintCreated', 0]
        ];
    }

    /**
     * Enable sitemap only if url matches to the configuration.
     */
    public function onPluginsInitialized()
    {
        if ($this->isAdmin()) {
            $this->active = false;
            return;
        }

        /** @var Uri $uri */
        $uri = $this->grav['uri'];
        $route = $this->config->get('plugins.sitemap-multilang.route');

        if ($route && $route == $uri->path()) {
            $this->enable([
                'onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
                'onPagesInitialized' => ['onPagesInitialized', 0],
                'onPageInitialized' => ['onPageInitialized', 0],
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0]
            ]);
        }
    }

    /**
     * Generate data for the sitemap.
     */
    public function onPagesInitialized()
    {
        require_once __DIR__ . '/classes/sitemapentry.php';

        /** @var Pages $pages */
        $pages = $this->grav['pages'];
        $routes = array_unique($pages->routes());
        ksort($routes);

        $ignores = (array) $this->config->get('plugins.sitemap-multilang.ignores');

        $rootUrl = (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
        $currentLanguage = $this->grav['language']->getLanguage();

        $reqUri = $_SERVER['REQUEST_URI'];
        $homeSitemapPath = array('/sitemap.xml', '/sitemap');

        // ROOT SITEMAP
        if (in_array($reqUri, $homeSitemapPath)) {
            $this->isRoot = true;
            $languages = $this->grav['language']->getLanguages();
            foreach ($languages as $language) {
                $route = '/'.$language;
                $entry = new SitemapEntry();

                $entry->location = $rootUrl . $route .'/sitemap.xml';
                $now = new \DateTime();
                $entry->lastmod = $now->format('Y-m-d');
                $this->sitemap[$route] = $entry;
            }
        }
        else {
            foreach ($routes as $route => $path) {
                /** @var Page $page */
                $page = $pages->get($path);

                if ($page->home()) {
                    $route = '/'.$currentLanguage;
                }

                if ($page->published() && $page->routable() && $page->visible() && !in_array($page->route(), $ignores)) {

                    $entry = new SitemapEntry();
                    $entry->location = ($page->home()) ? $rootUrl.$route : $page->permaLink();
                    $entry->lastmod = date('Y-m-d', $page->modified());

                    // optional changefreq & priority that you can set in the page header
                    $header = $page->header();
                    if (isset($header->sitemap['changefreq'])) {
                        $entry->changefreq = $header->sitemap['changefreq'];
                    }
                    if (isset($header->sitemap['priority'])) {
                        $entry->priority = $header->sitemap['priority'];
                    }

                    if ($page->home()) {
                        array_unshift($this->sitemap, $entry);
                    } else {
                        $this->sitemap[$route] = $entry;
                    }
                }
            }
        }
    }

    public function onPageInitialized()
    {
        // set a dummy page
        $page = new Page;
        $page->init(new \SplFileInfo(__DIR__ . '/pages/sitemap-multilang.md'));

        unset($this->grav['page']);
        $this->grav['page'] = $page;
    }

    /**
     * Add current directory to twig lookup paths.
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * Set needed variables to display the sitemap.
     */
    public function onTwigSiteVariables()
    {
        $twig = $this->grav['twig'];
        $twig->template = ($this->isRoot) ? 'sitemapIndex.xml.twig' : 'sitemap.xml.twig';
        $twig->twig_vars['sitemap'] = $this->sitemap;
    }

    /**
     * Extend page blueprints with feed configuration options.
     *
     * @param Event $event
     */
    public function onBlueprintCreated(Event $event)
    {
        static $inEvent = false;

        /** @var Data\Blueprint $blueprint */
        $blueprint = $event['blueprint'];
        if (!$inEvent && $blueprint->get('form/fields/tabs', null, '/')) {
            $inEvent = true;
            $blueprints = new Data\Blueprints(__DIR__ . '/blueprints/');
            $extends = $blueprints->get('sitemap-multilang');
            $blueprint->extend($extends, true);
            $inEvent = false;
        }
    }
}
