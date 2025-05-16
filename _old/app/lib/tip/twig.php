<?php

	namespace tip;
	use Twig\Extra\Markdown\DefaultMarkdown;
	use Twig\Extra\Markdown\MarkdownRuntime;
	use Twig\Extra\Markdown\MarkdownExtension;
	use Twig\RuntimeLoader\RuntimeLoaderInterface;

	
	class twig {
		function __construct( $path = false ){
			
			$template_path = \tip\conf::root_path ;
			if ($path) $template_path = $path;
			$loader = new \Twig\Loader\FilesystemLoader([$template_path]);
			$this->twig = new \Twig\Environment($loader);
			
						
						
			$this->twig->addRuntimeLoader(new class implements RuntimeLoaderInterface {
			    public function load($class) {
			        if (MarkdownRuntime::class === $class) {
			            return new MarkdownRuntime(new DefaultMarkdown());
			        }
			    }
			});
			
			$this->twig->addExtension(new MarkdownExtension());
			
			
		}
		
		function view404(){
			$this->put('/404.twig');
			exit;
		}
		
		
		function display ( $template, $data=[] ) { return $this->put( $template, $data); }
		
		function render( $template, $data=[] ) {
			$data['app'] = \tip\twig::appvars();
			return $this->twig->render( $template, $data );
		}
		
		function put ($template, $data=[], $stop = false) {
			$data['app'] = \tip\twig::appvars();
			print $this->twig->render( $template, $data );
			if ($stop) exit;
						
		}
		
		
		static function appvars() {
			
			$o = [];
			$o['request'] = $_REQUEST;
			$o['page']['url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
			return $o;
			
		}
	}