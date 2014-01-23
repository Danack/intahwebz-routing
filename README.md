intahwebz-routing
=================

A component for mapping URLs to controllers and parsing variables.


    $contentView = [Resource::CONTENT, Privilege::VIEW];

    $adminView = [Resource::ADMIN, Privilege::VIEW];

    array(

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
            'callable' =>array(
                BaseReality\Controller\HomePage::class,
                'show',
            ),
            'access' => $contentView,
         ),
     
     ),
    
```