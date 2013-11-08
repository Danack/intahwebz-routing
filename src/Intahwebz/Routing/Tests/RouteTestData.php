<?php

return array(


	array(
		'name' => 'javascriptInclude',
		'pattern' => '/js/jsInclude/{jsInclude}',
		'mapping' => array(
			'BaseReality\\Controller',
			'ScriptInclude',
			'echoJavascriptIncludes',
		),
	),

	# ^/css/cssInclude/(.*)$ /css/cssInclude.css.php?cssInclude=$1 last;
	array(
		'name' => 'cssInclude',
		'pattern' => '/css/cssInclude/{cssInclude}',
		'mapping' => array(
			'BaseReality\\Controller',
			'ScriptInclude',
			'echoCSSIncludes',
		),
	),


	array(
		'name' => 'blogRSSFeed',
		'pattern' => '/rss/',
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'rssFeed'
		),
	),

	array(
		'name' => 'blogUpload',
		'pattern' => '/blog/upload',
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'handleUpload'
		),
	),

	array(
		'name' => 'blogReplace',
		'pattern' => '/blog/{blogPostID}/replace/(\.)?',
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'handleReplace'
		),
	),



	array(
		'name' => 'blogUploadForm',
		'pattern' => '/blogUploadForm',
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'uploadForm'
		),
	),

	array(
		'name' => 'blogPostEdit',
		'pattern' => '/{blogPostID}/edit',
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'showEdit'
		),
		'requirements' => array(
			'blogPostID' => '\d+',
		),
	),

	array(
		'name' => 'blogDraft',
		'pattern' => '/blog/drafts/{draftFilename}{separator}{format}',
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'displayDraft'
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
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'display'
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
		'mapping' => array(
			'BaseReality\\Controller', 'Blog', 'displayIndex'
		),
	),

	array(
		'name' => 'formValidator',
		'pattern' => '/formValidator',
		'mapping' => array(
			'BaseReality\\Controller', 'FormValidator', 'display'
		)
	),

	array(
		'name' => 'signup',
		'pattern' => '/signup',
		'mapping' => array(
			'BaseReality\\Controller', 'Signup', 'display'
		),
		'requirements' => array(
//			'offset' => '\d+',
		),
	),
    array(
        'name' => 'StaticFiles',
        'pattern' => '/staticFiles',
        'mapping' => array(
            'BaseReality\\Controller\\Management', 'StaticFile', 'display'
        ),
    ),

    array(
        'name' => 'proxyStaticFile',
        'pattern' => '/staticFile/{filename}',
        'mapping' => array(
            'BaseReality\\Controller',
            'ProxyController',
            'staticFile',
        ),
        'requirements' => array(
            'filename' => '[^/]+'
        ),
    ),

);

