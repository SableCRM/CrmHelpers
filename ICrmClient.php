<?php

	namespace ZohoHelpers;

	interface ICrmClient
	{
		public function getRecordById($id);

		public function createRecord($record);

		public function updateRecord($record);

		public function deleteRecord($id);
	}