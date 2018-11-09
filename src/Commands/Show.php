<?php namespace natanfelles\CodeIgniter\DB\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Show
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package natanfelles\CodeIgniter\DB\Commands
 */
class Show extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:show';
	protected $description = 'Shows Databases Information';
	protected $usage       = 'db:show [database]';
	protected $arguments   = [
		'database' => 'Database name',
	];

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description           = lang('DB.showsDatabase');
		$this->arguments['database'] = lang('DB.databaseName');
	}

	public function run(array $params)
	{
		$database = array_shift($params);

		if (empty($database))
		{
			$db       = config('Database');
			$default  = $db->{$db->defaultGroup}['database'] ?? null;
			$database = CLI::prompt(lang('DB.databaseName'), $default, 'regex_match[\w.]');
			CLI::newLine();
		}

		$show = \Config\Database::connect()
		                        ->query('SHOW DATABASES LIKE :database:', [
			                        'database' => $database,
		                        ])->getRowArray();

		if (empty($show))
		{
			CLI::beep();
			CLI::error(lang('DB.databaseNotFound', [$database]));

			return;
		}

		// List Tables
		$list = $this->getTableList($database);

		if ($list)
		{
			CLI::write(CLI::color(lang('DB.database') . ': ', 'white')
				. CLI::color($database, 'yellow'));
			CLI::table($list, array_keys($list[0]));

			return;
		}

		CLI::write(lang('DB.databaseNoTables', [$database]));
	}

	public function getTableList(string $database): array
	{
		$sql = 'SELECT TABLE_NAME, ENGINE, TABLE_COLLATION, DATA_LENGTH, INDEX_LENGTH, DATA_FREE, AUTO_INCREMENT, TABLE_ROWS, TABLE_COMMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = :database: ORDER BY TABLE_NAME';

		$tables = \Config\Database::connect()
		                          ->query($sql, ['database' => $database])
		                          ->getResultArray();

		$list = [];

		helper('number');

		foreach ($tables as $table)
		{
			$list[] = [
				lang('DB.tableName')     => $table['TABLE_NAME'],
				lang('DB.engine')        => $table['ENGINE'],
				lang('DB.collation')     => $table['TABLE_COLLATION'],
				lang('DB.dataLength')    => number_to_size($table['DATA_LENGTH']),
				lang('DB.indexLength')   => number_to_size($table['INDEX_LENGTH']),
				lang('DB.dataFree')      => number_to_size($table['DATA_FREE']),
				lang('DB.autoIncrement') => $table['AUTO_INCREMENT'],
				lang('DB.rows')          => $table['TABLE_ROWS'],
				lang('DB.comment')       => $table['TABLE_COMMENT'],
			];
		}

		return $list;
	}
}
