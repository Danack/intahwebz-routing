<?php


require_once "../vendor/autoload.php";

//Obviously - don't load files like this in your real project. This example
//is only including classes via require as I don't want to pollute the autoloading 
//class map with example files
require_once("Security/AccessControl.php");
require_once("Security/AccessDeniedException.php");
require_once("Security/LoginRequiredException.php");
require_once("Security/Privilege.php");
require_once("Security/Resource.php");
require_once("Security/Role.php");
require_once("Controller/Index.php");

use Intahwebz\Routing\Example\Security\Resource;
use Intahwebz\Routing\Example\Security\Privilege;
use Intahwebz\Routing\Example\Security\AccessControl;
use Intahwebz\Routing\Example\Security\AccessDeniedException;
use Intahwebz\Routing\Example\Security\LoginRequiredException;

use Intahwebz\Jig\JigConfig;
use Intahwebz\Routing\RouteMissingException;

//use Intahwebz\Routing\Example\ViewModel\ExampleViewModel;

$contentView = [Resource::CONTENT, Privilege::VIEW];

$adminView = [Resource::ADMIN, Privilege::VIEW];
$adminEdit = [Resource::ADMIN, Privilege::EDIT];
$adminDelete = [Resource::ADMIN, Privilege::DELETE];

$routes = array(
    array(
        'name' => 'image',
        'pattern' => '/{path}/{imageID}/{size}/{filename}',
        'callable' => array(
            'BaseReality\\ImageController',
            'showImage',
        ),
        'requirements' => array(
            'imageID' => '\d+',
            'size' => '\w+',
            'filename' => '[^/]+',
            'path' => "(image|proxy)",
        ),
        'defaults' => array(
            'path' => 'image',
            'size' => null
        ),
        'optional' => array(

        )
    ),

    array(
        'name' => 'homepage',
        'pattern' => '/',
        'callable' => array(
            Intahwebz\Routing\Example\Controller\Index::class,
            'showHomePage',
        ),
        'access' => $contentView,
        'template' => 'pages/index'
    ),
);

/**
 * @return \Auryn\Provider
 */
function setupProvider() {

    $provider = new Auryn\Provider();

    $provider->alias(Intahwebz\Request::class, Intahwebz\Routing\HTTPRequest::class);
    $provider->define(
        Intahwebz\Routing\HTTPRequest::class,
        array(
            ':server' => $_SERVER,
            ':get' => $_GET,
            ':post' => $_POST,
            ':files' => $_FILES,
            ':cookie' => $_COOKIE
        )
    );

    $provider->alias(Intahwebz\Router::class, Intahwebz\Routing\Router::class);
    $provider->share(Intahwebz\Router::class);
    $provider->share(Intahwebz\Request::class);

    $provider->alias(Intahwebz\ViewModel::class, Intahwebz\Routing\Example\ViewModel\ExampleViewModel::class);
    $provider->share(Intahwebz\ViewModel::class);

    $defaultJigConfig = new JigConfig(
        "templates/",
        "output/",
        "php.tpl",
        \Intahwebz\Jig\JigRender::COMPILE_CHECK_MTIME //COMPILE_CHECK_EXISTS on live site
    );

    $provider->share($defaultJigConfig);

    return $provider;
}

function showClientErrorPage() {
    echo "Page not found.";
    exit(0);
}


function checkAllowed(
    Intahwebz\Session $session,
    AccessControl $accessControl,
    Intahwebz\Route $route ) {

    $userRole = $session->getSessionVariable('userRole');
    $isAllowed = $accessControl->isAllowed(
        $userRole,
        $route->getACLResourceName(),
        $route->getACLPrivilegeName()
    );

    if ($isAllowed == false) {

        if ($userRole == null) {
            //user is not logged in, show them login.
            throw new LoginRequiredException();
        }
        
        //User tried to access they shouldn't have access to.
        throw new AccessDeniedException("Access not allowed: userRole = $userRole, resource = ".$route->getACLResourceName().", privilege = ".$route->getACLPrivilegeName());
    }
}


function servePage() {

    $provider = setupProvider();

    $router = $provider->make(Intahwebz\Router::class);
    $request = $provider->make(Intahwebz\Request::class);
    $accessControl = $provider->make(AccessControl::class);
    $session = $provider->make(Intahwebz\Session::class);

    $route = $router->getRouteForRequest($request);

    if($route == false){
        showClientErrorPage();
    }

    $lastTemplate = null;

    while ($route != false) {
        checkAllowed($session, $accessControl, $route);
        $callable = $route->getCallable();

        if ($callable != null) {
            $classPath = $callable[0];
            $methodName = $callable[1];

            $lastTemplate = $route->getTemplate();
            
            $controller = $provider->make($classPath);

            $mergedParams = $route->getMergedParameters($request);
            $injectableRouteParams = array();
            foreach ($mergedParams as $key => $value) {
                $injectableRouteParams[':'.$key] = $value;
            }

            try {
                $route = $provider->execute(array($controller, $methodName), $injectableRouteParams);

                if ($route != null) {
                    if(is_string($route)) {
                        $route = $router->getRoute($route);
                    }
                }
            }
            catch (\Auryn\InjectionException $ie) {
                throw new \Auryn\InjectionException(
                    "InjectionException calling ".$callable.", ".$ie->getMessage(),
                    $ie->getCode(),
                    $ie
                );
            }
        }
        else{
            //The route had no callable
            $route = null;
        }
    }

    $viewModel = $provider->make(Intahwebz\Routing\Example\ViewModel\ExampleViewModel::class);

    if ($lastTemplate != null) {
        $viewModel->setTemplate($lastTemplate);
        $viewModel->render();
    }
}


try {
    servePage();
}
catch(LoginRequiredException $lre) {
    header("Location: /login");
    exit(0);
}
catch (AccessDeniedException $ade) {
    header("HTTP/1.0 403 Forbidden");
    //you probably want to log this
    exit(0);
}
catch (RouteMissingException $rme) {
    header("HTTP/1.0 404 Not found");
    exit(0);
}
catch (\Exception $e) {
    echo "Oops.";
    exit(0);
}
