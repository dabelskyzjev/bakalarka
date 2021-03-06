<?php

use Nette\Application\Routers\RouteList,
    Nette\Application\Routers\Route;

/**
 * Router factory.
 */
class RouterFactory {

    /**
     * @return Nette\Application\IRouter
     */
    public function createRouter() {
        $router = new RouteList();
        $router[] = new Route('index.php', 'Homepage:default', Route::ONE_WAY);
        $router[] = new Route('profile/<action>/<id>', 'Profile:default');
        $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
        $router[] = new Route('<presenter>/<action>/<todo>[/<id>]', 'Browse:default');
        return $router;
    }

}
