<?php

	namespace ZohoHelpers\DatabaseOperations;

	use PDO;

	class DeleteUser extends AbstractUser
	{
		protected $procedure = "DELETE FROM sablrcrm_test.USR WHERE USR.USER_ID = ? AND USR.COMPANY_ID = ? LIMIT 1";

		public function delete()
		{
			return $this->runTransaction();
		}

		protected function getResult()
		{
			return $this->db->query("SELECT 'DELETE PERFORMED' AS RESULT")->fetch(PDO::FETCH_OBJ)->RESULT;
		}

		protected function transaction()
		{
			$sql = $this->db->prepare($this->procedure);

			$sql->execute(
				[
					$this->user["CUSTOMMODULE2_ID"],
					$this->company,
				]
			);
		}
	}