<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Jochen Rau <jochen.rau@typoplanet.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * An abstract Entity. An Entity is an object fundamentally defined not by its attributes,
 * but by a thread of continuity and identity (e.g. a person).
 *
 * @package Extbase
 * @subpackage DomainObject
 * @version $ID:$
 */
abstract class Tx_Extbase_DomainObject_AbstractEntity extends Tx_Extbase_DomainObject_AbstractDomainObject {

	/**
	 * @var An array holding the clean property values. Set right after reconstitution of the object
	 */
	private $_cleanProperties = NULL;

	/**
	 * Register an object's clean state, e.g. after it has been reconstituted
	 * from the database.
	 *
	 * @param string $propertyName The name of the property to be memorized. If omitted all persistable properties are memorized.
	 * @return void
	 */
	public function _memorizeCleanState($propertyName = NULL) {
		if ($propertyName !== NULL) {
			$this->_memorizePropertyCleanState($propertyName);
		} else {
			$dataMapper = t3lib_div::makeInstance('Tx_Extbase_Persistence_Mapper_DataMapper'); // singleton
			$this->_cleanProperties = array();
			$properties = get_object_vars($this);
			foreach ($properties as $propertyName => $propertyValue) {
				if ($dataMapper->isPersistableProperty(get_class($this), $propertyName)) {
					$this->_memorizePropertyCleanState($propertyName);
				}
			}
		}
	}

	/**
	 * Register an properties's clean state, e.g. after it has been reconstituted
	 * from the database.
	 *
	 * @param string $propertyName The name of the property to be memorized. If omittet all persistable properties are memorized.
	 * @return void
	 */
	public function _memorizePropertyCleanState($propertyName) {
		$propertyValue = $this->$propertyName;
		if (!is_array($this->_cleanProperties)) {
			$this->_cleanProperties = array();
		}
		if (is_object($propertyValue)) {
			$this->_cleanProperties[$propertyName] = clone($propertyValue);

			// We need to make sure the clone and the original object
			// are identical when compared with == (see _isDirty()).
			// After the cloning, the Domain Object will have the property
			// "isClone" set to TRUE, so we manually have to set it to FALSE
			// again. Possible fix: Somehow get rid of the "isClone" property,
			// which is currently needed in Fluid.
			if ($propertyValue instanceof Tx_Extbase_DomainObject_AbstractDomainObject) {
				$this->_cleanProperties[$propertyName]->_setClone(FALSE);
			}
		} else {
			$this->_cleanProperties[$propertyName] = $propertyValue;
		}
	}

	/**
	 * Returns a hash map of clean properties and $values.
	 *
	 * @return array
	 */
	public function _getCleanProperties() {
		if (!is_array($this->_cleanProperties)) throw new Tx_Extbase_Persistence_Exception_CleanStateNotMemorized('The clean state of the object "' . get_class($this) . '" has not been memorized before calling _isDirty().', 1233309106);
		return $this->_cleanProperties;
	}

	/**
	 * Returns a hash map of dirty properties and $values
	 *
	 * @return array
	 */
	public function _getDirtyProperties() {
		// FIXME: We persist more than we'd like to. See _isDirty for the correct check.
		if (!is_array($this->_cleanProperties)) throw new Tx_Extbase_Persistence_Exception_CleanStateNotMemorized('The clean state of the object "' . get_class($this) . '" has not been memorized before asking _isDirty().', 1233309106);
		if ($this->uid !== NULL && $this->uid != $this->_cleanProperties['uid']) throw new Tx_Extbase_Persistence_Exception_TooDirty('The uid "' . $this->uid . '" has been modified, that is simply too much.', 1222871239);
		$dirtyProperties = array();
		foreach ($this->_cleanProperties as $propertyName => $propertyValue) {
			if ($this->$propertyName !== $propertyValue) {
				$dirtyProperties[$propertyName] = $this->$propertyName;
			}
		}
		return $dirtyProperties;
	}

	/**
	 * Returns TRUE if the properties were modified after reconstitution
	 *
	 * @return boolean
	 */
	public function _isDirty($propertyName = NULL) {
		if (!is_array($this->_cleanProperties)) throw new Tx_Extbase_Persistence_Exception_CleanStateNotMemorized('The clean state of the object "' . get_class($this) . '" has not been memorized before asking _isDirty().', 1233309106);
		if ($this->uid !== NULL && $this->uid != $this->_cleanProperties['uid']) throw new Tx_Extbase_Persistence_Exception_TooDirty('The uid "' . $this->uid . '" has been modified, that is simply too much.', 1222871239);
		$result = FALSE;
		if ($propertyName !== NULL) {
			if (is_object($this->$propertyName)) {
				// In case it is an object, we do a simple comparison (!=) as we want cloned objects to return the same values.
				$result = $this->_cleanProperties[$propertyName] != $this->$propertyName;
			} else {
				$result = $this->_cleanProperties[$propertyName] !== $this->$propertyName;
			}
		} else {
			foreach ($this->_cleanProperties as $propertyName => $propertyValue) {
				if (is_object($this->$propertyName)) {
					// In case it is an object, we do a simple comparison (!=) as we want cloned objects to return the same values.
					if ($this->$propertyName != $propertyValue) {
						$result = TRUE;
						break;
					}
				} else {
					if ($this->$propertyName !== $propertyValue) {
						$result = TRUE;
						break;
					}
				}
			}
		}
		return $result;
	}

}
?>