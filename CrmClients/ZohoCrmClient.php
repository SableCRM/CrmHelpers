<?php

	namespace ZohoHelpers\CrmClients;

	use CristianPontes\ZohoCRMClient\ZohoCRMClient as CristianPontes;
	use Exception;
	use PDO;
	use ZohoHelpers\CrmClient;

	class ZohoCrmClient extends CrmClient
	{
		public function __construct(PDO $db, $company)
		{
			parent::__construct($db, $company);

			$this->setCrmClient(new CristianPontes($this->Users, $this->authToken));
		}

		public function getRecordById($id)
		{
			$result = $this->getCrmClient()->getRecordById($id)
				->withEmptyFields()
				->request()[1];

			if($result != null){
				return $result->getData();
			}

			throw new Exception("Sable was unable to find a user in crm, matching this id. \"$id\"");
		}

		public function createRecord($record)
		{
			// TODO: Implement createRecord() method.
		}

		public function updateRecord($record)
		{
			// TODO: Implement updateRecord() method.
		}

		public function deleteRecord($id)
		{
			// TODO: Implement deleteRecord() method.
		}

		protected function getCrmClient() : CristianPontes
		{
			return $this->crmClient;
		}
	}