<?php
namespace Grav\Plugin;

use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;
use Grav\Plugin\Publish\AdminController;

class PublishPlugin extends Plugin
{

    /**
     * Initialize plugin and subsequent events
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0],
            'onTwigSiteVariables'  => ['onTwigSiteVariables', 0]
        ];
    }

    /**
     * Register events with Grav
     * @return void
     */
    public function onPluginsInitialized()
    {
        /* Check if Admin-interface */
        if (!$this->isAdmin()) {
            $this->active = false;
            return;
        }

        $user = $this->grav['user'];
        if (!($user->authenticated && ($user->authorize('admin.publish') || $user->authorize('admin.super')))) {
            return;
        }

        require_once __DIR__ . '/vendor/autoload.php';

        $this->enable([
            'onPageInitialized' => ['onPageInitialized', 0],
            'onAssetsInitialized' => ['onAssetsInitialized', 0],
            'onFormProcessed' => ['onFormProcessed', 0],
            'onPageContentProcessed' => ['onPageContentProcessed', 0],
            'onAdminTwigTemplatePaths' => ['onAdminTwigTemplatePaths', 0],
            'onAdminMenu' => ['onAdminMenu', 0]
        ]);

        /** @var AdminController controller */
        $this->controller = new AdminController($this);

        $page_file = GRAV_ROOT.'/user/plugins/publish/admin/pages/publish.md';
        if (!file_exists($page_file)) {
            mkdir(dirname($page_file), 0777, true);
            file_put_contents($page_file, file_get_contents(__DIR__.'/admin/pages/publish.md'));
        }
    }

    /**
     * Custom CSS setting
     * @return void
     */
    public function onAssetsInitialized()
    {
        //$this->grav['assets']->addInlineCss('.user-details img, #admin-user-details img, .admin-user-details img {border-radius: 50%;}');
    }

    /**
     * Register template overrides
     * @param RocketTheme\Toolbox\Event\Event $event
     * @return void
     */
    public function onAdminTwigTemplatePaths($event)
    {
        $event['paths'] = array_merge($event['paths'], [__DIR__ . '/admin/themes/grav/templates']);
        //$this->grav['twig']->twig_paths[] = __DIR__ . '/admin/themes/grav/templates';
    }

    /**
     * Set needed variables to display direcotry.
     */
    public function onTwigSiteVariables()
    {
        if ($this->isAdmin()) {
            $this->grav['assets']->addJs('plugin://publish/js/publish.js');
        }
    }

    /**
     * Create content variables and push to Twig
     * @return void
     */
    public function onPageContentProcessed()
    {
        //$this->grav['twig']->twig_vars['some_key'] = $someData;
    }

    public function onAdminMenu()
    {
        $this->grav['twig']->plugins_hooked_nav['PLUGIN_ADMIN_PUBLISH.MENU'] = [
            'route'     => $this->name.'.json/task:Run',
            'icon'      => 'fa-cloud-upload',
            'authorize' => ['admin.publish','admin.super']
        ];
        //
        // $admin_route = $this->config->get('plugins.admin.route');
        // $this->grav['twig']->plugins_quick_tray['PublishLink'] = [
        //     'icon' => 'fa fa-cloud-upload',
        //     'route' => $admin_route.'/'.$this->name.'.json/task:Run',
        //     'hint' => 'PLUGIN_ADMIN_PUBLISH.TITLE'
        // ];
    }

    public function onPageInitialized()
    {
        if ($this->isAdmin() && $this->controller->isActive()) {
            $this->controller->execute();
            $this->controller->redirect();
        }
    }

    public function onFormProcessed(Event $event)
    {
        // $action = $event['action'];
        //
        // if ($action == 'some') {
        //     $this->some();
        // }
    }

}
