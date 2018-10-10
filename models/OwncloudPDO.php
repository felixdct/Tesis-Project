<?php
class OwncloudPDO extends PDO
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

		$this->driver = $settings['ownclouddb']['driver'];
		$this->host   = $settings['ownclouddb']['host'];
		$this->port   = $settings['ownclouddb']['port'];
		$this->schema = $settings['ownclouddb']['schema'];
		$this->user   = $settings['ownclouddb']['user'];
		$this->passwd = $settings['ownclouddb']['passwd'];

		$this->dsn    = $this->driver . ':host=' . $this->host . 
		       ((!empty($port))? (';port='. $this->port) : '') .
			';dbname=' . $this->schema;

		parent::__construct($this->dsn, $this->user, $this->passwd);
	}
}
?>
