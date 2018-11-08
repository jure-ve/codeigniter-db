<?php namespace App\Commands\Database;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Class Delete
 *
 * @author  Natan Felles https://natanfelles.github.io
 * @link    https://github.com/natanfelles/codeigniter-db
 *
 * @package App\Commands\Database
 */
class Delete extends BaseCommand
{
	protected $group       = 'Database';
	protected $name        = 'db:delete';
	protected $description = 'Deletes a Database';

	public function __construct(...$params)
	{
		parent::__construct(...$params);

		$this->description = lang('Database.deletesDatabase');
	}

	public function run(array $params)
	{
		$database = array_shift($params);

		if (empty($database))
		{
			$database = CLI::prompt(lang('Database.databaseName'), null, 'regex_match[\w.]');
		}

		$show = \Config\Database::connect()
		                        ->query('SHOW DATABASES LIKE :database:', [
			                        'database' => $database,
		                        ])->getRowArray();

		if (empty($show))
		{
			CLI::beep();
			CLI::error(lang('Database.databaseNotExists', [$database]));
			CLI::newLine();
			exit;
		}

		$result = \Config\Database::forge()->dropDatabase($database);

		if ($result)
		{
			CLI::write(lang('Database.databaseDeleted', [$database]), 'green');
			CLI::newLine();
			exit;
		}

		CLI::error(lang('Database.databaseNotDeleted', [$database]));
		CLI::newLine();
	}
}
