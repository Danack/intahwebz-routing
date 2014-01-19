<?php

return array(


	array(
		'name' => 'javascriptInclude',
		'pattern' => '/js/jsInclude/{jsInclude}',
		'callable' => array(
			'BaseReality\\Controller\\ScriptInclude',
			'echoJavascriptIncludes',
		),
	),

	# ^/css/cssInclude/(.*)$ /css/cssInclude.css.php?cssInclude=$1 last;
	array(
		'name' => 'cssInclude',
		'pattern' => '/css/cssInclude/{cssInclude}',
		'callable' => array(
			'BaseReality\\Controller\\ScriptInclude',
			'echoCSSIncludes',
		),
	),


	array(
		'name' => 'blogRSSFeed',
		'pattern' => '/rss/',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'rssFeed'
		),
	),

	array(
		'name' => 'blogUpload',
		'pattern' => '/blog/upload',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'handleUpload'
		),
	),

	array(
		'name' => 'blogReplace',
		'pattern' => '/blog/{blogPostID}/replace/(\.)?',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'handleReplace'
		),
	),



	array(
		'name' => 'blogUploadForm',
		'pattern' => '/blogUploadForm',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'uploadForm'
		),
	),

	array(
		'name' => 'blogPostEdit',
		'pattern' => '/{blogPostID}/edit',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'showEdit'
		),
		'requirements' => array(
			'blogPostID' => '\d+',
		),
	),

	array(
		'name' => 'blogDraft',
		'pattern' => '/blog/drafts/{draftFilename}{separator}{format}',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'displayDraft'
		),
		'requirements' => array(
			'draftFilename' => '[^\./]+',
		),
		'defaults' => array(
			'format' => 'html',
			'separator' => '.',
		),
	),

	array(
		'name' => 'blogPost',
		'pattern' => '/blog/{blogPostID}/{title}{separator}{format}',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'display'
		),
		'requirements' => array(
			'blogPostID' => '\d+',
			'title' => '[^\./]+',
			'format' => '\w+',
			'separator' => '\.?',
		),
		'defaults' => array(
			'format' => 'html',
			'separator' => '.',
		),
	),

	array(
		'name' => 'blogIndex',
		'pattern' => '/',
		'callable' => array(
			'BaseReality\\Controller\\Blog', 'displayIndex'
		),
	),

	array(
		'name' => 'formValidator',
		'pattern' => '/formValidator',
		'callable' => array(
			'BaseReality\\Controller\\FormValidator', 'display'
		)
	),

	array(
		'name' => 'signup',
		'pattern' => '/signup',
		'callable' => array(
			'BaseReality\\Controller\\Signup', 'display'
		),
		'requirements' => array(
//			'offset' => '\d+',
		),
	),
    array(
        'name' => 'StaticFiles',
        'pattern' => '/staticFiles',
        'callable' => array(
            'BaseReality\\Controller\\Management\\StaticFile', 'display'
        ),
    ),

    array(
        'name' => 'proxyStaticFile',
        'pattern' => '/staticFile/{filename}',
        'callable' => array(
            'BaseReality\\Controller\\ProxyController',
            'staticFile',
        ),
        'requirements' => array(
            'filename' => '[^/]+'
        ),
    ),
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
        //This syntax is fucking stupid
        'optional' => array(
            'size' => true,
        )
    ),

);

