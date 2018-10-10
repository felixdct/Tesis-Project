<?php
class AuthenticatePDO extends PDO
{
	private $driver;
	private $host;
	private $port;
	private $schema;
	private $user;
	private $passwd;
	private $dsn;

	public function __construct($file = '../resources/config/sys.odbc.ini')
	{
		$settings = parse_ini_file($file, TRUE);

		if($settings == False) {
			throw new exception('Unable to open '. $file . '.');
		}

		$this->driver = $settings['database']['driver'];
		$this->host   = $settings['database']['host'];
		$this->port   = $settings['database']['port'];
		$this->schema = $settings['database']['schema'];
		$this->user   = $settings['database']['user'];
		$this->passwd = $settings['database']['passwd'];

		$this->dsn    = $this->driver . ':host=' . $this->host . 
		       ((!empty($port))? (';port='. $this->port) : '') .
			';dbname=' . $this->schema;

		parent::__construct($this->dsn, $this->user, $this->passwd);
	}
}
?>
