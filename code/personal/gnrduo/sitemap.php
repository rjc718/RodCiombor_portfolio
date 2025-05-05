<?php
    require 'includes/classes/Database.php';
    require 'includes/classes/Configuration.php';

	ini_set('memory_limit', '128M');

    $config = Configuration::getInstance();
    $siteUrl = $config->getConfigValue('SITE_URL');
    $pageSlugs = $config->getAllPageSlugs();

    $highPriorityPages = [
        'home', 
        'shows', 
        'song-list', 
        'contact-us',
        'testimonials'
    ];
    $lowPriorityPages = [
        '404'
    ];
    $changedMonthly = [
        'shows', 
        'song-list', 
        'testimonials'
    ];
    $changedNever = [
        '404'
    ];

	$pageCount = 2;

	$doc = new DomDocument("1.0", "utf-8");
	$root = $doc->createElement('urlset');

	// Add URLset Attribute 
	$UAtt = array(
        'xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9', 
        'xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance', 
        'xmlns:xhtml' => 'http://www.w3.org/1999/xhtml', 
        'xsi:schemaLocation' => 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd'
    );

	foreach ($UAtt as $key => $value) {
		$T = $doc->createAttribute($key);
		$T->value = $value;
		$root->appendChild($T);
		unset($T);
	}

	$doc->appendChild($root);

    //Add pages
    foreach($pageSlugs as $slug)
    {
        $urlElem = $doc->createElement('url');
	    $locElem = $doc->createElement('loc');

        if($slug['page_slug'] == 'home'){
            $pageLink = rtrim($siteUrl,'/');
        } else {
            $pageLink = $siteUrl . '/' . $slug['page_slug'];
        }

        //Add the link
        $locElem->appendChild(
            $doc->createTextNode($pageLink)
        );
	    $urlElem->appendChild($locElem);    
    
        //Add last modified
        $lastModElem = $doc->createElement('lastmod');
	    $lastModDate = date(DATE_W3C);
	    $lastModElem->appendChild(
            $doc->createTextNode($lastModDate)
        );
	    $urlElem->appendChild($lastModElem);

        //Add Change Frequency
        $frequency = 'yearly';
        if(
            in_array(
                $slug['page_slug'], 
                $changedMonthly
            )
        ){
            $frequency = 'monthly';
        }
        elseif(
            in_array(
                $slug['page_slug'], 
                $changedNever
            )
        ){
            $frequency = 'never';
        }

        $changeFreqElem = $doc->createElement('changefreq');
	    $changeFreqElem->appendChild(
            $doc->createTextNode($frequency)
        );
	    $urlElem->appendChild($changeFreqElem);

        //Add priority
        $priority = '.75';
        if(
            in_array(
                $slug['page_slug'], 
                $highPriorityPages
            )
        ){
            $priority = '1.0000';
        }
        elseif(
            in_array(
                $slug['page_slug'], 
                $lowPriorityPages
            )
        ){
            $priority = '0.5';
        }
        $priorityElem = $doc->createElement('priority');
	    $priorityElem->appendChild(
            $doc->createTextNode($priority)
        );
	    $urlElem->appendChild($priorityElem);
        
        //Append to root
        $root->appendChild($urlElem);

        //Increment page count
        $pageCount++;
    }

	$xmlfile = 'sitemap.xml';

	echo '<p>Saving ' . $xmlfile . '</p>';
	echo '<p>' . $pageCount . ' Pages</p>';
	$doc->save($xmlfile);
	$xdoc = new DomDocument;

	$xmlschema = 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd';
	//Load the xml document in the DOMDocument object

	$xdoc->Load($xmlfile);
	//Validate the XML file against the schema
	libxml_use_internal_errors(true);
	if ($xdoc->schemaValidate($xmlschema)) {
		echo "<p>file is valid.</p><br>";
	} else {
		echo "<p>file is invalid.</p><br>";
		$errorM = "<p>file is invalid.</p><br>";
		$errors = libxml_get_errors();
		foreach ($errors as $error) {
			$errorM .= sprintf('XML error "%s" [%d] (Code %d) in %s on line %d column %d' . "\n",
			$error->message, $error->level, $error->code, $error->file,
			$error->line, $error->column);
		}
		libxml_clear_errors();
		echo $errorM;
	}

	libxml_use_internal_errors(false);
?>
</body>
</html>


