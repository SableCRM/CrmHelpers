<?php

	namespace ZohoHelpers\DatabaseOperations;

	use InvalidArgumentException;
	use PDO;
	use PDOException;

	abstract class AbstractUser extends AbstractDatabaseOperations
	{
		protected $user;

		protected $company;

		protected $userRole;

		protected $contactName;

		public function __construct(PDO $db, $user, $company)
		{
			parent::__construct($db);

			$this->user = $user;

			$this->company = $company;
		}

		protected function setContactName($portalUserName)
		{
			if($name = explode(" ", $portalUserName)){
				if(count($name) > 2){
					$this->contactName["First Name"] = $name[1];
					$this->contactName["Last Name"] = $name[2];

					return;
				}

				$this->contactName["First Name"] = $name[0];
				$this->contactName["Last Name"] = $name[1];

				return;
			}

			throw new InvalidArgumentException("This user's name is invalid! \"$portalUserName\", User must have first and last name.");
		}

		protected function setUserRole($userRole)
		{
			$userRoles = ["tech portal" => 4, "sales portal" => 2, "both" => 1];

			$accessLevel = strtolower($userRole);

			$this->userRole = $userRoles[$accessLevel];
		}

		protected function runTransaction()
		{
			try{
				$this->db->beginTransaction();

				$this->transaction();

				$this->db->commit();
			} catch(PDOException $e){
				$this->db->rollBack();

				die($e->getMessage());
			}

			return $this->getResult();
		}

		protected abstract function getResult();

		protected abstract function transaction();
	}