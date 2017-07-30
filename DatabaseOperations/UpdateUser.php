<?php

	namespace ZohoHelpers\DatabaseOperations;

	use PDO;

	class UpdateUser extends AbstractUser
	{
		protected $procedure = "UPDATE USR SET USER_SECURITY_LEVEL_ID = ?, USER_NAME = ?, USER_FIRST_NAME = ?, USER_LAST_NAME = ?,USER_STATUS = ?, USER_PHONE = ?, USER_ADDRESS1 = ?, USER_CITY = ?, USER_STATE = ?, USER_ZIP = ?, USER_EMAIL = ?, MONI_NET_USER = ?, MONI_NET_PASSWORD = ?, USER_MOBILE = ? WHERE USER_ID = ? AND COMPANY_ID = ? LIMIT 1";

		public function update()
		{
			$this->setUserRole($this->user["Access Level"]);

			$this->setContactName($this->user["Portal User Name"]);

			return $this->runTransaction();
		}

		protected function getResult()
		{
			return $this->db->query("SELECT 'UPDATE PERFORMED' AS RESULT")->fetch(PDO::FETCH_OBJ)->RESULT;
		}

		protected function transaction()
		{
			$sql = $this->db->prepare($this->procedure);

			$sql->execute(
				[
					$this->userRole,
					$this->user["Email"],
					$this->contactName["First Name"],
					$this->contactName["Last Name"],
					$this->user["Inactive Tech"],
					$this->user["Phone"],
					$this->user["Address"],
					$this->user["City"],
					$this->user["State"],
					$this->user["Zip"],
					$this->user["Email"],
					$this->user["Moni.Net User Name"],
					$this->user["Moni.Net Password"],
					$this->user["Phone"],
					$this->user["CUSTOMMODULE2_ID"],
					$this->company,
				]
			);
		}
	}