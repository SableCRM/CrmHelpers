<?php

	namespace ZohoHelpers;

	use CristianPontes\ZohoCRMClient\ZohoCRMClient;
	use Exception;
	use PDO;
	use stdClass;

	class GenerateCsNumber
	{
		private $db;
		private $record;
		private $company;
		private $zohoApi;

		public $RESERVED = "RESERVED";
		public $AVAILABLE = "AVAILABLE";

		public function __construct(PDO $db, $companyId, $recordId)
		{
			$this->db = $db;
			$this->record = $recordId;
			$this->company = $companyId;
			$this->zohoApi = new ZohoCRMClient("Potentials", $this->getAuthtoken());
		}

		public function generateCsNumber()
		{
			$result = new stdClass();

			try{
				$result->dealData = $this->getDealData();

//				if($result->dealData->get("Monitoring Center") != "Moni PUR" || $result->dealData->get("Monitoring Center") != "Moni CM")
//				{
//					return $result->message = "Accounts Monitoring Center must be either Moni PUR or Moni CM";
//				}

				if($result->dealData->get("CS Number") == "null"){
					$result->csNumberRow = $this->getAvailableCsNumber();

					$result->updateCsNumber = $this->updateCsNumber($result->csNumberRow->ID, $this->RESERVED);

					$result->updateDeal = $this->updateDeal($result->csNumberRow->CS_NUMBER, $result->csNumberRow->REC_NUMBER);
				} else{
					$result->message = "Account already has CS Number assigned.";
				}
			} catch(Exception $e){
				$result->error = $e->getMessage();
			}

			return $result;
		}

		public function getAuthtoken()
		{
			$result = null;

			$sql = $this->db->prepare("SELECT ZOHO_AUTH_ID FROM sablrcrm_test.ZOHO_USER WHERE ZOHO_USER.COMPANY_ID = ?");

			if($sql->execute([$this->company])){
				$result = $sql->fetch(\PDO::FETCH_OBJ)->ZOHO_AUTH_ID;
			}

			return $result;
		}

		public function getDealData()
		{
			$result = $this->zohoApi->getRecordById($this->record)
				->selectColumns(["CS Number", "Monitoring Center"])
				->withEmptyFields()
				->request();

			return $result[1];
		}

		public function updateDeal($csNumber, $recNumber)
		{
			$result = $this->zohoApi->updateRecords()
				->addRecord(
					[
						"Id"             => $this->record,
						"CS Number"      => $csNumber,
						"Receiver Phone" => $recNumber,
					]
				)
				->request()[1];

			return (array)$result;
		}

		public function getAvailableCsNumber()
		{
			$result = null;

			$sql = $this->db->prepare("SELECT CS_NUMBERS.ID, CS_NUMBERS.CS_NUMBER, CS_NUMBERS.REC_NUMBER FROM sablrcrm_test.CS_NUMBERS WHERE CS_NUMBERS.COMPANY_ID = ? AND STATUS = ? LIMIT 1");

			if($sql->execute([$this->company, $this->AVAILABLE])){
				$result = $sql->fetch(\PDO::FETCH_OBJ);
			}

			return $result;
		}

		public function updateCsNumber($rowId, $status)
		{
			$sql = $this->db->prepare("UPDATE sablrcrm_test.CS_NUMBERS SET CS_NUMBERS.STATUS = ?, CS_NUMBERS.JOB_ID = ? WHERE CS_NUMBERS.COMPANY_ID = ? AND CS_NUMBERS.ID = ?");

			return $sql->execute([$status, $this->record, $this->company, $rowId]);
		}
	}