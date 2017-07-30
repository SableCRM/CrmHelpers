<?php

	namespace ZohoHelpers\DatabaseOperations;

	use PDO;

	class AddUser extends AbstractUser
	{
		private $userPassword;

		private $adminCredentials;

		protected $procedure = "CALL ADMIN_ADD_USER(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,@ERROR_CODE); UPDATE USR SET MONI_NET_USER = ?, MONI_NET_PASSWORD = ? WHERE USER_ID = ? AND COMPANY_ID = ?;";

		public function add($userPassword, $adminCredentials)
		{
			$this->userPassword = $userPassword;

			$this->setAdminCredentials($adminCredentials);

			$this->setUserRole($this->user["Access Level"]);

			$this->setContactName($this->user["Portal User Name"]);

			return $this->runTransaction();
		}

		protected function setAdminCredentials($adminCredentials)
		{
//			if(preg_match(".+,.+", $adminCredentials))
//			{
			$credentials = explode(",", $adminCredentials);

			$this->adminCredentials["Username"] = $credentials[0];
			$this->adminCredentials["Password"] = $credentials[1];
//			}
//
//			throw new InvalidArgumentException("Invalid admin credentials \"$adminCredentials\", format must be \"username,password\".");
		}

		protected function getResult()
		{
			return $this->db->query("SELECT MODULE_ERRO_MSG FROM ERROR_LOG WHERE MODULE_ERROR_CODE = @ERROR_CODE")->fetch(PDO::FETCH_OBJ)->MODULE_ERRO_MSG;
		}

		protected function transaction()
		{
			$sql = $this->db->prepare($this->procedure);

			$sql->execute(
				[
					$this->adminCredentials["Username"],
					$this->adminCredentials["Password"],
					$this->company,
					$this->user["CUSTOMMODULE2_ID"],
					$this->userRole,
					$this->user["Email"],
					null,
					$this->contactName["First Name"],
					$this->contactName["Last Name"],
					null,
					"images/tech-icon.png",
					null,
					null,
					null,
					null,
					$this->userPassword,
					$this->user["Inactive Tech"],
					$this->user["Phone"],
					$this->user["Address"],
					null,
					null,
					$this->user["City"],
					$this->user["State"],
					$this->user["Zip Code"],
					null,
					$this->user["Email"],
					$this->user["Phone"],
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					null,
					$this->user["Moni.Net User Name"],
					$this->user["Moni.Net Password"],
					$this->user["CUSTOMMODULE2_ID"],
					$this->company,
				]
			);
		}
	}