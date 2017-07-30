<?php

	namespace ZohoHelpers;

	use Helpers\pdo;
	use InvalidArgumentException;
	use ZohoHelpers\DatabaseOperations\AddUser;
	use ZohoHelpers\DatabaseOperations\DeleteUser;
	use ZohoHelpers\DatabaseOperations\UpdateUser;

	class SableUser
	{
		private $crmClient;

		private $sableUser;

		public function __construct(ICrmClient $client, $id = null)
		{
			$this->crmClient = $client;

			$this->sableUser = $this->getUser($id);
		}

		public function getUser($id)
		{
			if($id != null){
				return $this->crmClient->getRecordById($id);
			}

			return null;
		}

		public function addUser($company, $userPassword, $adminCredentials)
		{
			if(count($this->sableUser) > 0){
				$user = new AddUser((new pdo())->getConnection(), $this->sableUser, $company);

				return $user->add($userPassword, $adminCredentials);
			}

			throw new InvalidArgumentException("User id is required when adding to sable");
		}

		public function updateUser($company)
		{
			if(count($this->sableUser) > 0){
				$user = new UpdateUser((new pdo())->getConnection(), $this->sableUser, $company);

				return $user->update();
			}

			throw new InvalidArgumentException("User id is required when updating to sable");
		}

		public function deleteUser($company)
		{
			if(count($this->sableUser) > 0){
				$user = new DeleteUser((new pdo())->getConnection(), $this->sableUser, $company);

				return $user->delete();
			}

			throw new InvalidArgumentException("User id is required when deleting from sable");
		}
	}