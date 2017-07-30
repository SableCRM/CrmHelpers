<?php

	namespace ZohoHelpers\DatabaseOperations;

	use PDO;

	abstract class AbstractDatabaseOperations
	{
		protected $db;

		protected $procedure;

		public function __construct(PDO $db)
		{
			$this->db = $db;
		}
	}