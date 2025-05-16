<?php

	namespace tip;
	
	class cosmic {
		
		function __construct(){
			$this->avoid_cache = (($_REQUEST['cc']??false)=='cc');
			$this->api_url = 'https://api.cosmicjs.com/v2';
			$this->api_bucketslug = 'theindependencyco-production';
			$this->api_readkey = 'bodqgUVirgLMpeR5tkqVSNdjUDLrFkESRbJ3PeArsLVYgIZcZb';
			$this->api_path = "{$this->api_url}/buckets/{$this->api_bucketslug}/objects?read_key={$this->api_readkey}";
			$this->cache_root = \tip\conf::cache_path;
		}
		
		
		
		function readcache( $cache_slug ) {
			$cache_file = $this->cache_root . $cache_slug;
			$cache_content = unserialize(@file_get_contents($cache_file));
			return $cache_content;
		}
		
		function readapi($props,$query) {
			
			print "Freshly baked ... ";
			
			$call = $this->api_path . '&props=' . $props . '&query=' . urlencode($query);
			$opts = [];
			$opts = [
			    "http" => [
			        "header" => "Accept-Encoding: gzip\r\n"
			    ]
			];
			$context = stream_context_create($opts);
			$response = file_get_contents('compress.zlib://' . $call, false, $context);

			// transformation
			$objects = (json_decode($response))->objects;
			return $objects;
			
		}
		
		function writecache($cache_slug,$data){
			$cache_file = $this->cache_root . $cache_slug;
			file_put_contents($cache_file, serialize($data));
			return unserialize(@file_get_contents($cache_file));
		}
		
		function fetch_article( $slug ) {
			
			
			$cache_slug = '/article/' . $slug;
			$props = implode(',',
						[
						'slug',
						'created_at',
						'metadata.title',
						'metadata.subtitle',
						'metadata.description',
						'metadata.image',
						'metadata.date',
						
						'metadata.body',
						'metadata.blocks',
						'metadata.collections',
						'metadata.author',
						'metadata.redirecturl'
						]
						);
			$query =  '{"type":"articles","slug":"'.$slug.'"}';			
			$cache_content = $this->readcache($cache_slug);			
			
			
			if ($this->avoid_cache || !$cache_content) {
				$objects = $this->readapi( $props, $query );
				if (!$objects) return false;
				
				$articles = [];
				$object = $objects[0];
				//print_pre($object);
				
				$article = new \stdclass;
				$article->slug = $object->slug;
				$article->title = $object->metadata->title ?? false;
				if (!$article->title) $article->title = $object->title??false;
				$article->subtitle = $object->metadata->subtitle??false;
				$article->body = $object->metadata->body??false;
				
				$article->date = $object->metadata->date??false;
				$article->description = $object->metadata->description;
				$article->image = $object->metadata->image??false;
				
				$article->redirecturl = $object->metadata->redirecturl??false;
				$article->url = '/articles/' . date('Y-m-d', strtotime($article->date)) . '/' . $article->slug;
				if ($article->redirecturl) $article->url = $article->redirecturl;
				
				
				
				$article->collections = [];								
				$article->topics = [];
				
				if ($object->metadata->collections??false) {
					foreach($object->metadata->collections as $objectcollection) {
						$collection = new \stdclass;
						$collection->slug = $objectcollection->slug;
						$collection->title = $objectcollection->metadata->title ?? $objectcollection->title;
						$collection->type = $objectcollection->metadata->type->key??false;
						$collection->description = $objectcollection->metadata->description??false;
						$collection->image = $objectcollection->metadata->image??false;
						$article->collections[] = $collection;					
					
						if ($collection->type =='section') $article->section = $collection;
						if ($collection->type =='topic') $article->topics[] = $collection;
					
					}
					
					
				}
				
				
				$article->authors = [];
				foreach($object->metadata->author??[] as $object_author) {
					$author = new \stdclass;
					$author->name = $object_author->metadata->name??false;
					$author->url = $object_author->metadata->url??false;
					$article->authors[] = $author;
				}
				if (!sizeof($article->authors)) {
					$author = new \stdclass;
					$author->name = "Leapers";
					$author->url = "#";
					$article->authors[] = $author;
				}
				
				$article->blocks = $object->metadata->blocks??[];
				$cache_content = $this->writecache( $cache_slug, $article );
			}


			return $cache_content;
			
		}
		
		function fetch_resource( $slug ) {
			
			
			$cache_slug = '/resource/' . $slug;
			$props = implode(',',
						[
						'title','slug','created_at',
						'metadata.title',
						'metadata.subtitle',
						'metadata.description',
						'metadata.image.url',
						'metadata.body',
						'metadata.sections',
						'metadata.conclusion',
						'metadata.related',						
						'metadata.collections',
						'metadata.redirecturl'
						]
						);
			$query =  '{"type":"resources","slug":"'.$slug.'"}';			
			$cache_content = $this->readcache($cache_slug);			
			
			
			if (!$cache_content || $this->avoid_cache) {
				$objects = $this->readapi( $props, $query );

				$articles = [];
				$object = $objects[0];
				$article = new \stdclass;
				$article->slug = $object->slug;
				$article->title = $object->metadata->title ?? $object->title;
				$article->subtitle = $object->metadata->subtitle??false;
				$article->body = $object->metadata->body??false;
				$article->conclusion = $object->metadata->conclusion??false;
				
				
				$article->date = $object->metadata->date??false;
				$article->description = $object->metadata->description;
				$article->image = $object->metadata->image->url??false;
				$article->url = '/resource/' . $article->slug;
				
				
				$article->collections = [];								
				if ($object->metadata->collections??False) {
					foreach($object->metadata->collections as $objectcollection) {
						$collection = new \stdclass;
						$collection->slug = $objectcollection->slug;
						$collection->title = $objectcollection->title;
						$collection->type = $objectcollection->metadata->type->key??false;
						$article->collections[] = $collection;					
					}
				}
				
				$article->sections = $object->metadata->sections;
				$article->related = $object->metadata->related;
				
				
				if ($object->metadata->collections??False) {
					foreach($object->metadata->collections as $objectcollection) {
						$collection = new \stdclass;
						$collection->slug = $objectcollection->slug;
						$collection->title = $objectcollection->title;
						$collection->type = $objectcollection->metadata->type->key??false;
						$article->collections[] = $collection;					
						
						if ($collection->type == 'foundation') $article->foundations[] = $collection;			
						if ($collection->type == 'topic') $article->topics[] = $collection;			

					}
				}
				
				
				
				$cache_content = $this->writecache( $cache_slug, $article );
			}
			

			return $cache_content;
			
		}
		
		
		
		function fetch_articles(){
					
					
			$cache_slug = '/article/list';
			$cache_file = $this->cache_root . $cache_slug;
			$cache_content = unserialize(@file_get_contents($cache_file));
			

			
			if (!$cache_content || $this->avoid_cache || true) {
				
				$path = $this->api_path;
				$props = 'title,slug,metadata';
				$query =  '{"type":"articles"}';
				
				
				$call = $path . '&props=' . $props . '&query=' . urlencode($query);
				$opts = [];
				$opts = [
				    "http" => [
				        "header" => "Accept-Encoding: gzip\r\n"
				    ],
				    "ssl"=>[
				        "verify_peer"=>false,
				        "verify_peer_name"=>false,
				    ],
				];
				
				
				
				$context = stream_context_create($opts);
				$response = file_get_contents('compress.zlib://' . $call, false, $context);

				
				// transformation
				$objects = (json_decode($response))->objects;
				$articles = [];
				foreach($objects as $object) {
					$article = new \stdclass;
					
					
					$article->slug = $object->slug;
					$article->title = $object->metadata->title ?? $object->title;
					$article->subtitle = $object->metadata->subtitle??false;

					$article->metadata = $object->metadata;
					
					$article->date = $object->metadata->date_published??false;
					$article->type = $object->metadata->articletype->key??false;
					
					
					
					if ($article->date) { 
						$articles[$article->date] = $article;
					} else {
						$articles[] = $article;
					}
					//if(sizeof($articles)>=$limit) break;
					
				}
				
				krsort($articles);
				file_put_contents($cache_file, serialize($articles));
				$cache_content = unserialize(@file_get_contents($cache_file));
			
			}
			

			return $cache_content;
		}


		function fetch_projects(){
					
					
			$cache_slug = '/project/list';
			$cache_file = \tip\conf::cache_path . $cache_slug;
			$cache_content = unserialize(@file_get_contents($cache_file));
			
			if (!$cache_content || $this->avoid_cache) {
				
						
				
				$api_url = 'https://api.cosmicjs.com/v2';
				$bucket_slug = 'theindependencyco-production';
				$read_key = 'bodqgUVirgLMpeR5tkqVSNdjUDLrFkESRbJ3PeArsLVYgIZcZb';
				$path = "{$api_url}/buckets/{$bucket_slug}/objects?read_key={$read_key}";
	
				$props = 'title,slug,metadata.clientname,metadata.projectheadline,metadata.content';
				$query =  '{"type":"projects"}';
				
				
				$call = $path . '&props=' . $props . '&query=' . urlencode($query);
				$opts = [];
				$opts = [
					"http" => [
						"header" => "Accept-Encoding: gzip\r\n"
					],
					"ssl"=>[
						"verify_peer"=>false,
						"verify_peer_name"=>false,
					],
				];
				
				
				
				$context = stream_context_create($opts);
				$response = file_get_contents('compress.zlib://' . $call, false, $context);
				
				// transformation
				$objects = (json_decode($response))->objects;
				$projects = [];
				foreach($objects as $object) {
					$project = new \stdclass;
					$project->slug = $object->slug;
					$project->clientname = $object->metadata->clientname;
					$project->projectheadline = $object->metadata->projectheadline??false;
					$project->content = $object->metadata->content??false;
	
					$projects[] = $project;
				}
				
				krsort($projects);
				file_put_contents($cache_file, serialize($projects));
				$cache_content = unserialize(@file_get_contents($cache_file));
			
			}
			
	
			return $cache_content;
		}
	}


	
