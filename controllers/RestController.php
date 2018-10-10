<?php
require_once('../models/ErrorSystem.php');
require_once('UserController.php');
require_once('../views/Header.php');
require_once('../models/FormatFactory.php');

class RestController {
	private $httpMethod;
	public function __construct($httpMethod) {
		$this->httpMethod = $httpMethod;
	}

	public function handleUnknownAction($controllerName, $actionName, $parameters) {
		$resp;
		$controllerName = ucfirst($controllerName) . 'Controller';
		if (class_exists($controllerName)) {
			$controller = new $controllerName($this->httpMethod, $parameters);
			$action = strtolower($actionName).'Action';
			if (method_exists($controller, $action)) {
				$resp = $controller->$action();
			}else {
				$resp['errCode'] = ErrorSystem::WEBSERVICE_METHOD_NOT_FOUND;
			}
		}else {
			$resp['errCode'] = ErrorSystem::WEBSERVICE_CONTROLLER_NOT_FOUND;
		} 
		return $resp;
	}

	public function render($view, $format, $result) {
		$header = new Header();
		$statusCode;
		$response = "";

		if (empty($result)) {
			$statusCode = 404;
		}else {
			$statusCode = 200;
		}

		$header->setHttpHeaders($format, $statusCode);

		try {
			$encoder = FormatFactory::getEncoder(strtoupper($format), $view);
			$response = $encoder->encode($result);
		} catch (Exception $e) {
			$response .= $e->getMessage() . "\n";
		}

		echo $response;
	}
}

/* GET request */
$controllerName = isset($_REQUEST['controller'])? $_REQUEST['controller'] : '';
$methodName     = isset($_REQUEST['method']) ? $_REQUEST['method'] : '';
$format         = isset($_REQUEST['format']) ? $_REQUEST['format'] : '';

$controller = new RestController($_SERVER['REQUEST_METHOD']);
$resp = $controller->handleUnknownAction($controllerName, $methodName, $_REQUEST);
$controller->render($controllerName, $format, $resp);
?>
