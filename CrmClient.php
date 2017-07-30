<?php

	namespace ZohoHelpers;

	use PDO;

	abstract class CrmClient implements ICrmClient
	{
		protected $db;

		protected $module;

		protected $authToken;

		protected $crmClient;

		protected $Contacts = "Contacts";

		protected $Users = "CustomModule2";

		protected $Potentials = "Potentials";

		protected function __construct(PDO $db, $company)
		{
			$this->db = $db;

			$this->setAuthToken($this->getAuthToken($company));
		}

		protected function getAuthToken($company)
		{
			$result = null;

			$sql = $this->db->prepare("SELECT ZOHO_AUTH_ID FROM sablrcrm_test.ZOHO_USER WHERE ZOHO_USER.COMPANY_ID = ?");

			if($sql->execute([$company])){
				$result = $sql->fetch(\PDO::FETCH_OBJ)->ZOHO_AUTH_ID;
			}

			return $result;
		}

		protected function setModule($module)
		{
			$this->module = $module;
		}

		protected function setAuthToken($authToken)
		{
			$this->authToken = $authToken;
		}

		protected function setCrmClient($client)
		{
			$this->crmClient = $client;
		}

		protected abstract function getCrmClient();
	}