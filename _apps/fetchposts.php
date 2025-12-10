<?php

    // --------------------------------------
    // CONFIG
    // --------------------------------------
    $feed_url    = 'https://theindependencyco.substack.com/feed'; // change this
    $output_dir  = __DIR__ . '/../_notes';                        // or wherever Jekyll reads from
    $feed_cache = __DIR__ . '/../_data/substack.xml';
    header("Content-type: text/plain");

    // --------------------------------------
    // FETCH RSS
    // --------------------------------------



    $rss = fetch_rss( $feed_url );
    cache_rss ( $rss, $feed_cache );
    $rss = fetch_cache ( $feed_cache );

    libxml_use_internal_errors(true);
    $xml = simplexml_load_string($rss);
    foreach($xml->channel->item as $item) {
        list($markdown,$slug) = parse_item ($item);
        print "{$slug}\n";
        write_markdown( $output_dir, $slug, $markdown, $overwrite=false );
    }



    exit;






    function write_markdown ( $output_dir, $slug, $markdown, $overwrite=false ) {
        $file_exists = false;
        if ($overwrite==false) $file_exists = file_exists($output_dir . '/' . $slug );
        if (!$file_exists) file_put_contents($output_dir . '/' . $slug, $markdown );
    }
    




    




    function fetch_cache ( $feed_cache ) {
        return file_get_contents( $feed_cache );
    }
   
    function cache_rss ( $feed_body, $feed_cache ) {
        file_put_contents( $feed_cache, $feed_body );
    }

    function fetch_rss( $feed_url ) {

        $contextOptions = [
            'http' => [
                'timeout' => 10,
            ],
        ];
    
        $context = stream_context_create($contextOptions);
        $rssBody = @file_get_contents($feed_url, false, $context);
    
        if ($rssBody === false) {
            echo "Error: could not fetch RSS feed.\n";
            exit(1);
        }
        return $rssBody;
    }
    
    
  
    
   
    $result = @file_put_contents($filepath, $body);
    if ($result === false) {
        echo "Error: could not write file: {$filepath}\n";
        exit(1);
    }

    echo "Wrote: {$filepath}\n";


    function parse_item ( $item ) {
            
        $timezone   = 'Europe/London';
        $title   = trim((string) $item->title);
        $link    = trim((string) $item->link);
        $pubDateRaw = trim((string) $item->pubDate);

        // pubDate → DateTime
        try {
            $dt = new DateTime($pubDateRaw, new DateTimeZone($timezone));
        } catch (Exception $e) {
            $dt = new DateTime('now', new DateTimeZone($timezone));
        }

        // Get Substack HTML content if available (content:encoded)
        $namespaces  = $item->getNameSpaces(true);
        $contentHtml = '';

        if (isset($namespaces['content'])) {
            $contentNode = $item->children($namespaces['content']);
            if (isset($contentNode->encoded)) {
                $contentHtml = (string) $contentNode->encoded;
            }
        }

        // Fallback to description
        if ($contentHtml === '') {
            $contentHtml = (string) $item->description;
        }

        // --------------------------------------
        // BUILD FILENAME + FRONT MATTER
        // --------------------------------------
        $slug = strtolower($title);
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug);
        $slug = trim($slug, '-');

        $filenameDate = $dt->format('Y-m-d');
        $filename     = $filenameDate . '-' . $slug . '.md';
        $description = trim((string) $item->description);

        //$filepath = rtrim($outputDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;

        // Escape title for YAML
        $yamlTitle = str_replace('"', '\"', $title);
        $yamlSubtitle = str_replace('"', '\"', $description);

        $frontMatter  = "---\n";
        $frontMatter .= "title: \"{$yamlTitle}\"\n";
        $frontMatter .= "description: \"{$yamlSubtitle}\"\n";
        $frontMatter .= "date_posted: " . $dt->format('Y-m-d') . "\n";
        $frontMatter .= "original_url: \"{$link}\"\n";
        $frontMatter .= "source: substack\n";
        $frontMatter .= "layout: note\n";
        $frontMatter .= "toplevelsubnav_url: /notes\n";
        $frontMatter .= "toplevelsubnav_text: Notes\n";
        
        $frontMatter .= "---\n\n";

        // You can keep HTML as-is – Jekyll will render it fine inside Markdown
        $body = $frontMatter . $contentHtml . "\n";
        return [$body,$filename];

        
    }

   