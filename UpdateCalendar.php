<?php

	namespace ZohoHelpers;

	use CristianPontes\ZohoCRMClient\ZohoCRMClient;
	use Exception;
	use PDO;
	use stdClass;

	class UpdateCalendar
	{
		private $db;
		private $record;
		private $company;
		private $zohoApi;
		private $zohoUser;

		public function __construct(PDO $db, $companyId, $recordId, $zohoUser)
		{
			$this->db = $db;
			$this->record = $recordId;
			$this->company = $companyId;
			$this->zohoUser = $zohoUser;
			$this->zohoApi = new ZohoCRMClient("Potentials", $this->getAuthtoken());
		}

		public function update()
		{
			$result = new stdClass();

			try{
				$result->params = $this->getDeal();

				if(strtolower($result->params["Install Status"]) == "install scheduled"){
					if($this->checkJobExists() == null){
						$result->add = $this->addToCalendar($result->params);
					} else{
						$result->update = $this->updateCalendar($result->params);
					}
				} else{
					$result->remove = $this->removeFromCalendar();
				}
			} catch(Exception $e){
				$result->error = $e->getMessage();
			}

			return $result;
		}

		public function setParams($params)
		{
			return [
				":JOB_ID"     => $params["POTENTIALID"],
				":USER_ID"    => $params["Technician_ID"],
				":COMPANY_ID" => $this->company,
				":START_TIME" => $params["Install Date and Time"],
				":END_TIME"   => $params["Scheduled Finish Time"],
				":FULL_NAME"  => $params["Contact Name"],
				":CITY"       => $params["City"],
				":STATE"      => $params["State"],
				":ZIP"        => $params["Zip"],
				":ZOHO_USER"  => $this->zohoUser,
			];
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

		public function checkJobExists()
		{
			$result = null;

			$sql = $this->db->prepare("SELECT SCHEDULED_JOBS.ID FROM sablrcrm_test.SCHEDULED_JOBS WHERE SCHEDULED_JOBS.COMPANY_ID = ? AND SCHEDULED_JOBS.JOB_ID = ?");

			if($sql->execute([$this->company, $this->record])){
				if($status = $sql->fetch(PDO::FETCH_OBJ)){
					$result = $status->ID;
				}
			}

			return $result;
		}

		public function addToCalendar($params)
		{
			$sql = $this->db->prepare("INSERT INTO sablrcrm_test.SCHEDULED_JOBS SET SCHEDULED_JOBS.JOB_ID = ?, SCHEDULED_JOBS.DEAL_ID = ?, SCHEDULED_JOBS.USER_ID = ?, SCHEDULED_JOBS.COMPANY_ID = ?, SCHEDULED_JOBS.SCHEDULED_DATE_TIME = ?, SCHEDULED_JOBS.END_DATE_TIME = ?, SCHEDULED_JOBS.DATA_1 = ?, SCHEDULED_JOBS.DATA_2 = ?, SCHEDULED_JOBS.DATA_3 = ?, SCHEDULED_JOBS.ZIP = ?, SCHEDULED_JOBS.DATA_4 = ?");

			return $sql->execute(
				[
					$params["POTENTIALID"],
					$params["POTENTIALID"],
					$params["Technician_ID"],
					$this->company,
					$params["Install Date and Time"],
					$params["Scheduled Finish Time"],
					$params["Contact Name"],
					$params["City"],
					$params["State"],
					$params["Zip"],
					$this->zohoUser,
				]
			);
		}

		public function updateCalendar($params)
		{
			$sql = $this->db->prepare("UPDATE sablrcrm_test.SCHEDULED_JOBS SET SCHEDULED_JOBS.JOB_ID = ?, SCHEDULED_JOBS.DEAL_ID = ?, SCHEDULED_JOBS.USER_ID = ?, SCHEDULED_JOBS.COMPANY_ID = ?, SCHEDULED_JOBS.SCHEDULED_DATE_TIME = ?, SCHEDULED_JOBS.END_DATE_TIME = ?, SCHEDULED_JOBS.DATA_1 = ?, SCHEDULED_JOBS.DATA_2 = ?, SCHEDULED_JOBS.DATA_3 = ?, SCHEDULED_JOBS.ZIP = ?, SCHEDULED_JOBS.DATA_4 = ? WHERE SCHEDULED_JOBS.COMPANY_ID = ? AND SCHEDULED_JOBS.JOB_ID = ?");

			return $sql->execute(
				[
					$params["POTENTIALID"],
					$params["POTENTIALID"],
					$params["Technician_ID"],
					$this->company,
					$params["Install Date and Time"],
					$params["Scheduled Finish Time"],
					$params["Contact Name"],
					$params["City"],
					$params["State"],
					$params["Zip"],
					$this->zohoUser,
					$this->company,
					$this->record,
				]
			);
		}

		public function removeFromCalendar()
		{
			$sql = $this->db->prepare("DELETE FROM sablrcrm_test.SCHEDULED_JOBS WHERE SCHEDULED_JOBS.COMPANY_ID = ? AND SCHEDULED_JOBS.JOB_ID = ?");

			return $sql->execute([$this->company, $this->record]);
		}

		public function getDeal()
		{
			$result = $this->zohoApi->getRecordById($this->record)
				->selectColumns(
					[
						"POTENTIALID",
						"Install Status",
						"Technician",
						"Install Date and Time",
						"Scheduled Finish Time",
						"Contact Name",
						"City",
						"State",
						"Zip",
					]
				)
				->withEmptyFields()
				->request();

			return $result[1]->getData();
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

			return $result;
		}
	}