<?php
	/*
	
		https://phprouter.com/

		https://github.com/phprouter/main
	
	*/

	class route
	{		
   	public function GET($route, $path_to_include) 		{ $this->route($route, $path_to_include); }
   	public function POST($route, $path_to_include) 		{ $this->route($route, $path_to_include); }
   	public function PUT($route, $path_to_include) 		{ $this->route($route, $path_to_include); }
   	public function DELETE($route, $path_to_include) 	{ $this->route($route, $path_to_include); }

		private function route($route, $path_to_include)
		{
			global $_CONFIG;
			global $_JSON_PRINT;
         global $_TOKEN;
         global $_MYSQL;
         global $_LOG;
			global $_PUBLIC_KEY;
			global $_METHOD;
			global $_TABLE_LIST;
			global $_PARAM;

			$callback = $path_to_include;

			if(!is_callable($callback)) { if (!strpos($path_to_include, '.php')) { $path_to_include.='.php'; } }   

			$request_url         = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);
			$request_url         = rtrim($request_url, '/');
			$request_url         = strtok($request_url, '?');
			$route_parts         = explode('/', $route);
			$request_url_parts   = explode('/', $request_url);
			array_shift($route_parts);
			array_shift($request_url_parts);

			if (count($route_parts) != count($request_url_parts)) { return; } 

			$parameters = [];
			for ($__i__ = 0; $__i__ < count($route_parts); $__i__++)
			{
				$route_part = $route_parts[$__i__];
			  	if (preg_match("/^[$]/", $route_part))
				{
					$route_part    = ltrim($route_part, '$');
					array_push($parameters, $request_url_parts[$__i__]);
				 	$$route_part   = $request_url_parts[$__i__];
			  	}
			  	else if ($route_parts[$__i__] != $request_url_parts[$__i__]) { return; } 
			}

			// Auth token
			if ($_PUBLIC_KEY)
			{
				$_TOKEN->setKey($_PUBLIC_KEY);
				$_TOKEN->auth();
			}
			$_LOG->write(1, 1, 'route', implode('/', $request_url_parts));

			// Callback function
			if (is_callable($callback))
			{
				call_user_func_array($callback, $parameters);
			} 
			else
			{
				//echo $path_to_include;
				if (file_exists($path_to_include))
				{
					include $path_to_include;
				}
			}

			if ($_JSON_PRINT->_printed) { exit(); }
		}
	}
?>