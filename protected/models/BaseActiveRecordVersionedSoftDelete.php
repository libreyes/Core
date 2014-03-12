<?php
/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

class BaseActiveRecordVersionedSoftDelete extends BaseActiveRecordVersioned
{
	public $deletedField = 'deleted';

	/**
	 * Marks the record soft-deleted
	 */
	public function delete()
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("delete() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		if (isset($this->notDeletedField)) {
			if ($this->{$this->notDeletedField} == 0) {
				return true;
			}

			$this->{$this->notDeletedField} = 0;
		} else {
			if ($this->{$this->deletedField} == 1) {
				return true;
			}

			$this->{$this->deletedField} = 1;
		}

		return $this->save();
	}

	/**
	 * Undeletes the current model
	 */
	public function undelete()
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("undelete() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		if (isset($this->notDeletedField)) {
			if ($this->{$this->notDeletedField} == 1) {
				return true;
			}

			$this->{$this->notDeletedField} = 1;
		} else {
			if ($this->{$this->deletedField} == 0) {
				return true;
			}

			$this->{$this->deletedField} = 0;
		}

		return $this->save();
	}

	/**
	 * Marks the record soft-deleted
	 */
	public function deleteByPk($pk,$condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("deleteByPk() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		if (isset($this->notDeletedField)) {
			$attributes = array(
				$this->notDeletedField => 0,
			);
		} else {
			$attributes = array(
				$this->deletedField => 1,
			);
		}

		return $this->updateByPk($pk,$attributes,$condition,$params);
	}

	/**
	 * Marks the records soft-deleted
	 */
	public function deleteAll($condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("deleteAll() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		if (isset($this->notDeletedField)) {
			$attributes = array(
				$this->notDeletedField => 0,
			);
		} else {
			$attributes = array(
				$this->deletedField => 1,
			);
		}

		return $this->updateAll($attributes,$condition,$params);
	}

	/**
	 * Marks the records soft-deleted
	 */
	public function deleteAllByAttributes($attributes,$condition='',$params=array())
	{
		if (Yii::app()->params['enable_transactions']) {
			if (!$transaction = Yii::app()->db->getCurrentTransaction()) {
				throw new Exception("deleteAllByAttributes() called without a transaction");
			}

			$transaction->addTable($this->tableName());
		}

		if (is_object($condition)) {
			foreach ($attributes as $key => $value) {
				$condition->addCondition("$key = :__$key");
				$condition->params[":__$key"] = $value;
			}
		} else {
			$first = true;

			foreach ($attributes as $key => $value) {
				if ($first) {
					if ($condition) {
						$condition = '( '.$condition.' ) and ';
					}
				} else {
					$condition .= ' and ';
				}

				$condition .= "$key = :__$key ";
				$params[":__$key"] = $value;

				$first = false;
			}
		}

		if (isset($this->notDeletedField)) {
			$attributes = array(
				$this->notDeletedField => 0,
			);
		} else {
			$attributes = array(
				$this->deletedField => 1,
			);
		}

		return $this->updateAll($attributes,$condition,$params);
	}

	/*
	 * Returns items that are not deleted
	 */
	public function notDeleted()
	{
		$alias = $this->getTableAlias(false,false);

		if (isset($this->notDeletedField)) {
			$this->getDbCriteria()->mergeWith(array(
				'condition' => $alias.'.'.$this->notDeletedField.' = 1',
			));
		} else {
			$this->getDbCriteria()->mergeWith(array(
				'condition' => $alias.'.'.$this->deletedField.' = 0',
			));
		}

		return $this;
	}

	/*
	 * Returns items that are not deleted (or inactive/discontinue/whatever makes sense for the current model)
	 * but includes $id even if deleted
	 * $id can also be an array of ids
	 */
	public function notDeletedOrPk($id)
	{
		$alias = $this->getTableAlias(false,false);

		if (empty($id)) {
			return $this->notDeleted();
		}

		if (is_array($id)) {
			$ids = array();
			foreach ($id as $_id) {
				if ($_id) {
					$ids[] = $_id;
				}
			}
			if (empty($ids)) {
				return $this->notDeleted();
			}

			$condition = $alias.'.id in ('.implode(',',$ids).')';
		} else if(is_int($id)){
			$condition = $alias.'.id = '.$id;
		}
		else{
			return $this->notDeleted();
		}

		if (isset($this->notDeletedField)) {
			$this->getDbCriteria()->mergeWith(array(
				'condition' => $alias.'.'.$this->notDeletedField.' = 1 or '.$condition,
			));
		} else {
			$this->getDbCriteria()->mergeWith(array(
				'condition' => $alias.'.'.$this->deletedField.' = 0 or '.$condition,
			));
		}

		return $this;
	}

	public function active()
	{
		return $this->notDeleted();
	}

	public function activeOrPk($id) {
		return $this->notDeletedOrPk($id);
	}
}
